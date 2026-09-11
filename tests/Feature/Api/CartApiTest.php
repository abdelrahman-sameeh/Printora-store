<?php

namespace Tests\Feature\Api;

use App\Enums\RoleName;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_api_still_uses_the_same_endpoints(): void
    {
        $buyer = $this->createBuyer();
        $product = $this->createProduct($this->createSeller());
        Sanctum::actingAs($buyer);

        $this->postJson('/api/cart/items', [
            'items' => [['id' => $product->id, 'quantity' => 2]],
        ])
            ->assertCreated()
            ->assertJsonPath('cart.items_count', 1)
            ->assertJsonPath('cart.summary_cart.total', 150);

        $this->getJson('/api/cart')
            ->assertOk()
            ->assertJsonPath('cart.groups.0.items.0.product.title', 'منتج API');

        $this->postJson('/api/cart/validate')
            ->assertOk()
            ->assertJsonPath('valid', true);
    }

    private function createBuyer(): User
    {
        return User::create([
            'first_name' => 'مشتري',
            'last_name' => 'تجريبي',
            'email' => 'api-buyer@example.com',
            'password' => 'password123',
        ]);
    }

    private function createSeller(): User
    {
        $seller = User::create([
            'first_name' => 'بائع',
            'last_name' => 'تجريبي',
            'email' => 'api-seller@example.com',
            'password' => 'password123',
        ]);
        $seller->syncRoles(RoleName::SELLER);

        return $seller;
    }

    private function createProduct(User $seller): Product
    {
        return Product::create([
            'title' => 'منتج API',
            'description' => 'وصف مناسب للمنتج المستخدم في اختبار السلة.',
            'price' => 75,
            'discount_amount' => 0,
            'quantity' => 10,
            'seller_id' => $seller->id,
            'is_active' => true,
        ]);
    }
}
