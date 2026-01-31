import scrapy
from scrapy_selenium import SeleniumRequest

class MarketplaceSpider(scrapy.Spider):
    name = 'marketplace_spider'
    
    def start_requests(self):
        urls = ['https://shopee.com.my/search?keyword=milo']
        for url in urls:
            yield SeleniumRequest(url=url, callback=self.parse)

    def parse(self, response):
        for product in response.css('.shopee-search-item-result__item'):
            yield {
                'name': product.css('.product-name::text').get(),
                'price': product.css('.price::text').get(),
                'retailer': 'Shopee'
            }