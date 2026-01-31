#!/usr/bin/env python3
"""
Direct database import for scraped prices
Connects to Laravel's database and inserts price data
"""

import json
import sys
import os
from scrape_products import RetailerScraper

# Add parent directory to path for imports
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

def import_prices():
    """Scrape and import prices directly"""
    try:
        # Run scraper
        scraper = RetailerScraper()
        output = scraper.run()
        
        # Parse JSON output
        items = json.loads(output)
        
        if not items:
            print("ERROR: No items scraped")
            return False
        
        # Import using Laravel if available, otherwise just output JSON
        print(f"Successfully scraped {len(items)} items:")
        for item in items:
            print(f"  - {item['name']} ({item['retailer']}): RM {item['price']}")
        
        # Save to file for Laravel to import
        output_file = os.path.join(os.path.dirname(__file__), 'scraped_prices.json')
        with open(output_file, 'w') as f:
            json.dump(items, f, indent=2)
        
        print(f"\n✅ Data saved to {output_file}")
        return True
        
    except Exception as e:
        print(f"ERROR: {e}")
        return False

if __name__ == "__main__":
    success = import_prices()
    sys.exit(0 if success else 1)
