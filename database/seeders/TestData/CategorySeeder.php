<?php

namespace Database\Seeders\TestData;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Men Clothing', 'Women Clothing', 'Kids Clothing'] as $title) {
            Category::create(['title' => $title]);
        }
    }
}
