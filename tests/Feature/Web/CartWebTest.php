<?php

namespace Tests\Feature\Web;

use App\Enums\RoleName;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_buyers_can_open_the_cart(): void
    {
        $seller = $this->createSeller();

        $this->actingAs($seller)
            ->get(route('cart.index'))
            ->assertForbidden();
    }

    public function test_buyer_can_add_a_product_and_view_the_cart(): void
    {
        $buyer = $this->createBuyer('buyer@example.com');
        $product = $this->createProduct($this->createSeller(), 'تيشيرت للاختبار', 150);

        $this->actingAs($buyer)
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity' => 2,
            ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($buyer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('تيشيرت للاختبار')
            ->assertSee('300.00');
    }

    public function test_buyer_can_update_and_remove_their_cart_item(): void
    {
        $buyer = $this->createBuyer('buyer@example.com');
        $product = $this->createProduct($this->createSeller(), 'منتج السلة');
        $cart = $buyer->cart()->create();
        $item = $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer)
            ->put(route('cart.items.update', $item->id), ['quantity' => 3])
            ->assertRedirect();

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 3]);

        $this->actingAs($buyer)
            ->delete(route('cart.items.destroy', $item->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_buyer_cannot_manage_another_buyers_cart_item(): void
    {
        $owner = $this->createBuyer('owner@example.com');
        $otherBuyer = $this->createBuyer('other@example.com');
        $product = $this->createProduct($this->createSeller(), 'منتج خاص');
        $item = $owner->cart()->create()->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $otherBuyer->cart()->create();

        $this->actingAs($otherBuyer)
            ->delete(route('cart.items.destroy', $item->id))
            ->assertNotFound();

        $this->assertDatabaseHas('cart_items', ['id' => $item->id]);
    }

    public function test_buyer_can_apply_a_valid_coupon(): void
    {
        $buyer = $this->createBuyer('buyer@example.com');
        $seller = $this->createSeller();
        $product = $this->createProduct($seller, 'منتج بخصم', 100);
        $cart = $buyer->cart()->create();
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);
        $coupon = $this->createCoupon($seller, 'SAVE10');

        $this->actingAs($buyer)
            ->post(route('cart.coupons.store'), ['code' => 'save10'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cart_coupons', [
            'cart_id' => $cart->id,
            'coupon_id' => $coupon->id,
        ]);

        $this->actingAs($buyer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('SAVE10')
            ->assertSee('90.00');
    }

    private function createBuyer(string $email): User
    {
        return User::create([
            'first_name' => 'مشتري',
            'last_name' => 'تجريبي',
            'email' => $email,
            'password' => 'password123',
        ]);
    }

    private function createSeller(): User
    {
        $seller = User::create([
            'first_name' => 'بائع',
            'last_name' => 'تجريبي',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
        ]);
        $seller->syncRoles(RoleName::SELLER);

        return $seller;
    }

    private function createProduct(User $seller, string $title, float $price = 100): Product
    {
        return Product::create([
            'title' => $title,
            'description' => 'وصف مناسب للمنتج المستخدم في اختبار السلة.',
            'price' => $price,
            'discount_amount' => 0,
            'quantity' => 10,
            'seller_id' => $seller->id,
            'is_active' => true,
        ]);
    }

    private function createCoupon(User $seller, string $code): Coupon
    {
        return Coupon::create([
            'code' => $code,
            'percentage' => 10,
            'expire_date' => now()->addMonth()->toDateString(),
            'max_usage' => 10,
            'used_count' => 0,
            'seller_id' => $seller->id,
            'is_active' => true,
        ]);
    }
}
