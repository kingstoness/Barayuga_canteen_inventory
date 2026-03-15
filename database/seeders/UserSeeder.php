<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin User',     'email' => 'admin@canteen.com',    'role' => 'admin'],
            ['name' => 'Cashier One',    'email' => 'cashier@canteen.com',  'role' => 'cashier'],
            ['name' => 'Cashier Two',    'email' => 'cashier2@canteen.com', 'role' => 'cashier'],
            ['name' => 'Student User',   'email' => 'customer@canteen.com', 'role' => 'customer'],
            ['name' => 'Juan dela Cruz', 'email' => 'juan@school.edu',      'role' => 'customer'],
            ['name' => 'Maria Santos',   'email' => 'maria@school.edu',     'role' => 'customer'],
            ['name' => 'Pedro Reyes',    'email' => 'pedro@school.edu',     'role' => 'customer'],
            ['name' => 'Ana Garcia',     'email' => 'ana@school.edu',       'role' => 'customer'],
            ['name' => 'Carlo Bautista', 'email' => 'carlo@school.edu',     'role' => 'customer'],
            ['name' => 'Liza Mendoza',   'email' => 'liza@school.edu',      'role' => 'customer'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'     => $u['name'],
                    'password' => Hash::make('password123'),
                    'role'     => $u['role'],
                ]
            );
        }
    }
}