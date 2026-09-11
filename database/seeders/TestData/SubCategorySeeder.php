<?php

namespace Database\Seeders\TestData;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    public function run(): void
    {
        $subCategories = [
            'Men Clothing' => ['Men T-Shirts', 'Men Casual Wear'],
            'Women Clothing' => ['Women T-Shirts', 'Women Casual Wear'],
            'Kids Clothing' => ['Kids T-Shirts', 'Kids Sportswear'],
        ];

        foreach ($subCategories as $categoryTitle => $titles) {
            $category = Category::query()->where('title', $categoryTitle)->firstOrFail();

            foreach ($titles as $title) {
                SubCategory::create([
                    'title' => $title,
                    'category_id' => $category->id,
                ]);
            }
        }
    }
}
