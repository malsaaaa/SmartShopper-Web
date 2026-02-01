@echo off
REM Firecrawl MCP Quick Start Script (Windows)
REM This script sets up and starts the Firecrawl MCP Server

setlocal enabledelayedexpansion

echo.
echo =========================================================
echo     Firecrawl MCP Server Startup
echo =========================================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo Error: Python is not installed or not in PATH
    echo Please install Python 3.9+ from python.org
    pause
    exit /b 1
)

echo [OK] Python is installed
python --version

REM Check if .env file exists
if not exist ".env" (
    echo.
    echo Warning: .env file not found
    echo Creating .env from .env.example...
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo [OK] .env file created
    ) else (
        echo Error: .env.example not found
        pause
        exit /b 1
    )
)

REM Check if FIRECRAWL_API_KEY is set
for /f "tokens=2 delims==" %%a in ('findstr /i "FIRECRAWL_API_KEY" .env') do set API_KEY=%%a
if "!API_KEY!"=="" (
    echo.
    echo Warning: FIRECRAWL_API_KEY not configured in .env
    echo Please add your API key to the .env file:
    echo   FIRECRAWL_API_KEY=your_api_key_here
    echo.
    echo Get your API key from https://firecrawl.dev
    pause
)

REM Run quick start script
echo.
echo Running setup verification...
python firecrawl_mcp_quickstart.py

if errorlevel 1 (
    echo.
    echo Setup verification failed. Fix the issues above and try again.
    pause
    exit /b 1
)

REM Start the server
echo.
echo =========================================================
echo     Starting Firecrawl MCP Server...
echo =========================================================
echo.
echo Server will run at: http://localhost:3000
echo Press Ctrl+C to stop the server
echo.

python firecrawl_mcp_server.py

pause
