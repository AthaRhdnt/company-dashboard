<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('items')->insert([
            ['item_name' => 'Website Development', 'item_price' => 5000.00],
            ['item_name' => 'Advertising Campaign', 'item_price' => 8500.00],
            ['item_name' => 'IT Hardware', 'item_price' => 1500.00],
        ]);
    }
}
