# Firecrawl MCP Implementation Guide

**Status**: ✅ Complete and Ready to Use

## Overview

Firecrawl MCP (Model Context Protocol) provides a professional-grade web scraping and crawling solution integrated with your SmartShopper application. It enables automatic content extraction, data scraping, and web monitoring capabilities.

## 📦 What's Included

### Core Server Components
- **firecrawl_mcp_server.py** - Main Python server with three tools:
  - `crawl` - Extract full page content with Markdown conversion
  - `scrape` - Extract structured data using schemas
  - `extract` - Extract specific content using CSS selectors

### Client Interfaces
- **firecrawl_mcp_client.js** - Node.js command-line interface
- **firecrawl_mcp_quickstart.py** - Automated setup and verification
- **start_firecrawl_mcp.bat** - Windows startup script
- **start_firecrawl_mcp.sh** - Linux/macOS startup script

### Integration Layer
- **app/Services/FirecrawlService.py** - Laravel service for high-level operations
  - Product page crawling
  - Shopee product scraping
  - Price monitoring with trend analysis
  - Batch operations

### Configuration & Documentation
- **firecrawl_mcp_config.json** - MCP configuration with tool definitions
- **FIRECRAWL_MCP_SETUP.md** - Complete 350+ line setup guide
- **FIRECRAWL_MCP_QUICK_REFERENCE.md** - Quick reference guide
- **.env.example** - Configuration template with all settings
- **requirements.txt** - Updated Python dependencies
- **tests/test_firecrawl_mcp.py** - Comprehensive test suite

## 🚀 Quick Start

### Option 1: Automated Setup (Recommended)

**Windows:**
```bash
start_firecrawl_mcp.bat
```

**Linux/macOS:**
```bash
chmod +x start_firecrawl_mcp.sh
./start_firecrawl_mcp.sh
```

### Option 2: Manual Setup

```bash
# 1. Install dependencies
pip install -r requirements.txt
python -m playwright install

# 2. Configure environment
cp .env.example .env
# Edit .env and add your FIRECRAWL_API_KEY

# 3. Run setup verification
python firecrawl_mcp_quickstart.py

# 4. Start server
python firecrawl_mcp_server.py
```

## 📖 Documentation Structure

```
├── FIRECRAWL_MCP_SETUP.md
│   ├── Prerequisites
│   ├── Step-by-step Installation
│   ├── Configuration
│   ├── Tool Documentation (3 tools)
│   ├── Integration Guides
│   ├── Examples (3 detailed examples)
│   ├── Testing
│   ├── Troubleshooting
│   └── Advanced Configuration
│
├── FIRECRAWL_MCP_QUICK_REFERENCE.md
│   ├── What's Installed
│   ├── Quick Start (4 steps)
│   ├── Available Tools (3 tools)
│   ├── Integration Examples
│   ├── Features List
│   └── File Locations
│
└── This File (README)
    ├── Overview
    ├── Quick Start
    ├── Usage Examples
    ├── Architecture
    ├── Key Features
    └── Support
```

## 💻 Usage Examples

### Python - Crawl a Product Page

```python
from firecrawl_mcp_server import FirecrawlMCPServer
import asyncio

async def main():
    server = FirecrawlMCPServer()
    result = await server.client.crawl("https://example.com/product")
    print(f"Title: {result.metadata['title']}")
    print(f"Markdown:\n{result.markdown}")

asyncio.run(main())
```

### Python - Scrape with Schema

```python
from firecrawl_mcp_server import FirecrawlMCPServer
import asyncio

async def main():
    server = FirecrawlMCPServer()
    schema = {
        "product_names": "h2.title",
        "prices": "span.price",
        "ratings": "div.rating"
    }
    result = await server.client.scrape("https://example.com/products", schema)
    print(result)

asyncio.run(main())
```

### Node.js - CLI Usage

