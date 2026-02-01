# URGENT FIX: Module Import Error

## The Problem

```
ERROR - Failed to import FirecrawlMCPServer: No module named 'firecrawl_mcp_server'
```

This means the Python script can't find the `firecrawl_mcp_server` module.

## The Solution - Do These Steps in Order

### Step 1: Navigate to Correct Directory (IMPORTANT!)

```bash
cd c:\Users\HP VICTUS\SmartShopper
```

Make sure you're in the **root SmartShopper directory**, not in a subfolder.

### Step 2: Verify Files Exist

```bash
# Check these files exist:
ls firecrawl_mcp_server.py        # Should exist
ls requirements.txt                # Should exist
ls scrapers/shopee_firecrawl_scraper.py  # Should exist
```

If any of these files are missing, the setup didn't complete properly.

### Step 3: Fix Dependencies

```bash
python fix_dependencies.py
```

This will:
- ✅ Upgrade pip
- ✅ Install requirements.txt
- ✅ Install Playwright browsers
- ✅ Verify all imports

### Step 4: Run Diagnostics

```bash
python diagnose.py
```

This will check everything and show what's still broken.

### Step 5: Try Setup Check

```bash
python check_shopee_setup.py
```

This will verify configuration and readiness.

### Step 6: Try Scraper

```bash
python scrape_shopee_now.py
```

Or directly:

```bash
python scrapers/shopee_firecrawl_scraper.py
```

## If Still Failing

### Option A: Fresh Install (Recommended)

```bash
# Step 1: Clean pip cache
python -m pip cache purge

# Step 2: Install fresh
python -m pip install --upgrade pip
python -m pip install -r requirements.txt --force-reinstall
python -m playwright install

# Step 3: Verify
python diagnose.py
```

### Option B: Manual Fix

```bash
# Install each package individually
python -m pip install requests
python -m pip install beautifulsoup4
python -m pip install playwright
python -m pip install python-dotenv

# Install browsers
python -m playwright install
```

### Option C: Check Python Path

```bash
# Run this to check where Python is looking
python -c "import sys; print('\n'.join(sys.path))"

# Make sure current directory is in the path (should see '.')
```

## Common Issues & Fixes

| Issue | Fix |
|-------|-----|
| `ModuleNotFoundError: No module named 'bs4'` | `pip install beautifulsoup4` |
| `ModuleNotFoundError: No module named 'requests'` | `pip install requests` |
| `ModuleNotFoundError: No module named 'playwright'` | `pip install playwright && python -m playwright install` |
| `No module named 'firecrawl_mcp_server'` | Make sure you're in SmartShopper root directory |
| `FileNotFoundError: .env` | Create .env file with FIRECRAWL_API_KEY |

## Quick Test

Run this to see if everything works:

```bash
python -c "
import sys
from pathlib import Path

# Add parent directory to path
sys.path.insert(0, str(Path.cwd()))

# Try importing
try:
    from firecrawl_mcp_server import FirecrawlMCPServer
    print('✅ FirecrawlMCPServer imported successfully!')
except Exception as e:
    print(f'❌ Error: {e}')
    
# Try other imports
try:
    import bs4
    print('✅ BeautifulSoup4 imported successfully!')
except Exception as e:
    print(f'❌ Error importing bs4: {e}')
"
```

## Most Likely Fix

**99% of the time**, this fixes it:

```bash
python fix_dependencies.py
```

Just run that ONE command and it will install everything needed.

## Still Not Working?

Run the diagnostic to see exactly what's wrong:

```bash
python diagnose.py
```

This will tell you exactly what's missing and how to fix it.

---

## Quick Summary

1. **cd** to SmartShopper root directory
2. **python fix_dependencies.py** - Install all packages
3. **python diagnose.py** - Check what's still broken
4. **python scrape_shopee_now.py** - Run scraper

That's it!
