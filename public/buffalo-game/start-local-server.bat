@echo off
echo ========================================
echo Buffalo Game - Local HTTP Server
echo ========================================
echo.
echo Starting server on http://localhost:8000
echo.
echo Open this URL in your browser:
echo http://localhost:8000/index.html
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

python -m http.server 8000

pause