```bash
# Crawl a URL
node firecrawl_mcp_client.js crawl --url https://example.com

# Extract with selectors
node firecrawl_mcp_client.js extract \
  --url https://example.com \
  --selectors "h1,p,.description"

# Scrape with schema and save
node firecrawl_mcp_client.js scrape \
  --url https://example.com/products \
  --schema schema.json \
  --output results.json \
  --pretty
```

### Laravel - Integration

```php
use App\Services\FirecrawlService;

class ProductController
{
    private $firecrawl;
    
    public function __construct()
    {
        $this->firecrawl = new FirecrawlService();
    }
    
    public function crawlProduct($id)
    {
        $url = "https://example.com/product/$id";
        $result = $this->firecrawl->crawlProductPage($url);
        return response()->json($result);
    }
    
    public function compareProducts()
    {
        $urls = request('urls', []);
        $results = $this->firecrawl->batchCrawl($urls);
        return response()->json($results);
    }
    
    public function monitorPrice()
    {
        $url = request('url');
        $result = $this->firecrawl->monitorPriceChanges($url);
        return response()->json($result);
    }
}
```

## 🏗️ Architecture

```
┌─────────────────────────────────────┐
│   Client Applications               │
│  (Laravel, Node.js, Python, etc)    │
└────────────────┬────────────────────┘
                 │
         ┌───────▼────────┐
         │   MCP Client   │
         │  (firecrawl_   │
         │  mcp_client.js)│
         └────────┬───────┘
                  │
         ┌────────▼────────────────────┐
         │  Firecrawl MCP Server       │
         │ (firecrawl_mcp_server.py)   │
         │                             │
         │  Tools:                     │
         │  - crawl                    │
         │  - scrape                   │
         │  - extract                  │
         └────────┬────────────────────┘
                  │
    ┌─────────────┼─────────────┐
    │             │             │
    ▼             ▼             ▼
┌────────┐  ┌──────────┐  ┌──────────┐
│Playwright││BeautifulSoup││ Requests │
│ Browser  │  HTML Parser │  Library  │
└────────┘  └──────────┘  └──────────┘
    │             │             │
    └─────────────┼─────────────┘
                  │
                  ▼
         ┌─────────────────┐
         │   Web Pages     │
         │ (Target Sites)  │
         └─────────────────┘
```

## 🎯 Key Features

| Feature | Description |
|---------|-------------|
| **Dynamic Content** | Playwright browser automation for JavaScript-rendered content |
| **Markdown Conversion** | Automatic HTML to Markdown formatting |
| **CSS Selectors** | Precise data extraction using CSS selectors |
| **Schema Validation** | Extract structured data with JSON schemas |
| **Link Extraction** | Automatically extract all links and metadata |
| **Batch Processing** | Handle multiple URLs efficiently |
| **Caching** | Built-in cache for frequently accessed data |
| **Price Monitoring** | Track price changes with trend analysis |
| **Error Handling** | Graceful error management and logging |
| **Async/Await** | Full async support for concurrent operations |
| **CLI Interface** | Command-line tools for easy integration |
| **Logging** | Comprehensive logging for debugging |

## 🔧 Configuration

### Environment Variables

```env
# API Configuration
FIRECRAWL_API_KEY=your_api_key_here
FIRECRAWL_BASE_URL=https://api.firecrawl.dev

# Server Settings
FIRECRAWL_SERVER_HOST=localhost
FIRECRAWL_SERVER_PORT=3000

# Behavior
FIRECRAWL_DEBUG=false
FIRECRAWL_DEFAULT_TIMEOUT=30
FIRECRAWL_DEFAULT_DEPTH=1
FIRECRAWL_INCLUDE_MARKDOWN=true
```

### API Key Setup

