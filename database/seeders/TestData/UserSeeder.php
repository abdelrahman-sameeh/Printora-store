<?php

namespace Database\Seeders\TestData;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['أحمد', 'المدير', 'admin@gmail.com', RoleName::ADMIN],
            ['متجر', 'برنتورا', 'printora.shop0@gmail.com', RoleName::SELLER],
            ['علي', 'محمد', 'buyer1@gmail.com', RoleName::USER],
            ['سارة', 'أحمد', 'buyer2@gmail.com', RoleName::USER],
        ];

        foreach ($users as [$firstName, $lastName, $email, $role]) {
            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => Hash::make('printora1234'),
            ]);

            $user->syncRoles($role);
        }
    }
}
