<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Boss Admin',
            'email' => 'admin@admin.com',
            'password' =>'!Poker212',
            'email_verified_at' => now(),
            'is_admin' => true
        ]);

        User::factory()->create([
            'name' => 'Aling Ling',
            'email' => 'aling@ptjst.tech',
            'password' => '!Poker212',
            'email_verified_at' => now(),
            'is_admin' =>false
        ]);
    }
}
