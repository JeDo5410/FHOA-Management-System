@echo off
set PROJECT_PATH=C:\Apache\htdocs\fhoa-app
cd /d %PROJECT_PATH%

:: Check if the server is already running on port 8000
netstat -ano | find "LISTENING" | find ":8000" > nul
if %errorlevel% equ 0 (
    start chrome http://localhost:8000
    exit
)

:: If no server is running, start a new one (hidden)
start /B /MIN "" cmd /c "php artisan serve --no-interaction > nul 2>&1"

:: Wait for server initialization
timeout /t 3 /nobreak > nul

:: Open Chrome
start chrome http://localhost:8000

exit