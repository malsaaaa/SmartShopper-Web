#!/usr/bin/env python3
"""
Firecrawl MCP - Setup Verification & Status Report
Provides a complete overview of the installation
"""

import os
import sys
import json
from pathlib import Path
from datetime import datetime

def print_banner():
    """Print welcome banner"""
    banner = """
╔════════════════════════════════════════════════════════════╗
║          Firecrawl MCP - Setup Verification Report         ║
║                    Status Dashboard                         ║
╚════════════════════════════════════════════════════════════╝
"""
    print(banner)

def print_section(title):
    """Print section header"""
    print(f"\n{'─' * 60}")
    print(f"  {title}")
    print(f"{'─' * 60}\n")

def check_file(path, description):
    """Check if file exists and print status"""
    full_path = Path(path)
    status = "✓" if full_path.exists() else "✗"
    print(f"  {status} {description:<40} {path}")
    return full_path.exists()

def check_directory(path, description):
    """Check if directory exists"""
    full_path = Path(path)
    status = "✓" if full_path.exists() else "✗"
    print(f"  {status} {description:<40} {path}")
    return full_path.exists()

def get_file_size(path):
    """Get file size in KB"""
    try:
        size = Path(path).stat().st_size / 1024
        return f"{size:.1f} KB"
    except:
        return "N/A"