1. Visit [firecrawl.dev](https://firecrawl.dev)
2. Sign up for an account
3. Generate API key from dashboard
4. Add to `.env`: `FIRECRAWL_API_KEY=your_key`

## 📝 Testing

### Run Tests
```bash
# All tests
pytest tests/test_firecrawl_mcp.py -v

# Specific test
pytest tests/test_firecrawl_mcp.py::TestFirecrawlClient::test_crawl_basic -v

# With coverage
pytest tests/test_firecrawl_mcp.py --cov=firecrawl_mcp_server
```

### Manual Testing
```bash
# Test crawl
python firecrawl_mcp_server.py --test-crawl https://example.com

# CLI test
node firecrawl_mcp_client.js crawl --url https://example.com
```

## 🐛 Troubleshooting

### Issue: "API Key not found"
**Solution:** Add `FIRECRAWL_API_KEY` to your `.env` file

### Issue: "Playwright browsers not found"
**Solution:** Run `python -m playwright install`

### Issue: "Timeout error"
**Solution:** Increase timeout parameter or check if website is responding

### Issue: "Module not found"
**Solution:** Run `pip install -r requirements.txt`

See [FIRECRAWL_MCP_SETUP.md](FIRECRAWL_MCP_SETUP.md#troubleshooting) for more details.

## 📚 Full Documentation

- **Setup Guide**: [FIRECRAWL_MCP_SETUP.md](FIRECRAWL_MCP_SETUP.md)
- **Quick Reference**: [FIRECRAWL_MCP_QUICK_REFERENCE.md](FIRECRAWL_MCP_QUICK_REFERENCE.md)
- **Official Docs**: https://docs.firecrawl.dev
- **MCP Protocol**: https://modelcontextprotocol.io

## 🔗 Resources

- **Firecrawl**: https://firecrawl.dev
- **Playwright**: https://playwright.dev/python
- **BeautifulSoup**: https://www.crummy.com/software/BeautifulSoup/
- **MCP Protocol**: https://modelcontextprotocol.io

## 📂 File Reference

| File | Purpose |
|------|---------|
| `firecrawl_mcp_server.py` | Main MCP server implementation |
| `firecrawl_mcp_client.js` | Node.js CLI client |
| `firecrawl_mcp_quickstart.py` | Automated setup script |
| `app/Services/FirecrawlService.py` | Laravel integration service |
| `firecrawl_mcp_config.json` | MCP tool definitions |
| `tests/test_firecrawl_mcp.py` | Test suite |
| `FIRECRAWL_MCP_SETUP.md` | Complete setup guide |
| `FIRECRAWL_MCP_QUICK_REFERENCE.md` | Quick reference |
| `start_firecrawl_mcp.bat` | Windows startup script |
| `start_firecrawl_mcp.sh` | Linux/macOS startup script |

## ✅ Verification Checklist

- [ ] Python 3.9+ installed
- [ ] Dependencies installed (`pip install -r requirements.txt`)
- [ ] Playwright installed (`python -m playwright install`)
- [ ] `.env` file configured with `FIRECRAWL_API_KEY`
- [ ] Server starts successfully (`python firecrawl_mcp_server.py`)
- [ ] Test crawl works (`python firecrawl_mcp_server.py --test-crawl https://example.com`)
- [ ] Integration service accessible (`app/Services/FirecrawlService.py`)

## 🎓 Next Steps

1. **Start the Server**: Use `start_firecrawl_mcp.bat` (Windows) or `start_firecrawl_mcp.sh` (Linux/macOS)
2. **Read the Guide**: Review [FIRECRAWL_MCP_SETUP.md](FIRECRAWL_MCP_SETUP.md)
3. **Try Examples**: Run the provided examples
4. **Integrate**: Use in your Laravel, Node.js, or Python applications
5. **Explore**: Check out advanced features in the documentation

---

**Status**: ✅ Ready to Use | **Version**: 1.0.0 | **Last Updated**: 2026-02-01

For questions or issues, refer to [FIRECRAWL_MCP_SETUP.md](FIRECRAWL_MCP_SETUP.md) or the official documentation.
