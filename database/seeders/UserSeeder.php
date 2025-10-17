<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Level;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminLevel = Level::where('level_name', 'Administrator')->first();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@app.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'level_id' => $adminLevel->id ?? null,
        ]);

        // Create 10 additional dummy users using a factory
        User::factory(10)->create(['level_id' => $adminLevel->id ?? null]);
    }
}
