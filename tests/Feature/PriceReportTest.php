<?php

namespace Tests\Feature;

use App\Models\Price;
use App\Models\PriceReport;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_report_price()
    {
        $user = User::factory()->create();
        $product = Product::create(['name' => 'Rpt Prod', 'category' => 'Misc']);
        $retailer = Retailer::create(['name' => "Lotus's"]);

        Price::create(['product_id' => $product->id, 'retailer_id' => $retailer->id, 'amount' => 9.99, 'scraped_at' => now()]);

        $this->actingAs($user)->post(route('product.report', $product->id), [
            'retailer_id' => $retailer->id,
            'reported_price' => 8.99,
            'comment' => 'Price looks outdated',
        ])->assertRedirect();

        $this->assertDatabaseHas('price_reports', [
            'product_id' => $product->id,
            'retailer_id' => $retailer->id,
            'user_id' => $user->id,
            'reported_price' => 8.99,
            'comment' => 'Price looks outdated',
        ]);
    }
}
