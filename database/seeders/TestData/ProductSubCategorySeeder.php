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
            'Classic Burgundy T-Shirt' => 'Men T-Shirts',
            'Essential Stripe T-Shirt' => 'Men T-Shirts',
            'Women Casual Cotton T-Shirt' => 'Women T-Shirts',
            'Kids Burgundy Sports T-Shirt' => 'Kids Sportswear',
        ];

        foreach ($productSubCategories as $productTitle => $subCategoryTitle) {
            ProductSubCategory::create([
                'product_id' => Product::query()->where('title', $productTitle)->firstOrFail()->id,
                'sub_category_id' => SubCategory::query()->where('title', $subCategoryTitle)->firstOrFail()->id,
            ]);
        }
    }
}
