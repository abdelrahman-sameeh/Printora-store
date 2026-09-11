<?php

namespace Database\Seeders\TestData;

use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    public function run(): void
    {
        $cartItems = [
            'buyer1@gmail.com' => [
                'تيشيرت كلاسيك نبيتي' => 1,
                'تيشيرت مخطط أساسي' => 2,
            ],
            'buyer2@gmail.com' => [
                'تيشيرت حريمي قطني كاجوال' => 1,
                'تيشيرت رياضي نبيتي للأطفال' => 1,
            ],
        ];

        foreach ($cartItems as $buyerEmail => $products) {
            $buyer = User::query()->where('email', $buyerEmail)->firstOrFail();
            $cart = Cart::query()->where('user_id', $buyer->id)->firstOrFail();

            foreach ($products as $productTitle => $quantity) {
                $product = Product::query()->where('title', $productTitle)->firstOrFail();

                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                ]);
            }
        }
    }
}
