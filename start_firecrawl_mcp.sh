#!/bin/bash
# Firecrawl MCP Quick Start Script (Linux/macOS)
# This script sets up and starts the Firecrawl MCP Server

set -e

echo ""
echo "========================================================="
echo "     Firecrawl MCP Server Startup"
echo "========================================================="
echo ""

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "Error: Python 3 is not installed"
    echo "Please install Python 3.9+ from python.org"
    exit 1
fi

echo "[OK] Python is installed"
python3 --version

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo ""
    echo "Warning: .env file not found"
    echo "Creating .env from .env.example..."
    
    if [ -f ".env.example" ]; then
        cp ".env.example" ".env"
        echo "[OK] .env file created"
    else
        echo "Error: .env.example not found"
        exit 1
    fi
fi

# Check if FIRECRAWL_API_KEY is set
if ! grep -q "FIRECRAWL_API_KEY" .env || grep -q "FIRECRAWL_API_KEY=your_api_key_here" .env; then
    echo ""
    echo "Warning: FIRECRAWL_API_KEY not configured in .env"
    echo "Please add your API key to the .env file:"
    echo "  FIRECRAWL_API_KEY=your_api_key_here"
    echo ""
    echo "Get your API key from https://firecrawl.dev"
    echo ""
fi

# Run quick start script
echo ""
echo "Running setup verification..."
python3 firecrawl_mcp_quickstart.py

# Start the server
echo ""
echo "========================================================="
echo "     Starting Firecrawl MCP Server..."
echo "========================================================="
echo ""
echo "Server will run at: http://localhost:3000"
echo "Press Ctrl+C to stop the server"
echo ""

python3 firecrawl_mcp_server.py
