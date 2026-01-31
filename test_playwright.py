#!/usr/bin/env python3
"""
Quick test of Shopee with Playwright
"""
import asyncio
from playwright.async_api import async_playwright
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

async def test_shopee():
    async with async_playwright() as p:
        # Use chromium without headless initially to see what's happening
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context()
        page = await context.new_page()
        
        url = "https://shopee.com.my/search?keyword=milo"
        logger.info(f"Navigating to {url}")
        
        await page.goto(url, wait_until="networkidle")
        logger.info("Page loaded")
        
        # Wait for some elements
        try:
            await page.wait_for_selector("div[data-testid='product-item']", timeout=10000)
            logger.info("Found product items!")
        except:
            logger.warning("Could not find product items with test ID")
        
        # Get page content
        content = await page.content()
        logger.info(f"Page HTML size: {len(content)} bytes")
        
        # Save HTML
        with open('playwright_debug.html', 'w', encoding='utf-8') as f:
            f.write(content)
        logger.info("Saved HTML to playwright_debug.html")
        
        # Try to find any divs
        items = await page.locator("div").count()
        logger.info(f"Found {items} div elements")
        
        # Look for any text "product" or "price"
        try:
            price_text = await page.inner_text("body")
            if "price" in price_text.lower() or "product" in price_text.lower():
                logger.info("Found product/price related content in page")
            else:
                logger.warning("No product/price content found")
        except:
            logger.warning("Could not extract page text")
        
        await browser.close()

if __name__ == "__main__":
    asyncio.run(test_shopee())
