#!/usr/bin/env python3
"""
ChromeDriver Auto-Downloader
Automatically downloads the correct ChromeDriver version matching your Chrome browser
"""

import os
import sys
import subprocess
import json
import urllib.request
import shutil
import zipfile
from pathlib import Path

def get_chrome_version():
    """Get the installed Chrome version"""
    try:
        result = subprocess.run(
            ['powershell', '-Command', 
             r'(Get-ItemProperty "HKLM:\SOFTWARE\Google\Chrome\Binaries" -Name "Version").Version'],
            capture_output=True, text=True, timeout=5
        )
        if result.returncode == 0:
            version = result.stdout.strip()
            print(f"✓ Chrome version found: {version}")
            return version
    except Exception as e:
        print(f"✗ Error reading Chrome version: {e}")
    
    return None

def get_chromedriver_version():
    """Get the installed ChromeDriver version"""
    try:
        result = subprocess.run(['chromedriver', '--version'], capture_output=True, text=True, timeout=5)
        if result.returncode == 0:
            version_str = result.stdout.strip()
            version = version_str.split()[1]
            print(f"✓ ChromeDriver version found: {version}")
            return version
    except Exception as e:
        print(f"✗ ChromeDriver not found: {e}")
    
    return None

def download_chromedriver(chrome_version):
    """Download ChromeDriver matching Chrome version"""
    print(f"\n📥 Downloading ChromeDriver for Chrome {chrome_version}...")
    
    # Extract major version (e.g., 144 from 144.0.7559.110)
    major_version = chrome_version.split('.')[0]
    
    # Use Chrome for Testing API to get download URL
    api_url = f"https://googlechromelabs.github.io/chrome-for-testing/latest-versions-per-milestone.json"
    
    try:
        with urllib.request.urlopen(api_url, timeout=10) as response:
            data = json.loads(response.read().decode())
        
        # Find the matching version
        if major_version in data["milestones"]:
            release = data["milestones"][major_version]["downloads"]["chromedriver"]
            if release:
                url = release[0]["url"]
                print(f"✓ Found ChromeDriver download URL")
                
                # Download
                download_path = "chromedriver.zip"
                print(f"⏳ Downloading from: {url}")
                urllib.request.urlretrieve(url, download_path)
                print(f"✓ Downloaded: {download_path}")
                
                # Extract
                extract_dir = "chromedriver_temp"
                with zipfile.ZipFile(download_path, 'r') as zip_ref:
                    zip_ref.extractall(extract_dir)
                print(f"✓ Extracted to: {extract_dir}")
                
                # Find chromedriver executable
                for root, dirs, files in os.walk(extract_dir):
                    for file in files:
                        if file == "chromedriver.exe" or file == "chromedriver":
                            src = os.path.join(root, file)
                            dst = "chromedriver.exe"
                            shutil.copy(src, dst)
                            os.chmod(dst, 0o755)
                            print(f"✓ ChromeDriver installed: {dst}")
                            
                            # Cleanup
                            shutil.rmtree(extract_dir)
                            os.remove(download_path)
                            
                            return dst
            else:
                print(f"✗ No ChromeDriver release found for Chrome {major_version}")
        else:
            print(f"✗ Chrome version {major_version} not found in database")
            
    except Exception as e:
        print(f"✗ Error downloading ChromeDriver: {e}")
    
    return None

def add_to_path():
    """Add current directory to PATH"""
    current_dir = os.getcwd()
    print(f"\n📝 To add ChromeDriver to PATH, run this in PowerShell (as Administrator):")
    print(f"   $env:PATH += ';{current_dir}'")
    print(f"   Or permanently add: {current_dir}")

def main():
    print("=" * 60)
    print("ChromeDriver Auto-Downloader & Setup")
    print("=" * 60)
    
    chrome_version = get_chrome_version()
    chromedriver_version = get_chromedriver_version()
    
    if chrome_version and chromedriver_version:
        chrome_major = chrome_version.split('.')[0]
        driver_major = chromedriver_version.split('.')[0]
        
        if chrome_major == driver_major:
            print(f"\n✓ ChromeDriver is compatible with Chrome!")
            return
        else:
            print(f"\n⚠️  Version mismatch:")
            print(f"   Chrome: {chrome_version} (v{chrome_major})")
            print(f"   ChromeDriver: {chromedriver_version} (v{driver_major})")
    
    if chrome_version:
        chromedriver_path = download_chromedriver(chrome_version)
        if chromedriver_path:
            print(f"\n✓ ChromeDriver setup complete!")
            add_to_path()
            
            # Test
            print(f"\n🧪 Testing...")
            result = subprocess.run(['py', 'scrapers/shopee_crawler.py', 'milo', '5'], 
                                  capture_output=True, text=True, timeout=60)
            if result.returncode == 0:
                print("✓ Crawler test successful!")
            else:
                print(f"⚠️  Crawler test output:\n{result.stderr}")
        else:
            print(f"\n✗ Could not download ChromeDriver")
            print(f"   Download manually from: https://googlechromelabs.github.io/chrome-for-testing/")
    else:
        print(f"\n✗ Could not detect Chrome version")
        print(f"   Ensure Chrome is installed properly")

if __name__ == '__main__':
    main()
