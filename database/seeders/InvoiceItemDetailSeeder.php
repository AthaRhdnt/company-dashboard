<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InvoiceItemDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quantity = 1;
        $outgoingInvoice = DB::table('outgoing_invoices')->where('inv_number', 'OUT-INV-001')->first();
        $itemSpec = DB::table('item_specs')->where('item_description', 'Custom WordPress theme build.')->first();

        if ($outgoingInvoice && $itemSpec) {
            // Get the item_id from the item_spec
            $itemId = $itemSpec->item_id;

            // Get the item price from the items table using the item_id
            $item = DB::table('items')->where('id', $itemId)->first();

            // Check if item exists to prevent errors
            if ($item) {
                // Correctly access the item_price property from the item object
                $unitPrice = $item->item_price;
                $outgoingInvoiceId = $outgoingInvoice->id;
                $itemSpecId = $itemSpec->id;

                // Insert into the invoice_item_details table
                DB::table('invoice_item_details')->insert([
                    [
                        'outgoing_invoice_id' => $outgoingInvoiceId,
                        'item_spec_id' => $itemSpecId,
                        'quantity' => $quantity,
                        'subtotal' => $quantity * $unitPrice,
                    ]
                ]);
            }
        }
    }
}
