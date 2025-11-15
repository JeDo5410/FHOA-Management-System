@echo off
setlocal EnableDelayedExpansion

REM Set database credentials and backup locations
set DB_USER=root
set DB_PASSWORD=1234
set DB_NAME=fhoa
set BACKUP_PATH1=C:\MySQL_Backup
set BACKUP_PATH2=F:\MySQL_Backup
set BACKUP_PATH3=C:\Users\Fortezza Admin\OneDrive
set MAX_BACKUPS=10
set MYSQL_PATH=C:\Program Files\MySQL\MySQL Server 8.0\bin

REM Create timestamp for unique filename
set TIMESTAMP=%date:~10,4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=!TIMESTAMP: =0!

REM Create backup directories if they don't exist
if not exist "!BACKUP_PATH1!" mkdir "!BACKUP_PATH1!"
if not exist "!BACKUP_PATH2!" mkdir "!BACKUP_PATH2!"
if not exist "!BACKUP_PATH3!" mkdir "!BACKUP_PATH3!"

REM Clean up old backups
echo Cleaning up old backups...
for %%P in ("!BACKUP_PATH1!" "!BACKUP_PATH2!" "!BACKUP_PATH3!") do (
    set COUNT=0
    for /f "tokens=*" %%F in ('dir /b /o-d "%%~P\%DB_NAME%_backup_*.sql" 2^>nul') do (
        set /a COUNT+=1
        if !COUNT! geq %MAX_BACKUPS% (
            del "%%~P\%%F" >nul 2>&1
        )
    )
)

REM Initialize status variables
set STATUS_PATH1=FAILED
set STATUS_PATH2=FAILED
set STATUS_PATH3=FAILED

REM Create backup in BACKUP_PATH1
echo Creating backup in primary location...
"!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH1!\%DB_NAME%_backup_%TIMESTAMP%.sql"
if %ERRORLEVEL% == 0 (
    echo Primary backup successful.
    set STATUS_PATH1=SUCCESS
) else (
    echo Primary backup failed.
)

REM Create backup in BACKUP_PATH2
echo Creating backup in secondary location...
"!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH2!\%DB_NAME%_backup_%TIMESTAMP%.sql"
if %ERRORLEVEL% == 0 (
    echo Secondary backup successful.
    set STATUS_PATH2=SUCCESS
) else (
    echo Secondary backup failed.
)

REM Create backup in BACKUP_PATH3
echo Creating backup in OneDrive location...
"!MYSQL_PATH!\mysqldump" --user=!DB_USER! --password=!DB_PASSWORD! --single-transaction --quick --databases %DB_NAME% --result-file="!BACKUP_PATH3!\%DB_NAME%_backup_%TIMESTAMP%.sql"
if %ERRORLEVEL% == 0 (
    echo OneDrive backup successful.
    set STATUS_PATH3=SUCCESS
) else (
    echo OneDrive backup failed.
)

REM Build log message
set LOG_MESSAGE=Database BACKUP

REM Insert log entry into activity_log table with dynamic status
echo Logging backup status to database...
"!MYSQL_PATH!\mysql" --user=!DB_USER! --password=!DB_PASSWORD! -e "INSERT INTO fhoa.activity_log (Process, timestamp) VALUES('!LOG_MESSAGE!', NOW());"
if %ERRORLEVEL% == 0 (
    echo Database log entry created successfully.
) else (
    echo Warning: Failed to create database log entry.
)

echo.
echo ================================================
echo Backup Summary:
echo Primary Location: !STATUS_PATH1!
echo Secondary Location: !STATUS_PATH2!
echo OneDrive Location: !STATUS_PATH3!
echo ================================================
echo Backup process completed at: %time%
endlocal