<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Message;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@admin.com',
            'phone' => '0999999990',
            'role' => 'admin',
            'password' => bcrypt('12345678'),
        ]);
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'phone' => '0999999910',
            'role' => 'admin',
            'password' => bcrypt('12345678'),
        ]);
        User::factory()->create([
            'name' => 'user',
            'email' => 'user@user.com',
            'phone' => '0999999999',
            'role' => 'user',
            'password' => bcrypt('12345678'),
        ]);
        User::factory()->create([
            'name' => 'user1',
            'email' => 'user1@user.com',
            'phone' => '0912345678',
            'role' => 'user',
            'password' => bcrypt('12345678'),
        ]);
        User::factory(10)->create();
    }
}
