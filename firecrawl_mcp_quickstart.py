#!/usr/bin/env python3
"""
Quick start script for Firecrawl MCP setup and testing
"""

import os
import sys
import subprocess
import json
import asyncio
from pathlib import Path
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

def print_header(text):
    """Print formatted header"""
    print(f"\n{'='*60}")
    print(f"  {text}")
    print(f"{'='*60}\n")

def print_step(step_num, text):
    """Print step"""
    print(f"[{step_num}] {text}")

def check_python():
    """Check Python version"""
    print_step(1, "Checking Python version...")
    version = sys.version_info
    if version.major >= 3 and version.minor >= 9:
        print(f"✓ Python {version.major}.{version.minor}.{version.micro}")
        return True
    else:
        print(f"✗ Python 3.9+ required (found {version.major}.{version.minor})")
        return False

def check_dependencies():
    """Check required dependencies"""
    print_step(2, "Checking dependencies...")
    
    required = {
        'playwright': 'playwright',
        'bs4': 'beautifulsoup4',
        'requests': 'requests',
        'dotenv': 'python-dotenv'
    }
    
    missing = []
    for module, package in required.items():
        try:
            __import__(module)
            print(f"✓ {package}")
        except ImportError:
            print(f"✗ {package}")
            missing.append(package)
    
    if missing:
        print(f"\nInstalling missing packages: {', '.join(missing)}")
        subprocess.run([sys.executable, '-m', 'pip', 'install'] + missing, check=True)
        return True
    return True

def install_playwright_browsers():
    """Install Playwright browsers"""
    print_step(3, "Installing Playwright browsers...")
    try:
        subprocess.run([sys.executable, '-m', 'playwright', 'install'], check=True)
        print("✓ Playwright browsers installed")
        return True
    except subprocess.CalledProcessError:
        print("✗ Failed to install Playwright browsers")
        return False

def check_env_file():
    """Check and create .env file if needed"""
    print_step(4, "Checking environment configuration...")
    
    env_file = Path('.env')
    if env_file.exists():
        print("✓ .env file exists")
        api_key = os.getenv('FIRECRAWL_API_KEY')
        if api_key:
            print(f"✓ FIRECRAWL_API_KEY is set")
            return True
        else:
            print("✗ FIRECRAWL_API_KEY not found in .env")
    else:
        print("✗ .env file not found")
    
    # Create .env file with template
    print("\nCreating .env file template...")
    env_content = """# Firecrawl MCP Configuration
FIRECRAWL_API_KEY=your_api_key_here
FIRECRAWL_BASE_URL=https://api.firecrawl.dev
FIRECRAWL_DEBUG=false
FIRECRAWL_DEFAULT_TIMEOUT=30
FIRECRAWL_DEFAULT_DEPTH=1
FIRECRAWL_INCLUDE_MARKDOWN=true

# Server Configuration
FIRECRAWL_SERVER_HOST=localhost
FIRECRAWL_SERVER_PORT=3000
"""
    
    env_file.write_text(env_content)
    print(f"✓ Created .env template")
    print(f"  Please add your FIRECRAWL_API_KEY to .env file")
    return False

def test_firecrawl_server():
    """Test Firecrawl server"""
    print_step(5, "Testing Firecrawl MCP Server...")
    
    test_url = "https://httpbin.org/html"
    
    try:
        from firecrawl_mcp_server import FirecrawlMCPServer
        
        async def test():
            server = FirecrawlMCPServer(debug=True)
            print(f"Testing crawl of {test_url}...")
            result = await server.client.crawl(test_url, depth=1, include_markdown=False)
            print(f"✓ Server test passed")
            print(f"  Status: {result.status_code}")
            print(f"  Content length: {len(result.content)} bytes")
            return True
        
        asyncio.run(test())
        return True
    except Exception as e:
        print(f"✗ Server test failed: {e}")
        return False

def create_config():
    """Create Firecrawl configuration"""
    print_step(6, "Creating Firecrawl configuration...")
    
    config = {
        "name": "firecrawl-mcp",
        "version": "1.0.0",
        "api_endpoints": {
            "crawl": "/api/v1/crawl",
            "scrape": "/api/v1/scrape",
            "extract": "/api/v1/extract"
        },
        "timeouts": {
            "default": 30,
            "max": 300
        },
        "features": {
            "markdown_conversion": True,
            "link_extraction": True,
            "metadata_extraction": True,
            "schema_validation": True
        }
    }
    
    config_file = Path('firecrawl_config.json')
    config_file.write_text(json.dumps(config, indent=2))
    print(f"✓ Configuration created: {config_file}")
    return True

def show_next_steps():
    """Show next steps"""
    print_header("Setup Complete!")
    print("""
Next Steps:

1. Update your .env file with FIRECRAWL_API_KEY:
   - Visit https://firecrawl.dev
   - Sign up and create an API key
   - Add it to .env: FIRECRAWL_API_KEY=your_key_here

2. Start the Firecrawl MCP Server:
   python firecrawl_mcp_server.py

3. Test the server in another terminal:
   python firecrawl_mcp_server.py --test-crawl https://example.com

4. Use in your application:
   from firecrawl_mcp_server import FirecrawlMCPServer
   server = FirecrawlMCPServer()
   result = await server.client.crawl("https://example.com")

5. Read the documentation:
   - FIRECRAWL_MCP_SETUP.md - Complete setup guide
   - firecrawl_mcp_config.json - Configuration options
   - app/Services/FirecrawlService.py - Integration example

Resources:
   - Firecrawl Docs: https://docs.firecrawl.dev
   - MCP Protocol: https://modelcontextprotocol.io
   - Source: firecrawl_mcp_server.py

Questions? Check FIRECRAWL_MCP_SETUP.md for troubleshooting.
    """)

def main():
    """Main setup function"""
    print_header("Firecrawl MCP Quick Start Setup")
    
    steps = [
        check_python,
        check_dependencies,
        install_playwright_browsers,
        check_env_file,
        create_config,
        test_firecrawl_server,
    ]
    
    for step in steps:
        try:
            if not step():
                print(f"\n⚠ Warning: {step.__name__} had issues")
        except Exception as e:
            print(f"✗ Error in {step.__name__}: {e}")
            return False
    
    show_next_steps()
    return True

if __name__ == '__main__':
    success = main()
    sys.exit(0 if success else 1)
