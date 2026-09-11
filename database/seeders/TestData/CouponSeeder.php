<?php

namespace Database\Seeders\TestData;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::query()->where('email', 'printora.shop0@gmail.com')->firstOrFail();

        Coupon::create([
            'code' => 'FASHION10',
            'percentage' => 10,
            'expire_date' => now()->addMonths(3)->toDateString(),
            'max_usage' => 100,
            'used_count' => 5,
            'seller_id' => $seller->id,
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'EXPIRED25',
            'percentage' => 25,
            'expire_date' => now()->subDays(10)->toDateString(),
            'max_usage' => 10,
            'used_count' => 10,
            'seller_id' => $seller->id,
            'is_active' => false,
        ]);
    }
}
