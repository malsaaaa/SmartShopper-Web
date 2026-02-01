#!/usr/bin/env python3
"""
Firecrawl MCP (Model Context Protocol) Server
Provides web scraping and crawling capabilities through MCP
"""

import json
import sys
import logging
import asyncio
from typing import Any, Dict, Optional
from abc import ABC, abstractmethod
from dataclasses import dataclass
from urllib.parse import urljoin, urlparse
import argparse

import requests
from bs4 import BeautifulSoup
from playwright.async_api import async_playwright

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


@dataclass
class CrawlResult:
    """Result from a crawl operation"""
    url: str
    status_code: int
    content: str
    markdown: Optional[str] = None
    links: list = None
    metadata: dict = None

    def to_dict(self) -> Dict[str, Any]:
        return {
            'url': self.url,
            'status_code': self.status_code,
            'content_length': len(self.content),
            'markdown': self.markdown,
            'links': self.links or [],
            'metadata': self.metadata or {}
        }


class FirecrawlClient:
    """Client for interacting with Firecrawl API"""

    def __init__(self, api_key: Optional[str] = None, base_url: str = "https://api.firecrawl.dev"):
        self.api_key = api_key
        self.base_url = base_url
        self.session = requests.Session()
        if api_key:
            self.session.headers.update({'Authorization': f'Bearer {api_key}'})

    async def crawl(
        self,
        url: str,
        depth: int = 1,
        include_markdown: bool = True,
        timeout: int = 30
    ) -> CrawlResult:
        """Crawl a URL and extract content"""
        logger.info(f"Crawling URL: {url} (depth: {depth})")
        
        try:
            # Use Playwright for dynamic content
            async with async_playwright() as p:
                browser = await p.chromium.launch(headless=True)
                page = await browser.new_page()
                await page.goto(url, wait_until='networkidle', timeout=timeout * 1000)
                
                # Get page content
                content = await page.content()
                
                # Get markdown if requested
                markdown = None
                if include_markdown:
                    markdown = await self._convert_to_markdown(content)
                
                # Extract links
                links = await page.evaluate("""
                    () => Array.from(document.querySelectorAll('a'))
                        .map(a => ({text: a.textContent, href: a.href}))
                """)
                
                await browser.close()
                
                # Parse metadata
                soup = BeautifulSoup(content, 'html.parser')
                title = soup.title.string if soup.title else ""
                description = ""
                meta_desc = soup.find('meta', attrs={'name': 'description'})
                if meta_desc:
                    description = meta_desc.get('content', '')
                
                metadata = {
                    'title': title,
                    'description': description,
                    'url': url
                }
                
                result = CrawlResult(
                    url=url,
                    status_code=200,
                    content=content,
                    markdown=markdown,
                    links=links,
                    metadata=metadata
                )
                
                logger.info(f"Successfully crawled {url}")
                return result
                
        except Exception as e:
            logger.error(f"Error crawling {url}: {str(e)}")
            raise

    async def scrape(
        self,
        url: str,
        schema: Optional[Dict[str, Any]] = None,
        timeout: int = 30
    ) -> Dict[str, Any]:
        """Scrape a URL for structured data"""
        logger.info(f"Scraping URL: {url}")
        
        try:
            crawl_result = await self.crawl(url, depth=1, include_markdown=False, timeout=timeout)
            
            if schema:
                # Extract data based on schema
                soup = BeautifulSoup(crawl_result.content, 'html.parser')
                extracted_data = self._extract_by_schema(soup, schema)
                return {
                    'success': True,
                    'url': url,
                    'data': extracted_data,
                    'schema_used': True
                }
            else:
                # Return basic page data
                soup = BeautifulSoup(crawl_result.content, 'html.parser')
                return {
                    'success': True,
                    'url': url,
                    'data': crawl_result.to_dict(),
                    'schema_used': False
                }
                
        except Exception as e:
            logger.error(f"Error scraping {url}: {str(e)}")
            return {
                'success': False,
                'url': url,
                'error': str(e)
            }

    async def extract(
        self,
        url: str,
        selectors: Optional[list] = None,
        timeout: int = 30
    ) -> Dict[str, Any]:
        """Extract specific content from a URL using CSS selectors"""
        logger.info(f"Extracting from URL: {url}")
        
        try:
            crawl_result = await self.crawl(url, depth=1, include_markdown=False, timeout=timeout)
            soup = BeautifulSoup(crawl_result.content, 'html.parser')
            
            extracted = {}
            if selectors:
                for selector in selectors:
                    elements = soup.select(selector)
                    extracted[selector] = [elem.get_text(strip=True) for elem in elements]
            
            return {
                'success': True,
                'url': url,
                'data': extracted,
                'selectors_used': selectors or []
            }
            
        except Exception as e:
            logger.error(f"Error extracting from {url}: {str(e)}")
            return {
                'success': False,
                'url': url,
                'error': str(e)
            }

    @staticmethod
    async def _convert_to_markdown(html_content: str) -> str:
        """Convert HTML to Markdown"""
        try:
            # Simple HTML to Markdown conversion
            soup = BeautifulSoup(html_content, 'html.parser')
            
            # Remove script and style elements
            for script in soup(["script", "style"]):
                script.decompose()
            
            # Get text
            text = soup.get_text()
            
            # Break into lines and remove leading and trailing space on each
            lines = (line.strip() for line in text.splitlines())
            
            # Break multi-headlines into a line each
            chunks = (phrase.strip() for line in lines for phrase in line.split("  "))
            
            # Drop blank lines
            markdown = '\n'.join(chunk for chunk in chunks if chunk)
            return markdown
        except Exception as e:
            logger.error(f"Error converting to markdown: {str(e)}")
            return ""

    @staticmethod
    def _extract_by_schema(soup: BeautifulSoup, schema: Dict[str, Any]) -> Dict[str, Any]:
        """Extract data from soup based on schema"""
        extracted = {}
        
        for field, selector in schema.items():
            if isinstance(selector, str):
                elements = soup.select(selector)
                if elements:
                    extracted[field] = [elem.get_text(strip=True) for elem in elements]
            elif isinstance(selector, dict):
                # Handle nested selectors
                selector_str = selector.get('selector', '')
                attr = selector.get('attribute')
                elements = soup.select(selector_str)
                
                if attr:
                    extracted[field] = [elem.get(attr, '') for elem in elements]
                else:
                    extracted[field] = [elem.get_text(strip=True) for elem in elements]
        
        return extracted