def main():
    print_banner()
    
    # System Information
    print_section("System Information")
    print(f"  Python Version:     {sys.version.split()[0]}")
    print(f"  Platform:           {sys.platform}")
    print(f"  Current Directory:  {os.getcwd()}")
    print(f"  Timestamp:          {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    
    # Core Files
    print_section("Core Files")
    core_files = [
        ("firecrawl_mcp_server.py", "Main MCP Server"),
        ("firecrawl_mcp_client.js", "Node.js CLI Client"),
        ("firecrawl_mcp_quickstart.py", "Automated Setup"),
    ]
    
    core_status = []
    for file, desc in core_files:
        status = check_file(file, desc)
        core_status.append(status)
        print(f"    Size: {get_file_size(file)}")
    
    # Configuration Files
    print_section("Configuration Files")
    config_files = [
        ("firecrawl_mcp_config.json", "MCP Configuration"),
        (".env", "Environment Variables"),
        (".env.example", "Environment Template"),
        ("requirements.txt", "Python Dependencies"),
    ]
    
    config_status = []
    for file, desc in config_files:
        status = check_file(file, desc)
        config_status.append(status)
        if file == ".env" and status:
            # Check for API key
            try:
                with open(file) as f:
                    has_key = "FIRECRAWL_API_KEY" in f.read()
                    key_status = "✓ Has API Key" if has_key else "✗ Missing API Key"
                    print(f"    {key_status}")
            except:
                pass
    
    # Documentation
    print_section("Documentation")
    docs = [
        ("FIRECRAWL_MCP_SETUP.md", "Setup Guide (350+ lines)"),
        ("FIRECRAWL_MCP_QUICK_REFERENCE.md", "Quick Reference"),
        ("FIRECRAWL_MCP_INDEX.md", "Complete Index"),
    ]
    
    docs_status = []
    for file, desc in docs:
        status = check_file(file, desc)
        docs_status.append(status)
        print(f"    Size: {get_file_size(file)}")
    
    # Services and Integration
    print_section("Integration & Services")
    services = [
        ("app/Services/FirecrawlService.py", "Laravel Service Layer"),
    ]
    
    services_status = []
    for file, desc in services:
        status = check_file(file, desc)
        services_status.append(status)
        print(f"    Size: {get_file_size(file)}")
    
    # Testing
    print_section("Testing")
    tests = [
        ("tests/test_firecrawl_mcp.py", "Test Suite"),
    ]
    
    tests_status = []
    for file, desc in tests:
        status = check_file(file, desc)
        tests_status.append(status)
        print(f"    Size: {get_file_size(file)}")
    
    # Startup Scripts
    print_section("Startup Scripts")
    scripts = [
        ("start_firecrawl_mcp.bat", "Windows Startup"),
        ("start_firecrawl_mcp.sh", "Linux/macOS Startup"),
    ]
    
    scripts_status = []
    for file, desc in scripts:
        status = check_file(file, desc)
        scripts_status.append(status)
    
    # Directories
    print_section("Directories")
    dirs = [
        ("app/Services", "Service Layer"),
        ("tests", "Test Suite"),
        ("scrapers", "Scraper Scripts"),
    ]
    
    for dir_path, desc in dirs:
        check_directory(dir_path, desc)
    
    # Summary
    print_section("Installation Summary")
    
    all_status = core_status + config_status + docs_status + services_status + tests_status + scripts_status
    total = len(all_status)
    installed = sum(all_status)
    percentage = (installed / total * 100) if total > 0 else 0
    
    print(f"  Total Files:        {installed}/{total}")
    print(f"  Completion:         {percentage:.0f}%")
    
    if percentage == 100:
        print(f"\n  ✓ Installation Complete!")
        print(f"  ✓ All files and configurations are in place")
    elif percentage >= 80:
        print(f"\n  ⚠ Installation Mostly Complete")
        print(f"  ⚠ Some optional files may be missing")
    else:
        print(f"\n  ✗ Installation Incomplete")
        print(f"  ✗ Please complete the setup")
    
    # Quick Commands
    print_section("Quick Commands")
    print("""
  1. Start Server (Windows):
     start_firecrawl_mcp.bat
  
  2. Start Server (Linux/macOS):
     ./start_firecrawl_mcp.sh
  
  3. Run Setup Verification:
     python firecrawl_mcp_quickstart.py
  
  4. Start Server Directly:
     python firecrawl_mcp_server.py
  
  5. Test a URL:
     python firecrawl_mcp_server.py --test-crawl https://example.com
  
  6. Run Tests:
     pytest tests/test_firecrawl_mcp.py -v
  
  7. Use CLI Client:
     node firecrawl_mcp_client.js crawl --url https://example.com
""")
    
    # Configuration Status
    print_section("Configuration Status")
    
    if Path(".env").exists():
        try:
            with open(".env") as f:
                env_content = f.read()
                has_api_key = "FIRECRAWL_API_KEY" in env_content
                has_server_host = "FIRECRAWL_SERVER_HOST" in env_content
                has_server_port = "FIRECRAWL_SERVER_PORT" in env_content
                
                print(f"  {'✓' if has_api_key else '✗'} API Key configured")
                print(f"  {'✓' if has_server_host else '✗'} Server host configured")
                print(f"  {'✓' if has_server_port else '✗'} Server port configured")
                
                if not has_api_key:
                    print("\n  ⚠ Action Required:")
                    print("    Add FIRECRAWL_API_KEY to .env file")
                    print("    Get key from: https://firecrawl.dev")
        except Exception as e:
            print(f"  Error reading .env: {e}")
    else:
        print("  ✗ .env file not found")
        print("  Create from .env.example: cp .env.example .env")
    
    # Next Steps
    print_section("Next Steps")
    print("""
  1. [ ] Configure Environment:
         Copy .env.example to .env
         Add FIRECRAWL_API_KEY from https://firecrawl.dev
  
  2. [ ] Install Dependencies:
         pip install -r requirements.txt
  
  3. [ ] Install Playwright Browsers:
         python -m playwright install
  
  4. [ ] Run Setup Verification:
         python firecrawl_mcp_quickstart.py
  
  5. [ ] Start the Server:
         python firecrawl_mcp_server.py
  
  6. [ ] Test Functionality:
         python firecrawl_mcp_server.py --test-crawl https://example.com
  
  7. [ ] Read Documentation:
         See FIRECRAWL_MCP_SETUP.md for detailed guide
""")
    
    # Resources
    print_section("Resources & Support")
    print("""
  Documentation:
    • Complete Setup: FIRECRAWL_MCP_SETUP.md
    • Quick Reference: FIRECRAWL_MCP_QUICK_REFERENCE.md
    • Full Index: FIRECRAWL_MCP_INDEX.md
  
  External Resources:
    • Firecrawl Official: https://firecrawl.dev
    • Firecrawl Docs: https://docs.firecrawl.dev
    • MCP Protocol: https://modelcontextprotocol.io
    • Playwright: https://playwright.dev
  
  Troubleshooting:
    See "Troubleshooting" section in FIRECRAWL_MCP_SETUP.md
""")
    
    # Footer
    print("\n" + "="*60)
    print(f"  Status: {'✅ Ready' if percentage == 100 else '⚠️ Review Config'}")
    print(f"  Version: 1.0.0 | Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("="*60 + "\n")

if __name__ == '__main__':
    try:
        main()
    except KeyboardInterrupt:
        print("\n\nInterrupted by user")
        sys.exit(0)
    except Exception as e:
        print(f"Error: {e}")
        sys.exit(1)
