# -*- coding: utf-8 -*-
import asyncio
import sys
import io
import json
from playwright.async_api import async_playwright
from datetime import datetime

async def capture_api_calls():
    """Capture API calls made by Shopee and Mydin"""
    if sys.platform == 'win32':
        sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    
    print("=" * 80)
    print("CAPTURING API CALLS FROM SHOPEE & MYDIN")
    print("=" * 80)
    
    # Shopee
    print("\n1️⃣ SHOPEE SUPERMARKET - Capturing API requests")
    try:
        async with async_playwright() as p:
            browser = await p.chromium.launch()
            context = await browser.new_context(
                user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            )
            page = await context.new_page()
            
            # Capture responses
            responses = []
            def handle_response(response):
                responses.append({
                    'url': response.url,
                    'status': response.status,
                    'content_type': response.headers.get('content-type', 'unknown')
                })
            
            page.on("response", handle_response)
            
            url = "https://shopee.com.my/supermarket?keyword=milo"
            print(f"📍 Loading: {url}")
            await page.goto(url, wait_until="networkidle", timeout=60000)
            print("⏳ Waiting additional 3 seconds...")
            await asyncio.sleep(3)
            
            # Filter for API calls
            api_calls = [r for r in responses if 'api' in r['url'].lower()]
            print(f"\n✓ Total responses captured: {len(responses)}")
            print(f"✓ API calls: {len(api_calls)}")
            
            if api_calls:
                print("\n🔗 Top 10 API calls:")
                for i, call in enumerate(api_calls[:10]):
                    print(f"  {i+1}. {call['url'][:100]}")
            
            await browser.close()
    except Exception as e:
        print(f"❌ Shopee error: {e}")
    
    # Mydin
    print("\n2️⃣ MYDIN - Capturing API requests")
    try:
        async with async_playwright() as p:
            browser = await p.chromium.launch()
            context = await browser.new_context(
                user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            )
            page = await context.new_page()
            
            # Capture responses
            responses = []
            def handle_response(response):
                responses.append({
                    'url': response.url,
                    'status': response.status,
                    'content_type': response.headers.get('content-type', 'unknown')
                })
            
            page.on("response", handle_response)
            
            url = "https://www.mydin.my/search?q=milo"
            print(f"📍 Loading: {url}")
            await page.goto(url, wait_until="networkidle", timeout=60000)
            print("⏳ Waiting additional 3 seconds...")
            await asyncio.sleep(3)
            
            # Filter for API calls
            api_calls = [r for r in responses if 'api' in r['url'].lower() or 'graphql' in r['url'].lower()]
            print(f"\n✓ Total responses captured: {len(responses)}")
            print(f"✓ API/GraphQL calls: {len(api_calls)}")
            
            if api_calls:
                print("\n🔗 Top 10 API calls:")
                for i, call in enumerate(api_calls[:10]):
                    print(f"  {i+1}. {call['url'][:120]}")
            
            # Also show non-API responses
            print(f"\n📊 Other notable responses:")
            json_calls = [r for r in responses if 'json' in r['url'].lower()]
            print(f"✓ JSON responses: {len(json_calls)}")
            
            await browser.close()
    except Exception as e:
        print(f"❌ Mydin error: {e}")

async def main():
    await capture_api_calls()

if __name__ == "__main__":
    asyncio.run(main())