class FirecrawlMCPServer:
    """MCP Server implementation for Firecrawl"""

    def __init__(self, api_key: Optional[str] = None, debug: bool = False):
        self.client = FirecrawlClient(api_key)
        self.debug = debug
        logger.setLevel(logging.DEBUG if debug else logging.INFO)

    async def handle_tool_call(self, tool_name: str, params: Dict[str, Any]) -> Dict[str, Any]:
        """Handle tool call requests"""
        logger.info(f"Handling tool call: {tool_name}")
        
        if tool_name == 'crawl':
            url = params.get('url')
            depth = params.get('depth', 1)
            include_markdown = params.get('include_markdown', True)
            timeout = params.get('timeout', 30)
            
            result = await self.client.crawl(url, depth, include_markdown, timeout)
            return {
                'success': True,
                'result': result.to_dict()
            }
            
        elif tool_name == 'scrape':
            url = params.get('url')
            schema = params.get('schema')
            timeout = params.get('timeout', 30)
            
            return await self.client.scrape(url, schema, timeout)
            
        elif tool_name == 'extract':
            url = params.get('url')
            selectors = params.get('selectors', [])
            timeout = params.get('timeout', 30)
            
            return await self.client.extract(url, selectors, timeout)
            
        else:
            return {
                'success': False,
                'error': f'Unknown tool: {tool_name}'
            }

    async def run(self):
        """Run the MCP server"""
        logger.info("Starting Firecrawl MCP Server")
        
        # Read from stdin for MCP protocol messages
        loop = asyncio.get_event_loop()
        
        while True:
            try:
                line = await loop.run_in_executor(None, sys.stdin.readline)
                if not line:
                    break
                
                message = json.loads(line.strip())
                
                if message.get('method') == 'tool_call':
                    tool_name = message.get('tool')
                    params = message.get('params', {})
                    
                    result = await self.handle_tool_call(tool_name, params)
                    print(json.dumps(result))
                    
            except json.JSONDecodeError:
                continue
            except KeyboardInterrupt:
                logger.info("Server interrupted")
                break
            except Exception as e:
                logger.error(f"Error: {str(e)}")
                error_response = {'success': False, 'error': str(e)}
                print(json.dumps(error_response))


async def main():
    parser = argparse.ArgumentParser(description='Firecrawl MCP Server')
    parser.add_argument('--api-key', type=str, help='Firecrawl API key')
    parser.add_argument('--debug', action='store_true', help='Enable debug mode')
    parser.add_argument('--test-crawl', type=str, help='Test crawl a URL')
    
    args = parser.parse_args()
    
    server = FirecrawlMCPServer(api_key=args.api_key, debug=args.debug)
    
    if args.test_crawl:
        # Test mode
        result = await server.client.crawl(args.test_crawl)
        print(json.dumps(result.to_dict(), indent=2))
    else:
        # Run server
        await server.run()


if __name__ == '__main__':
    asyncio.run(main())
