<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('clients')->insert([
            [
                'client_name' => 'TechSolutions Inc.',
                'address' => '123 Main St, New York',
                'phone_number' => '555-1234',
                'contact_person_name' => 'Alex Johnson'
            ],
            [
                'client_name' => 'Global Corp',
                'address' => '456 Elm St, London',
                'phone_number' => '555-5678',
                'contact_person_name' => 'Maria Rodriguez'
            ]
        ]);
    }
}
