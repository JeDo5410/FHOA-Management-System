@echo off
setlocal EnableDelayedExpansion

REM Set database credentials and backup locations
set DB_USER=root
set DB_PASSWORD=1234
set DB_NAME=fhoa
set BACKUP_PATH1=C:\MySQL_Backup
set BACKUP_PATH2=D:\MySQL_Backup
set SKIP_ONEDRIVE=
REM Use 8.3 short path format to avoid space issues
for /f "tokens=*" %%G in ('dir /x /a:d "C:\Users" ^| findstr /i "Fortezza"') do (
    set SHORT_USERNAME=%%~nxG
    set SHORT_USERNAME=!SHORT_USERNAME:~0,8!
)
set BACKUP_PATH3=C:\Users\!SHORT_USERNAME!\OneDrive
REM Alternative using environment variable
REM set BACKUP_PATH3=%USERPROFILE%\OneDrive
set MAX_BACKUPS=10
set MYSQL_PATH=C:\Program Files\MySQL\MySQL Server 8.0\bin

REM Create timestamp for unique filename - optimize by removing spaces in one step
set TIMESTAMP=%date:~10,4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=!TIMESTAMP: =0!

REM Process all backup paths
REM Enhanced error checking for the OneDrive backup
if exist "!BACKUP_PATH3!" (
    echo OneDrive path exists: !BACKUP_PATH3!
) else (
    echo WARNING: OneDrive path does not exist. Attempting to create it...
    mkdir "!BACKUP_PATH3!" 2>nul
    if !ERRORLEVEL! neq 0 (
        echo ERROR: Cannot create OneDrive directory. Check path and permissions.
        echo Path was: !BACKUP_PATH3!
        set BACKUP_PATH3=%USERPROFILE%\OneDrive
        echo Trying alternative path: !BACKUP_PATH3!
        if not exist "!BACKUP_PATH3!" mkdir "!BACKUP_PATH3!" 2>nul
        if !ERRORLEVEL! neq 0 (
            echo ERROR: Alternative OneDrive path failed too. OneDrive backups will be skipped.
            set SKIP_ONEDRIVE=1
        )
    )
)

REM Clean up old backups more efficiently using a single sorted dir command
echo Cleaning up old backups...
for %%P in ("!BACKUP_PATH1!" "!BACKUP_PATH2!") do (
    set COUNT=0
    for /f "tokens=*" %%F in ('dir /b /o-d "%%~P\%DB_NAME%_backup_*.sql" 2^>nul') do (
        set /a COUNT+=1
        if !COUNT! geq %MAX_BACKUPS% (
            del "%%~P\%%F" >nul 2>&1
        )
    )
)

REM Handle OneDrive cleanup separately
if not defined SKIP_ONEDRIVE (
    set COUNT=0
    for /f "tokens=*" %%F in ('dir /b /o-d "!BACKUP_PATH3!\%DB_NAME%_backup_*.sql" 2^>nul') do (
        set /a COUNT+=1
        if !COUNT! geq %MAX_BACKUPS% (
            del "!BACKUP_PATH3!\%%F" >nul 2>&1
        )
    )
)

REM Insert log entry into activity_log table - run in background
start /b "" "!MYSQL_PATH!\mysql" --user=!DB_USER! --password=!DB_PASSWORD! -e "INSERT INTO fhoa.activity_log (Process, timestamp) VALUES('Database BACKUP', NOW());" >nul 2>&1

REM Execute the mysqldump commands for all locations
echo Creating backups... Please wait.
"!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH1!\%DB_NAME%_backup_%TIMESTAMP%.sql"

REM Only create second and third backups if first one succeeded
if %ERRORLEVEL% == 0 (
    echo Primary backup successful.
    copy "!BACKUP_PATH1!\%DB_NAME%_backup_%TIMESTAMP%.sql" "!BACKUP_PATH2!\%DB_NAME%_backup_%TIMESTAMP%.sql" >nul
    if %ERRORLEVEL% == 0 (
        echo Secondary backup successful.
        if not defined SKIP_ONEDRIVE (
            echo Copying to OneDrive...
            copy "!BACKUP_PATH1!\%DB_NAME%_backup_%TIMESTAMP%.sql" "!BACKUP_PATH3!\%DB_NAME%_backup_%TIMESTAMP%.sql" >nul
            if %ERRORLEVEL% == 0 (
                echo OneDrive backup successful.
            ) else (
                echo Creating OneDrive backup independently...
                "!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH3!\%DB_NAME%_backup_%TIMESTAMP%.sql"
                if !ERRORLEVEL! neq 0 echo ERROR: Failed to create OneDrive backup.
            )
        )
    ) else (
        echo Creating secondary backup independently...
        "!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH2!\%DB_NAME%_backup_%TIMESTAMP%.sql"
        
        if not defined SKIP_ONEDRIVE (
            echo Creating OneDrive backup...
            if exist "!BACKUP_PATH1!\%DB_NAME%_backup_%TIMESTAMP%.sql" (
                copy "!BACKUP_PATH1!\%DB_NAME%_backup_%TIMESTAMP%.sql" "!BACKUP_PATH3!\%DB_NAME%_backup_%TIMESTAMP%.sql" >nul
            ) else (
                "!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH3!\%DB_NAME%_backup_%TIMESTAMP%.sql"
            )
            if !ERRORLEVEL! neq 0 echo ERROR: Failed to create OneDrive backup.
        )
    )
) else (
    echo Primary backup failed, trying secondary location...
    "!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH2!\%DB_NAME%_backup_%TIMESTAMP%.sql"
    if %ERRORLEVEL% == 0 (
        echo Secondary backup successful, copying to OneDrive...
        if not defined SKIP_ONEDRIVE (
            copy "!BACKUP_PATH2!\%DB_NAME%_backup_%TIMESTAMP%.sql" "!BACKUP_PATH3!\%DB_NAME%_backup_%TIMESTAMP%.sql" >nul
            if %ERRORLEVEL% != 0 (
                echo Creating OneDrive backup independently...
                "!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH3!\%DB_NAME%_backup_%TIMESTAMP%.sql"
                if !ERRORLEVEL! neq 0 echo ERROR: Failed to create OneDrive backup.
            )
        )
    ) else (
        echo Secondary backup failed.
        if not defined SKIP_ONEDRIVE (
            echo Trying OneDrive location directly...
            "!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH3!\%DB_NAME%_backup_%TIMESTAMP%.sql"
            if !ERRORLEVEL! neq 0 echo ERROR: All backup attempts failed.
        )
    )
)

echo Backup process completed at: %time%
endlocal