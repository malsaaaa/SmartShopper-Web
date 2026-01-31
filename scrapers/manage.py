#!/usr/bin/env python3
"""
SmartShopper Scraper Manager
Utility script for managing scraping operations
"""

import argparse
import json
import subprocess
import sys
from datetime import datetime
from pathlib import Path

class ScraperManager:
    def __init__(self):
        self.base_path = Path(__file__).parent
        self.log_file = self.base_path / 'scraper.log'
        self.data_file = self.base_path / 'scraped_data.json'

    def run_scraper(self):
        """Run the scraper"""
        print("🚀 Running scraper...")
        try:
            result = subprocess.run(
                [sys.executable, str(self.base_path / 'scrape_products.py')],
                capture_output=True,
                text=True,
                timeout=300
            )
            
            if result.returncode == 0:
                data = json.loads(result.stdout)
                print(f"✅ Scraper completed successfully")
                print(f"   Found {len(data)} items")
                
                # Save to file
                with open(self.data_file, 'w') as f:
                    json.dump(data, f, indent=2)
                print(f"   Data saved to: {self.data_file}")
                
                return 0
            else:
                print(f"❌ Scraper failed")
                print(f"   Error: {result.stderr}")
                return 1
                
        except subprocess.TimeoutExpired:
            print("❌ Scraper timed out (exceeded 5 minutes)")
            return 1
        except Exception as e:
            print(f"❌ Error running scraper: {e}")
            return 1

    def test_scraper(self):
        """Test the scraper setup"""
        print("🧪 Testing scraper setup...")
        try:
            result = subprocess.run(
                [sys.executable, str(self.base_path / 'test_scraper.py')],
                capture_output=True,
                text=True,
                timeout=60
            )
            print(result.stdout)
            return result.returncode
        except Exception as e:
            print(f"❌ Error running tests: {e}")
            return 1

    def show_logs(self, lines=50):
        """Show recent logs"""
        if not self.log_file.exists():
            print("📭 No logs yet")
            return
        
        print(f"📋 Last {lines} log entries:\n")
        try:
            with open(self.log_file, 'r') as f:
                log_lines = f.readlines()
            
            # Show last N lines
            for line in log_lines[-lines:]:
                print(line.rstrip())
        except Exception as e:
            print(f"❌ Error reading logs: {e}")

    def show_data(self):
        """Show latest scraped data"""
        if not self.data_file.exists():
            print("📭 No data scraped yet")
            return
        
        print("📊 Latest scraped data:\n")
        try:
            with open(self.data_file, 'r') as f:
                data = json.load(f)
            
            print(f"Total items: {len(data)}\n")
            
            # Group by retailer
            by_retailer = {}
            for item in data:
                retailer = item.get('retailer', 'Unknown')
                if retailer not in by_retailer:
                    by_retailer[retailer] = []
                by_retailer[retailer].append(item)
            
            # Display by retailer
            for retailer, items in sorted(by_retailer.items()):
                print(f"🏪 {retailer}: {len(items)} items")
                for item in items[:3]:  # Show first 3
                    print(f"   - {item['name']}: RM {item['price']:.2f}")
                if len(items) > 3:
                    print(f"   ... and {len(items) - 3} more")
                print()
        except Exception as e:
            print(f"❌ Error reading data: {e}")

    def clear_logs(self):
        """Clear log file"""
        if self.log_file.exists():
            self.log_file.unlink()
            print("🗑️  Logs cleared")
        else:
            print("✓ No logs to clear")

    def clear_data(self):
        """Clear data file"""
        if self.data_file.exists():
            self.data_file.unlink()
            print("🗑️  Data cleared")
        else:
            print("✓ No data to clear")

def main():
    parser = argparse.ArgumentParser(
        description='SmartShopper Scraper Manager'
    )
    
    subparsers = parser.add_subparsers(dest='command', help='Command to run')
    
    # Run command
    subparsers.add_parser('run', help='Run the scraper')
    
    # Test command
    subparsers.add_parser('test', help='Test scraper setup')
    
    # Logs command
    logs_parser = subparsers.add_parser('logs', help='Show logs')
    logs_parser.add_argument('-n', '--lines', type=int, default=50, help='Number of lines to show')
    
    # Data command
    subparsers.add_parser('data', help='Show scraped data')
    
    # Clear commands
    subparsers.add_parser('clear-logs', help='Clear log file')
    subparsers.add_parser('clear-data', help='Clear data file')
    
    # Status command
    subparsers.add_parser('status', help='Show status')
    
    args = parser.parse_args()
    
    manager = ScraperManager()
    
    if args.command == 'run':
        return manager.run_scraper()
    elif args.command == 'test':
        return manager.test_scraper()
    elif args.command == 'logs':
        manager.show_logs(args.lines)
    elif args.command == 'data':
        manager.show_data()
    elif args.command == 'clear-logs':
        manager.clear_logs()
    elif args.command == 'clear-data':
        manager.clear_data()
    elif args.command == 'status':
        print("📊 SmartShopper Scraper Status\n")
        print(f"Scraper path: {manager.base_path}")
        print(f"Logs: {manager.log_file}")
        print(f"Data: {manager.data_file}")
        print(f"Log file exists: {'✓' if manager.log_file.exists() else '✗'}")
        print(f"Data file exists: {'✓' if manager.data_file.exists() else '✗'}")
    else:
        parser.print_help()
    
    return 0

if __name__ == '__main__':
    sys.exit(main())
