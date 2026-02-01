# Firecrawl MCP Setup Guide

## Overview

Firecrawl MCP (Model Context Protocol) provides web scraping and crawling capabilities integrated with your SmartShopper application. This guide walks through the complete setup process.

## Prerequisites

- Python 3.9+
- Node.js 16+ (for CLI integration)
- pip package manager
- Internet connection (for API calls)

## Step 1: Install Dependencies

### Python Dependencies

Add firecrawl to your requirements.txt:

```bash
# Add to requirements.txt
firecrawl-py>=1.0.0
```

Or install directly:

```bash
pip install firecrawl-py playwright beautifulsoup4 requests python-dotenv
```

### Node.js Dependencies

```bash
npm install @firecrawl/sdk
```

## Step 2: Configuration

### Environment Variables

Create or update your `.env` file:

```env
# Firecrawl Configuration
FIRECRAWL_API_KEY=your_api_key_here
FIRECRAWL_BASE_URL=https://api.firecrawl.dev
FIRECRAWL_DEBUG=false

# Scraping Settings
FIRECRAWL_DEFAULT_TIMEOUT=30
FIRECRAWL_DEFAULT_DEPTH=1
FIRECRAWL_INCLUDE_MARKDOWN=true
```

### Get Firecrawl API Key

1. Visit [firecrawl.dev](https://firecrawl.dev)
2. Sign up for an account
3. Create an API key in the dashboard
4. Copy the key to your `.env` file

## Step 3: Start the MCP Server

### Python Server

```bash
# Basic start
python firecrawl_mcp_server.py

# Debug mode
python firecrawl_mcp_server.py --debug

# Test a URL
python firecrawl_mcp_server.py --test-crawl https://example.com
```

### Node.js/CLI Integration

```bash
# Use with Node.js
node firecrawl_mcp_client.js --url https://example.com --action crawl
```

## Step 4: Available Tools

### 1. Crawl Tool

Crawl a URL and extract all content with optional markdown conversion.

```python
# Python
result = await server.client.crawl(
    url="https://example.com",
    depth=1,
    include_markdown=True,
    timeout=30
)
```

**Parameters:**
- `url` (string, required): URL to crawl
- `depth` (integer, optional): Crawl depth (1-5, default: 1)
- `include_markdown` (boolean, optional): Convert HTML to Markdown (default: true)
- `timeout` (integer, optional): Timeout in seconds (default: 30)

**Returns:**
```json
{
  "url": "https://example.com",
  "status_code": 200,
  "content_length": 15234,
  "markdown": "...",
  "links": [
    {"text": "Link Text", "href": "https://..."}
  ],
  "metadata": {
    "title": "Page Title",
    "description": "Page description"
  }
}
```

### 2. Scrape Tool

Scrape a URL for structured data based on a schema.

```python
# Python
result = await server.client.scrape(
    url="https://example.com/products",
    schema={
        "products": "div.product",
        "price": "span.price",
        "title": "h2.title"
    },
    timeout=30
)
```

**Parameters:**
- `url` (string, required): URL to scrape
- `schema` (object, optional): JSON schema for data extraction
- `timeout` (integer, optional): Timeout in seconds (default: 30)

**Returns:**
```json
{
  "success": true,
  "url": "https://example.com/products",
  "data": {
    "products": ["Product 1", "Product 2", ...],
    "price": ["$19.99", "$29.99", ...],
    "title": ["Title 1", "Title 2", ...]
  },
  "schema_used": true
}
```

### 3. Extract Tool

Extract specific content using CSS selectors.

```python
# Python
result = await server.client.extract(
    url="https://example.com",
    selectors=[
        "h1.title",
        "p.description",
        "div.content"
    ],
    timeout=30
)
```

**Parameters:**
- `url` (string, required): URL to extract from
- `selectors` (array, optional): CSS selectors for extraction
- `timeout` (integer, optional): Timeout in seconds (default: 30)

**Returns:**
```json
{
  "success": true,
  "url": "https://example.com",
  "data": {
    "h1.title": ["Title"],
    "p.description": ["Description text"],
    "div.content": ["Content..."]
  },
  "selectors_used": ["h1.title", "p.description", "div.content"]
}
```

## Step 5: Integration with SmartShopper

### Django/Laravel Integration

Create a service to handle Firecrawl operations:

```python
# app/Services/FirecrawlService.py
import asyncio
from firecrawl_mcp_server import FirecrawlMCPServer

class FirecrawlService:
    def __init__(self):
        self.server = FirecrawlMCPServer()
    
    def crawl_url(self, url, depth=1):
        loop = asyncio.get_event_loop()
        return loop.run_until_complete(self.server.client.crawl(url, depth))
    
    def scrape_products(self, url, schema=None):
        loop = asyncio.get_event_loop()
        return loop.run_until_complete(self.server.client.scrape(url, schema))

# Usage in Laravel controller
# $firecrawlService = new FirecrawlService();
# $result = $firecrawlService->crawl_url('https://example.com');
```

### Use in Existing Scrapers

Update your existing scrapers to use Firecrawl:

```python
# scrapers/firecrawl_scraper.py
from firecrawl_mcp_server import FirecrawlClient
import asyncio

async def scrape_shopee_with_firecrawl(url):
    client = FirecrawlClient()
    
    # Define schema for product extraction
    schema = {
        "product_name": "h2.product-name",
        "price": "span.product-price",
        "rating": "span.rating",
        "review_count": "span.review-count"
    }
    
    result = await client.scrape(url, schema)
    return result['data']

# Run scraper
if __name__ == "__main__":
    url = "https://shopee.com/..."
    data = asyncio.run(scrape_shopee_with_firecrawl(url))
    print(data)
```

## Step 6: Testing

### Run Tests

```bash
# Test basic crawl
python firecrawl_mcp_server.py --test-crawl https://example.com

# Test with specific selectors
python -c "
import asyncio
from firecrawl_mcp_server import FirecrawlMCPServer

async def test():
    server = FirecrawlMCPServer()
    result = await server.client.extract(
        'https://example.com',
        ['h1', 'p', 'a']
    )
    print(result)

asyncio.run(test())
"
```

### Unit Tests

Create `tests/test_firecrawl_mcp.py`:

```python
import pytest
import asyncio
from firecrawl_mcp_server import FirecrawlClient, FirecrawlMCPServer

@pytest.mark.asyncio
async def test_crawl_basic():
    client = FirecrawlClient()
    result = await client.crawl("https://httpbin.org/html")
    assert result.status_code == 200
    assert len(result.content) > 0

@pytest.mark.asyncio
async def test_extract():
    server = FirecrawlMCPServer()
    result = await server.client.extract(
        "https://httpbin.org/html",
        ["h1", "p"]
    )
    assert result['success']
    assert 'data' in result

@pytest.mark.asyncio
async def test_tool_call():
    server = FirecrawlMCPServer()
    result = await server.handle_tool_call('crawl', {
        'url': 'https://httpbin.org/html',
        'depth': 1
    })
    assert result['success']
```

## Step 7: Usage Examples

### Example 1: Crawl a Product Page

```python
import asyncio
from firecrawl_mcp_server import FirecrawlMCPServer

async def main():
    server = FirecrawlMCPServer()
    
    result = await server.client.crawl(
        url="https://example.com/product/123",
        depth=1,
        include_markdown=True
    )
    
    print(f"Title: {result.metadata['title']}")
    print(f"Links found: {len(result.links)}")
    print("\nMarkdown content:\n", result.markdown)

asyncio.run(main())
```

### Example 2: Extract Product Information

```python
import asyncio
from firecrawl_mcp_server import FirecrawlMCPServer

async def extract_products():
    server = FirecrawlMCPServer()
    
    selectors = [
        "h2.product-title",
        "span.price",
        "div.rating",
        "button.add-to-cart"
    ]
    
    result = await server.client.extract(
        url="https://example.com/products",
        selectors=selectors
    )
    
    return result['data']

products = asyncio.run(extract_products())
print(products)
```

### Example 3: Structured Data Scraping

```python
import asyncio
from firecrawl_mcp_server import FirecrawlMCPServer

async def scrape_ecommerce_site():
    server = FirecrawlMCPServer()
    
    schema = {
        "product_names": "h2.title",
        "prices": {
            "selector": "span.price",
            "attribute": "data-price"
        },
        "ratings": "div.rating",
        "images": {
            "selector": "img.product-image",
            "attribute": "src"
        }
    }
    
    result = await server.client.scrape(
        url="https://example.com/products",
        schema=schema
    )
    
    return result['data']

data = asyncio.run(scrape_ecommerce_site())
print(data)
```

## Troubleshooting

### Issue: "API Key not found"
**Solution:** Ensure `FIRECRAWL_API_KEY` is set in your `.env` file and the file is loaded before running the server.

### Issue: "Timeout error"
**Solution:** Increase the timeout parameter or check if the website is responding properly.

### Issue: "Playwright browser not found"
**Solution:** Run `playwright install` to download browser binaries.

```bash
python -m playwright install
```

### Issue: "Connection refused"
**Solution:** Ensure the MCP server is running and listening on the correct port.

## Advanced Configuration

### Custom Headers

```python
client = FirecrawlClient()
client.session.headers.update({
    'User-Agent': 'Custom User Agent',
    'Accept-Language': 'en-US'
})
```

### Proxy Support

```python
import requests
from firecrawl_mcp_server import FirecrawlClient

client = FirecrawlClient()
client.session.proxies = {
    'http': 'http://proxy.example.com:8080',
    'https': 'https://proxy.example.com:8080'
}
```

### Rate Limiting

```python
import asyncio
from firecrawl_mcp_server import FirecrawlClient

async def scrape_with_rate_limit(urls, delay=1):
    client = FirecrawlClient()
    results = []
    
    for url in urls:
        result = await client.crawl(url)
        results.append(result)
        await asyncio.sleep(delay)
    
    return results
```

## Next Steps

1. Test the setup with `python firecrawl_mcp_server.py --test-crawl https://example.com`
2. Integrate with your existing scrapers
3. Set up monitoring and logging
4. Configure rate limiting for production use
5. Create automated workflows

## Resources

- [Firecrawl Documentation](https://docs.firecrawl.dev)
- [MCP Protocol Specification](https://modelcontextprotocol.io)
- [Playwright Documentation](https://playwright.dev/python)
- [BeautifulSoup Documentation](https://www.crummy.com/software/BeautifulSoup/)

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review the logs in the terminal
3. Consult the official documentation
4. Open an issue on the project repository
