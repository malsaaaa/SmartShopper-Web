from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from bs4 import BeautifulSoup
import sys, json

def scrape_retailer(url):
    options = Options()
    options.add_argument("--headless")
    driver = webdriver.Chrome(options=options)
    
    driver.get(url)
    # Wait for the price element to be visible
    soup = BeautifulSoup(driver.page_source, 'html.parser')
    
    # Example selectors (You must find the exact ones for Mydin/Giant/Lotus's)
    price = soup.select_one('.price, .product-price, #item-price').text
    name = soup.select_one('h1').text

    driver.quit()
    return json.dumps({"name": name.strip(), "price": price.strip()})

if __name__ == "__main__":
    print(scrape_retailer(sys.argv[1]))