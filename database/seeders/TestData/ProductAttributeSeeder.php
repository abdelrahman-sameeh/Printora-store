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
            'تيشيرت كلاسيك نبيتي' => [
                ['اللون', 'نبيتي'],
                ['المقاس', 'متوسط، كبير، كبير جدًا'],
                ['الخامة', 'قطن ١٠٠٪'],
            ],
            'تيشيرت مخطط أساسي' => [
                ['اللون', 'نبيتي'],
                ['المقاس', 'صغير، متوسط، كبير'],
                ['القصة', 'عادية'],
            ],
            'تيشيرت حريمي قطني كاجوال' => [
                ['اللون', 'نبيتي'],
                ['المقاس', 'صغير، متوسط، كبير'],
                ['الخامة', 'قطن'],
            ],
            'تيشيرت رياضي نبيتي للأطفال' => [
                ['اللون', 'نبيتي'],
                ['المقاس', 'من ٦ إلى ١٢ سنة'],
                ['القصة', 'رياضية'],
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
