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
            ['Admin', 'System', 'admin@gmail.com', RoleName::ADMIN],
            ['Printora', 'Fashion', 'printora.shop0@gmail.com', RoleName::SELLER],
            ['Ali', 'Customer', 'buyer1@gmail.com', RoleName::USER],
            ['Sara', 'Customer', 'buyer2@gmail.com', RoleName::USER],
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
