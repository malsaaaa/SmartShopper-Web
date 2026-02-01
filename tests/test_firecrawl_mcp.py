#!/usr/bin/env python3
"""
Test suite for Firecrawl MCP
"""

import asyncio
import pytest
import json
from pathlib import Path
from firecrawl_mcp_server import FirecrawlMCPServer, FirecrawlClient, CrawlResult


class TestFirecrawlClient:
    """Tests for FirecrawlClient"""
    
    @pytest.fixture
    def client(self):
        """Create client instance"""
        return FirecrawlClient()
    
    @pytest.mark.asyncio
    async def test_crawl_basic(self, client):
        """Test basic crawl functionality"""
        url = "https://httpbin.org/html"
        result = await client.crawl(url, depth=1)
        
        assert isinstance(result, CrawlResult)
        assert result.status_code == 200
        assert len(result.content) > 0
        assert result.url == url
    
    @pytest.mark.asyncio
    async def test_crawl_with_markdown(self, client):
        """Test crawl with markdown conversion"""
        url = "https://httpbin.org/html"
        result = await client.crawl(url, include_markdown=True)
        
        assert result.markdown is not None
        assert len(result.markdown) > 0
    
    @pytest.mark.asyncio
    async def test_extract_selectors(self, client):
        """Test CSS selector extraction"""
        url = "https://httpbin.org/html"
        result = await client.extract(url, ['h1', 'p'])
        
        assert result['success']
        assert 'data' in result
    
    @pytest.mark.asyncio
    async def test_scrape_basic(self, client):
        """Test basic scrape"""
        url = "https://httpbin.org/html"
        result = await client.scrape(url)
        
        assert result['success']
        assert 'url' in result


class TestFirecrawlMCPServer:
    """Tests for FirecrawlMCPServer"""
    
    @pytest.fixture
    def server(self):
        """Create server instance"""
        return FirecrawlMCPServer(debug=False)
    
    @pytest.mark.asyncio
    async def test_handle_crawl_tool(self, server):
        """Test crawl tool handler"""
        result = await server.handle_tool_call('crawl', {
            'url': 'https://httpbin.org/html',
            'depth': 1
        })
        
        assert result['success']
        assert 'result' in result
    
    @pytest.mark.asyncio
    async def test_handle_extract_tool(self, server):
        """Test extract tool handler"""
        result = await server.handle_tool_call('extract', {
            'url': 'https://httpbin.org/html',
            'selectors': ['h1', 'p']
        })
        
        assert result['success']
    
    @pytest.mark.asyncio
    async def test_handle_unknown_tool(self, server):
        """Test handling of unknown tool"""
        result = await server.handle_tool_call('unknown_tool', {})
        
        assert not result['success']
        assert 'error' in result
    
    @pytest.mark.asyncio
    async def test_invalid_url(self, server):
        """Test handling of invalid URL"""
        result = await server.handle_tool_call('crawl', {
            'url': 'not_a_valid_url'
        })
        
        # Should handle gracefully
        assert 'result' in result or 'error' in result


class TestCrawlResult:
    """Tests for CrawlResult dataclass"""
    
    def test_crawl_result_to_dict(self):
        """Test CrawlResult.to_dict() method"""
        result = CrawlResult(
            url="https://example.com",
            status_code=200,
            content="<html>content</html>",
            markdown="# Content",
            links=[{"text": "link", "href": "https://..."}],
            metadata={"title": "Example"}
        )
        
        result_dict = result.to_dict()
        
        assert result_dict['url'] == "https://example.com"
        assert result_dict['status_code'] == 200
        assert result_dict['content_length'] == len("<html>content</html>")
        assert result_dict['markdown'] == "# Content"
        assert len(result_dict['links']) == 1
        assert result_dict['metadata']['title'] == "Example"


class TestIntegration:
    """Integration tests"""
    
    @pytest.mark.asyncio
    async def test_full_crawl_workflow(self):
        """Test complete crawl workflow"""
        server = FirecrawlMCPServer()
        
        # Crawl
        crawl_result = await server.handle_tool_call('crawl', {
            'url': 'https://httpbin.org/html',
            'include_markdown': True
        })
        
        assert crawl_result['success']
        
        # Extract
        extract_result = await server.handle_tool_call('extract', {
            'url': 'https://httpbin.org/html',
            'selectors': ['h1']
        })
        
        assert extract_result['success']
    
    @pytest.mark.asyncio
    async def test_concurrent_requests(self):
        """Test concurrent crawl requests"""
        server = FirecrawlMCPServer()
        
        urls = [
            'https://httpbin.org/html',
            'https://httpbin.org/html',
            'https://httpbin.org/html'
        ]
        
        tasks = [
            server.handle_tool_call('crawl', {'url': url})
            for url in urls
        ]
        
        results = await asyncio.gather(*tasks)
        
        assert all(r['success'] for r in results)


# Example test execution
if __name__ == '__main__':
    pytest.main([__file__, '-v', '-s'])
