@echo off
setlocal EnableDelayedExpansion

REM Set database credentials and backup locations
set DB_USER=root
set DB_PASSWORD=1234
set DB_NAME=fhoa
set BACKUP_PATH1=C:\MySQL_Backup
set BACKUP_PATH2=D:\MySQL_Backup
set MAX_BACKUPS=10
set MYSQL_PATH=C:\Program Files\MySQL\MySQL Server 8.0\bin

REM Create timestamp for unique filename - optimize by removing spaces in one step
set TIMESTAMP=%date:~10,4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=!TIMESTAMP: =0!

REM Process both backup paths in parallel via start command
if not exist "!BACKUP_PATH1!" mkdir "!BACKUP_PATH1!"
if not exist "!BACKUP_PATH2!" mkdir "!BACKUP_PATH2!"

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

REM Insert log entry into activity_log table - run in background
start /b "" "!MYSQL_PATH!\mysql" --user=!DB_USER! --password=!DB_PASSWORD! -e "INSERT INTO fhoa.activity_log (Process, timestamp) VALUES('Database BACKUP', NOW());" >nul 2>&1

REM Execute the mysqldump commands for both locations - use optimized parameters
echo Creating backups... Please wait.
"!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH1!\%DB_NAME%_backup_%TIMESTAMP%.sql"

REM Only create second backup if first one succeeded
if %ERRORLEVEL% == 0 (
    echo Primary backup successful.
    copy "!BACKUP_PATH1!\%DB_NAME%_backup_%TIMESTAMP%.sql" "!BACKUP_PATH2!\%DB_NAME%_backup_%TIMESTAMP%.sql" >nul
    if %ERRORLEVEL% == 0 (
        echo Secondary backup successful.
    ) else (
        echo Creating second backup independently...
        "!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH2!\%DB_NAME%_backup_%TIMESTAMP%.sql"
    )
) else (
    echo Primary backup failed, trying secondary location...
    "!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH2!\%DB_NAME%_backup_%TIMESTAMP%.sql"
)

echo Backup process completed at: %time%
endlocal