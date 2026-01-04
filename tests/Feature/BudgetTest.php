<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\ShoppingItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_item_and_budget_is_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('budget.check'), [
            'name' => 'Apple',
            'price' => 3.50,
            'retailer' => 'Mydin',
        ]);

        $this->assertDatabaseHas('shopping_items', [
            'user_id' => $user->id,
            'name' => 'Apple',
            'retailer' => 'Mydin',
            'quantity' => 1,
        ]);

        $budget = Budget::where('user_id', $user->id)->first();
        $this->assertNotNull($budget);
        $this->assertEquals(3.50, (float) $budget->current_spending);
    }

    public function test_adding_same_item_increments_quantity_and_updates_total(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('budget.check'), [
            'name' => 'Milk',
            'price' => 5.00,
            'retailer' => 'Giant',
        ]);

        $this->actingAs($user)->post(route('budget.check'), [
            'name' => 'Milk',
            'price' => 5.00,
            'retailer' => 'Giant',
        ]);

        $this->assertDatabaseHas('shopping_items', [
            'user_id' => $user->id,
            'name' => 'Milk',
            'retailer' => 'Giant',
            'quantity' => 2,
        ]);

        $budget = Budget::where('user_id', $user->id)->first();
        $this->assertEquals(10.00, (float) $budget->current_spending);
    }
}