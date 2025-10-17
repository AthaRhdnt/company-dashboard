<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class IncomingInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $order1Id = DB::table('orders')->where('ord_number', 'A-2023-001')->first()->id;
        $vendor1Id = DB::table('vendors')->where('vendor_name', 'Supply Co.')->first()->id;
        $departmentId = DB::table('departments')->where('department_code', 'D-20')->first()->id;

        DB::table('incoming_invoices')->insert([
            [
                'inv_number' => 'INC-INV-123',
                'inv_received_date' => '2023-01-22',
                'fp_date' => '2023-01-21',
                'fp_number' => 'FP-456-B',
                'cur' => 'USD',
                'amount' => 150.00,
                'payment_date' => '2023-02-01',
                'order_id' => $order1Id,
                'vendor_id' => $vendor1Id,
                'department_id' => $departmentId
            ]
        ]);
    }
}
