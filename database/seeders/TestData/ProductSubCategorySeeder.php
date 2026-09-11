<?php

namespace Database\Seeders\TestData;

use App\Models\Product;
use App\Models\ProductSubCategory;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class ProductSubCategorySeeder extends Seeder
{
    public function run(): void
    {
        $productSubCategories = [
            'تيشيرت كلاسيك نبيتي' => 'تيشيرتات رجالي',
            'تيشيرت مخطط أساسي' => 'تيشيرتات رجالي',
            'تيشيرت حريمي قطني كاجوال' => 'تيشيرتات حريمي',
            'تيشيرت رياضي نبيتي للأطفال' => 'ملابس رياضية أطفال',
        ];

        foreach ($productSubCategories as $productTitle => $subCategoryTitle) {
            ProductSubCategory::create([
                'product_id' => Product::query()->where('title', $productTitle)->firstOrFail()->id,
                'sub_category_id' => SubCategory::query()->where('title', $subCategoryTitle)->firstOrFail()->id,
            ]);
        }
    }
}
