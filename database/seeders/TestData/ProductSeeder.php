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
                'title' => 'تيشيرت كلاسيك نبيتي',
                'description' => 'تيشيرت قطني ناعم بقصة مريحة ومناسبة للاستخدام اليومي.',
                'cover_image' => '/storage/products/covers/3fhjagZjKUaDlyIBtfhIb1NfwmXQjQC5lTNfUbHM.webp',
                'price' => 499.00,
                'discount_amount' => 50.00,
                'quantity' => 80,
            ],
            [
                'title' => 'تيشيرت مخطط أساسي',
                'description' => 'تيشيرت خفيف بأكمام مخططة لإطلالة كاجوال أنيقة.',
                'cover_image' => '/storage/products/covers/UsvsVsvtfi5ludnao1MHZn0eUvOMoFUUErH1gwGN.webp',
                'price' => 549.00,
                'discount_amount' => 0,
                'quantity' => 65,
            ],
            [
                'title' => 'تيشيرت حريمي قطني كاجوال',
                'description' => 'تيشيرت قطني جيد التهوية بقصة واسعة ومريحة للإطلالات اليومية.',
                'cover_image' => '/storage/products/covers/dGx7GHVweNGFbWZZDjCd1kyeVcNntASfIhNzumk8.webp',
                'price' => 599.00,
                'discount_amount' => 75.00,
                'quantity' => 55,
            ],
            [
                'title' => 'تيشيرت رياضي نبيتي للأطفال',
                'description' => 'تيشيرت رياضي متين للأطفال بخامة ناعمة تمنحهم حرية الحركة.',
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
