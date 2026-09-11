<?php

namespace Database\Seeders\TestData;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::query()->where('email', 'printora.shop0@gmail.com')->firstOrFail();

        $products = [
            [
                'title' => 'Classic Burgundy T-Shirt',
                'description' => 'A soft cotton T-shirt with a comfortable regular fit for everyday wear.',
                'cover_image' => '/storage/products/covers/3fhjagZjKUaDlyIBtfhIb1NfwmXQjQC5lTNfUbHM.webp',
                'price' => 499.00,
                'discount_amount' => 50.00,
                'quantity' => 80,
            ],
            [
                'title' => 'Essential Stripe T-Shirt',
                'description' => 'A lightweight striped-sleeve T-shirt made for a clean casual look.',
                'cover_image' => '/storage/products/covers/UsvsVsvtfi5ludnao1MHZn0eUvOMoFUUErH1gwGN.webp',
                'price' => 549.00,
                'discount_amount' => 0,
                'quantity' => 65,
            ],
            [
                'title' => 'Women Casual Cotton T-Shirt',
                'description' => 'A breathable cotton T-shirt with a relaxed fit for daily outfits.',
                'cover_image' => '/storage/products/covers/dGx7GHVweNGFbWZZDjCd1kyeVcNntASfIhNzumk8.webp',
                'price' => 599.00,
                'discount_amount' => 75.00,
                'quantity' => 55,
            ],
            [
                'title' => 'Kids Burgundy Sports T-Shirt',
                'description' => 'A durable sports T-shirt for kids with soft fabric and easy movement.',
                'cover_image' => '/storage/products/gallery/U8nNE05WagOePq1D0S0xBwDKiR4VMRqL3blk95nR.webp',
                'price' => 399.00,
                'discount_amount' => 25.00,
                'quantity' => 90,
            ],
        ];

        foreach ($products as $product) {
            Product::create([
                ...$product,
                'seller_id' => $seller->id,
                'is_active' => true,
            ]);
        }
    }
}
