<?php

namespace Database\Seeders\TestData;

use App\Models\Product;
use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Classic Burgundy T-Shirt' => [
                ['Color', 'Burgundy'],
                ['Size', 'M, L, XL'],
                ['Material', '100% Cotton'],
            ],
            'Essential Stripe T-Shirt' => [
                ['Color', 'Burgundy'],
                ['Size', 'S, M, L'],
                ['Fit', 'Regular'],
            ],
            'Women Casual Cotton T-Shirt' => [
                ['Color', 'Burgundy'],
                ['Size', 'S, M, L'],
                ['Material', 'Cotton'],
            ],
            'Kids Burgundy Sports T-Shirt' => [
                ['Color', 'Burgundy'],
                ['Size', '6-12 Years'],
                ['Fit', 'Sports'],
            ],
        ];

        foreach ($attributes as $productTitle => $productAttributes) {
            $product = Product::query()->where('title', $productTitle)->firstOrFail();

            foreach ($productAttributes as [$key, $value]) {
                ProductAttribute::create([
                    'product_id' => $product->id,
                    'key' => $key,
                    'value' => $value,
                ]);
            }
        }
    }
}
