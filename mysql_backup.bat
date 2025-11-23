@echo off
setlocal EnableDelayedExpansion

REM ============================================================================
REM CONFIGURATION
REM ============================================================================
set DB_USER=root
set DB_PASSWORD=1234
set DB_NAME=fhoa
set BACKUP_PATH1=C:\MySQL_Backup
set BACKUP_PATH2=F:\MySQL_Backup
set MYSQL_PATH=C:\Program Files\MySQL\MySQL Server 8.0\bin

REM Local Backup Limit
set MAX_BACKUPS=10

REM === Google Drive Configuration ===
set RCLONE_PATH=rclone
set GDRIVE_FOLDER=MySQL_Backups
set MAX_GDRIVE_BACKUPS=10
set TEMP_LIST=%TEMP%\gdrive_filelist.txt

REM ============================================================================
REM PREPARATION
REM ============================================================================

REM Create timestamp for unique filename (YYYYMMDD_HHMMSS)
set TIMESTAMP=%date:~10,4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=!TIMESTAMP: =0!

REM Create backup directories if they don't exist
if not exist "!BACKUP_PATH1!" mkdir "!BACKUP_PATH1!" 2>nul
if not exist "!BACKUP_PATH2!" mkdir "!BACKUP_PATH2!" 2>nul

REM Pull latest changes from git
echo Pulling latest changes from git...
git pull

REM Initialize status variables
set STATUS_PATH1=FAILED
set STATUS_PATH2=FAILED
set STATUS_GDRIVE=FAILED

REM ============================================================================
REM CREATE LOCAL BACKUPS
REM ============================================================================

REM Create backup in BACKUP_PATH1
echo Creating backup in primary location...
"!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH1!\%DB_NAME%_backup_%TIMESTAMP%.sql"
if !ERRORLEVEL! == 0 (
    echo Primary backup successful.
    set STATUS_PATH1=SUCCESS
) else (
    echo Primary backup failed.
)

REM Create backup in BACKUP_PATH2
echo Creating backup in secondary location ^(F: Drive^)...
REM Check if drive exists first to avoid error 2
if exist "!BACKUP_PATH2!" (
    "!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH2!\%DB_NAME%_backup_%TIMESTAMP%.sql"
    if !ERRORLEVEL! == 0 (
        echo Secondary backup successful.
        set STATUS_PATH2=SUCCESS
    ) else (
        echo Secondary backup failed ^(Dump error^).
    )
) else (
    echo Secondary backup failed ^(Path not found - Check F: Drive^).
)

REM ============================================================================
REM LOCAL BACKUP CLEANUP
REM ============================================================================
echo.
echo ================================================
echo Cleaning up old local backups...
echo ================================================

REM Process each backup location
for %%P in ("!BACKUP_PATH1!" "!BACKUP_PATH2!") do (
    if exist "%%~P" (
        echo Checking: %%~P

        REM Count backup files in this directory
        set /a file_count=0
        for /f "tokens=*" %%F in ('dir /b /o-d "%%~P\%DB_NAME%_backup_*.sql" 2^>nul') do (
            set /a file_count+=1
        )

        echo   Files found: !file_count! ^(Limit: !MAX_BACKUPS!^)

        REM If we exceed the limit, delete oldest files
        if !file_count! gtr !MAX_BACKUPS! (
            set /a files_to_delete=!file_count! - !MAX_BACKUPS!
            echo   Removing !files_to_delete! oldest file^(s^)...

            set /a del_counter=0

            REM List files in ascending order (oldest first) and delete excess
            for /f "tokens=*" %%F in ('dir /b /o-d "%%~P\%DB_NAME%_backup_*.sql" 2^>nul ^| sort') do (
                set /a del_counter+=1
                if !del_counter! leq !files_to_delete! (
                    echo   Deleting: %%F
                    del "%%~P\%%F" >nul 2>&1
                )
            )
        ) else (
            echo   Storage limit not reached.
        )
    )
)

echo ================================================
echo.

