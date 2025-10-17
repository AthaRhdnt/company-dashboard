<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $techSolutionsId = DB::table('clients')->where('client_name', 'TechSolutions Inc.')->first()->id;
        $globalCorpId = DB::table('clients')->where('client_name', 'Global Corp')->first()->id;
        $tsPoId = DB::table('purchase_orders')->where('po_number', 'TS-PO-001')->first()->id;
        $gcPoId = DB::table('purchase_orders')->where('po_number', 'GC-PO-002')->first()->id;
        $itDeptId = DB::table('departments')->where('department_code', 'D-20')->first()->id;
        $marketingDeptId = DB::table('departments')->where('department_code', 'D-30')->first()->id;

        DB::table('orders')->insert([
            [
                'ord_number' => 'A-2023-001',
                'ord_date' => '2023-01-20',
                'project_name' => 'Website Redesign',
                'cur' => 'USD',
                'client_id' => $techSolutionsId,
                'purchase_order_id' => $tsPoId,
                'department_id' => $itDeptId
            ],
            [
                'ord_number' => 'A-2023-002',
                'ord_date' => '2023-02-05',
                'project_name' => 'Mobile App',
                'cur' => 'GBP',
                'client_id' => $globalCorpId,
                'purchase_order_id' => $gcPoId,
                'department_id' => $itDeptId
            ]
        ]);
    }
}
