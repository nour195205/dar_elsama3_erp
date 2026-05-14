@echo off
set "PHP_EXE=%~dp0php_portable\php.exe"
set "ARTISAN=%~dp0artisan"

echo Starting Dar El Samea ERP Web Server...
echo.
echo Open your browser at: http://localhost:8000
echo.

start "" http://localhost:8000
"%PHP_EXE%" "%ARTISAN%" serve --port=8000
pause
