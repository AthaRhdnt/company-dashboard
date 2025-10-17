<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('purchase_orders')->insert([
            ['po_number' => 'TS-PO-001', 'po_date' => '2023-01-15'],
            ['po_number' => 'GC-PO-002', 'po_date' => '2023-02-01'],
        ]);
    }
}