REM ============================================================================
REM GOOGLE DRIVE CLEANUP & UPLOAD
REM ============================================================================
echo.
echo ================================================
echo Processing Google Drive...
echo ================================================

REM 1. Ensure the folder exists in Google Drive
echo Checking Google Drive folder...
"!RCLONE_PATH!" mkdir "gdrive:!GDRIVE_FOLDER!/" 2>nul

REM 2. List files from GDrive into a temporary file
REM We use simple 'lsf' which lists names.
REM We pipe to 'sort' to order them alphabetically. Since your filenames are YYYYMMDD, alphabetical = chronological.
"!RCLONE_PATH!" lsf "gdrive:!GDRIVE_FOLDER!/" --files-only 2>nul | sort > "!TEMP_LIST!"

if !ERRORLEVEL! neq 0 (
    echo WARNING: Failed to list Google Drive files. Check rclone configuration.
    set STATUS_GDRIVE=FAILED ^(List operation failed^)
    goto :skip_gdrive
)

REM 2. Count files
set /a gdrive_count=0
for /f "usebackq tokens=*" %%A in ("!TEMP_LIST!") do (
    set /a gdrive_count+=1
)

echo Current files in Drive: !gdrive_count! (Limit: !MAX_GDRIVE_BACKUPS!)

REM 3. Check limit and delete oldest
if !gdrive_count! geq !MAX_GDRIVE_BACKUPS! (
    set /a files_to_delete=!gdrive_count! - !MAX_GDRIVE_BACKUPS! + 1
    echo Limit reached. Removing !files_to_delete! oldest file^(s^)...

    set /a del_counter=0
    
    REM Read the sorted temp file again
    for /f "usebackq tokens=*" %%F in ("!TEMP_LIST!") do (
        set /a del_counter+=1
        if !del_counter! leq !files_to_delete! (
            echo Deleting old file: %%F
            "!RCLONE_PATH!" deletefile "gdrive:!GDRIVE_FOLDER!/%%F"
        )
    )
) else (
    echo Storage limit not reached. Proceeding...
)

REM Clean up temp list
if exist "!TEMP_LIST!" del "!TEMP_LIST!"

REM 4. Upload new backup
REM We prefer to upload the one from BACKUP_PATH1 as it is the primary
set LATEST_BACKUP=!BACKUP_PATH1!\%DB_NAME%_backup_%TIMESTAMP%.sql

if exist "!LATEST_BACKUP!" (
    echo.
    echo Uploading: !LATEST_BACKUP!
    echo Destination: gdrive:!GDRIVE_FOLDER!/
    
    "!RCLONE_PATH!" copy "!LATEST_BACKUP!" gdrive:!GDRIVE_FOLDER!/ --progress --retries 3
    
    REM Using !ERRORLEVEL! is crucial here because we are inside a block
    if !ERRORLEVEL! == 0 (
        echo.
        echo Google Drive upload: SUCCESS
        set STATUS_GDRIVE=SUCCESS
    ) else (
        echo.
        echo Google Drive upload: FAILED ^(Error Code: !ERRORLEVEL!^)
        set STATUS_GDRIVE=FAILED
    )
) else (
    echo ERROR: Backup file to upload was not found!
    set STATUS_GDRIVE=FAILED
)

echo ================================================
echo.

:skip_gdrive

REM ============================================================================
REM LOGGING & SUMMARY
REM ============================================================================

REM Build log message
set LOG_MESSAGE=Database BACKUP

REM Insert log entry into activity_log table
echo Logging backup status to database...
"!MYSQL_PATH!\mysql" --user=!DB_USER! --password=!DB_PASSWORD! -e "INSERT INTO fhoa.activity_log (Process, timestamp) VALUES('!LOG_MESSAGE!', NOW());"

echo.
echo ================================================
echo Backup Summary:
echo ================================================
echo Primary Location:    !STATUS_PATH1!
echo Secondary Location:  !STATUS_PATH2!
echo Google Drive:        !STATUS_GDRIVE!
echo ================================================
echo Backup process completed at: %time%
endlocal