<?php

namespace Database\Seeders\TestData;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $buyer1 = User::query()->where('email', 'buyer1@gmail.com')->firstOrFail();
        $buyer2 = User::query()->where('email', 'buyer2@gmail.com')->firstOrFail();

        Address::create([
            'user_id' => $buyer1->id,
            'country' => 'EG',
            'city' => 'Cairo',
            'street' => '15 Tahrir Square, Downtown',
            'is_default' => true,
            'note' => 'Next to the metro station',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);

        Address::create([
            'user_id' => $buyer1->id,
            'country' => 'EG',
            'city' => 'Giza',
            'street' => '22 Pyramids Road',
            'is_default' => false,
            'note' => 'Near the mall',
        ]);

        Address::create([
            'user_id' => $buyer2->id,
            'country' => 'EG',
            'city' => 'Alexandria',
            'street' => '5 Corniche Road',
            'is_default' => true,
        ]);
    }
}
