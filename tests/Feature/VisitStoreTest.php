<?php

namespace Tests\Feature;

use App\Models\Price;
use App\Models\Product;
use App\Models\Retailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_visit_store_prefers_price_product_url()
    {
        $product = Product::create(['name' => 'VisitTest', 'category' => 'Misc']);
        $retailer = Retailer::create(['name' => "Lotus's", 'base_url' => 'https://www.lotus.com']);

        $price = Price::create(['product_id' => $product->id, 'retailer_id' => $retailer->id, 'amount' => 9.50, 'scraped_at' => now(), 'product_url' => 'https://www.lotus.com/product/visit-test']);

        $res = $this->get(route('product.show', $product->id));
        $res->assertSee('Visit Store');
        $res->assertSee('https://www.lotus.com/product/visit-test');
    }

    public function test_visit_store_falls_back_to_base_url_search()
    {
        $product = Product::create(['name' => 'VisitTest2', 'category' => 'Misc']);
        $retailer = Retailer::create(['name' => 'Mydin', 'base_url' => 'https://www.mydin.com']);

        $price = Price::create(['product_id' => $product->id, 'retailer_id' => $retailer->id, 'amount' => 9.50, 'scraped_at' => now(), 'product_url' => null]);

        $res = $this->get(route('product.show', $product->id));
        $res->assertSee('Visit Store');
        $res->assertSee('https://www.mydin.com/search?q=' . urlencode($product->name));
    }
}
