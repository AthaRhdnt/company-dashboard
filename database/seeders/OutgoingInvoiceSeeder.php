<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OutgoingInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $order1Id = DB::table('orders')->where('ord_number', 'A-2023-001')->first()->id;
        $client1Id = DB::table('clients')->where('client_name', 'TechSolutions Inc.')->first()->id;
        $departmentId = DB::table('departments')->where('department_code', 'D-20')->first()->id;

        DB::table('outgoing_invoices')->insert([
            [
                'inv_number' => 'OUT-INV-001',
                'inv_date' => '2023-01-25',
                'due_date' => '2023-02-25',
                'fp_number' => 'FP-001-A',
                'income_date' => '2023-02-20',
                'cur' => 'USD',
                'order_id' => $order1Id,
                'client_id' => $client1Id,
                'department_id' => $departmentId
            ]
        ]);
    }
}
