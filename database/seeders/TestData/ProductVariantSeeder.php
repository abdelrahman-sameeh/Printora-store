<?php

namespace Database\Seeders\TestData;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $variants = [
            'تيشيرت أسود كلاسيك' => [
                ['size' => 'S', 'color' => 'أسود', 'quantity' => 12],
                ['size' => 'M', 'color' => 'أسود', 'quantity' => 18],
                ['size' => 'L', 'color' => 'أسود', 'quantity' => 15],
                ['size' => 'XL', 'color' => 'أسود', 'quantity' => 7],
            ],
            'تيشيرت أسود أوفرسايز' => [
                ['size' => 'M', 'color' => 'أسود', 'quantity' => 10],
                ['size' => 'L', 'color' => 'أسود', 'quantity' => 14],
                ['size' => 'XL', 'color' => 'أسود', 'quantity' => 6],
                ['size' => 'M', 'color' => 'فحمي', 'quantity' => 8],
                ['size' => 'L', 'color' => 'فحمي', 'quantity' => 9],
                ['size' => 'XL', 'color' => 'فحمي', 'quantity' => 4],
            ],
            'جاكيت جينز حريمي' => [
                ['size' => 'S', 'color' => 'أزرق', 'quantity' => 5],
                ['size' => 'M', 'color' => 'أزرق', 'quantity' => 9],
                ['size' => 'L', 'color' => 'أزرق', 'quantity' => 6],
                ['size' => 'S', 'color' => 'أسود', 'quantity' => 4],
                ['size' => 'M', 'color' => 'أسود', 'quantity' => 7],
                ['size' => 'L', 'color' => 'أسود', 'quantity' => 3],
            ],
            'فستان كتان موف' => [
                ['size' => 'S', 'color' => 'موف', 'quantity' => 4],
                ['size' => 'M', 'color' => 'موف', 'quantity' => 8],
                ['size' => 'L', 'color' => 'موف', 'quantity' => 5],
                ['size' => 'S', 'color' => 'بيج', 'quantity' => 3],
                ['size' => 'M', 'color' => 'بيج', 'quantity' => 6],
                ['size' => 'L', 'color' => 'بيج', 'quantity' => 2],
            ],
            'جاكيت هودي حضري' => [
                ['size' => 'M', 'color' => 'بنفسجي', 'quantity' => 7],
                ['size' => 'L', 'color' => 'بنفسجي', 'quantity' => 10],
                ['size' => 'XL', 'color' => 'بنفسجي', 'quantity' => 4],
                ['size' => 'M', 'color' => 'أسود', 'quantity' => 9],
                ['size' => 'L', 'color' => 'أسود', 'quantity' => 11],
                ['size' => 'XL', 'color' => 'أسود', 'quantity' => 5],
            ],
            'شورت أطفال سماوي' => [
                ['size' => '4 سنوات', 'color' => 'سماوي', 'quantity' => 8],
                ['size' => '6 سنوات', 'color' => 'سماوي', 'quantity' => 12],
                ['size' => '8 سنوات', 'color' => 'سماوي', 'quantity' => 10],
                ['size' => '10 سنوات', 'color' => 'سماوي', 'quantity' => 6],
                ['size' => '4 سنوات', 'color' => 'بيج', 'quantity' => 5],
                ['size' => '6 سنوات', 'color' => 'بيج', 'quantity' => 7],
                ['size' => '8 سنوات', 'color' => 'بيج', 'quantity' => 4],
                ['size' => '10 سنوات', 'color' => 'بيج', 'quantity' => 3],
            ],
        ];

        foreach ($variants as $productTitle => $rows) {
            $product = Product::query()->where('title', $productTitle)->firstOrFail();
            $product->variants()->createMany($rows);
            $product->update(['quantity' => collect($rows)->sum('quantity')]);
        }
    }
}
