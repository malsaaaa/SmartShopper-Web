<?php

namespace Tests\Unit;

use App\Models\Price;
use App\Models\Product;
use App\Models\Retailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_best_price_with_retailer_is_selected(): void
    {
        $product = Product::create(['name' => 'Test Prod', 'category' => 'test', 'image_url' => null]);

        $r1 = Retailer::create(['name' => "Lotus's"]);
        $r2 = Retailer::create(['name' => 'Mydin']);

        // r1 has zero price (should be ignored)
        Price::create(['product_id' => $product->id, 'retailer_id' => $r1->id, 'amount' => 0, 'scraped_at' => now()]);
        // r2 has a positive price and should be selected
        Price::create(['product_id' => $product->id, 'retailer_id' => $r2->id, 'amount' => 9.99, 'scraped_at' => now()]);

        $info = $product->bestPriceWithRetailer();

        $this->assertEquals(9.99, (float) $info['amount']);
        $this->assertNotNull($info['retailer']);
        $this->assertEquals('Mydin', $info['retailer']->name);
    }
}
