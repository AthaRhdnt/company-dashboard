<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ----------------------------------------------------
        //  I. USER & PERMISSION DATA (3 ENTRIES EACH)
        // ----------------------------------------------------

        DB::table('levels')->insert([
            ['id' => 1, 'level_name' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'level_name' => 'Manager', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'level_name' => 'Staff', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Admin User', 'email' => 'admin@app.com', 'password' => Hash::make('password'), 'level_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Manager Smith', 'email' => 'manager@app.com', 'password' => Hash::make('password'), 'level_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Staff Jones', 'email' => 'staff@app.com', 'password' => Hash::make('password'), 'level_id' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // Permissions linked to Admin Level (ID 1)
        DB::table('permissions')->insert([
            ['id' => 1, 'permission_name' => 'view_all_reports', 'level_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'permission_name' => 'create_invoices', 'level_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'permission_name' => 'manage_users', 'level_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);


        // ----------------------------------------------------
        //  II. MASTER DATA (3 ENTRIES EACH)
        // ----------------------------------------------------

        DB::table('clients')->insert([
            ['id' => 1, 'client_name' => 'Tech Solutions Inc.', 'address' => '123 Tech St', 'phone_number' => '555-0101', 'fax_number' => '555-0102', 'contact_person_name' => 'Jane Doe', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'client_name' => 'Global Manufacturing', 'address' => '456 Global Ave', 'phone_number' => '555-0202', 'fax_number' => null, 'contact_person_name' => 'John Smith', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'client_name' => 'Local Retail Co.', 'address' => '789 Retail Rd', 'phone_number' => '555-0303', 'fax_number' => '555-0304', 'contact_person_name' => 'Alice Johnson', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('vendors')->insert([
            ['id' => 1, 'vendor_name' => 'Hardware Supply Inc.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'vendor_name' => 'Software License Corp.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'vendor_name' => 'Local Service Provider', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('departments')->insert([
            ['id' => 1, 'department_code' => 'D-10', 'department_name' => 'Sales', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'department_code' => 'D-20', 'department_name' => 'IT Projects', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'department_code' => 'D-30', 'department_name' => 'General Affairs', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('taxes')->insert([
            ['id' => 1, 'tax_name' => 'VAT (PPN)', 'tax_percentage' => 11.00, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'tax_name' => 'PPH 23', 'tax_percentage' => 2.00, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'tax_name' => 'PPH 21', 'tax_percentage' => 5.00, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('items')->insert([
            ['id' => 1, 'item_name' => 'Dell PowerEdge R650', 'item_price' => 75000000.00, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'item_name' => 'Cisco Catalyst 9300', 'item_price' => 45000000.00, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'item_name' => 'Microsoft 365 E5 License', 'item_price' => 3000000.00, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 9 Item Specs (3 for each of the 3 items)
        DB::table('item_specs')->insert([
            // Specs for Item 1 (R650 Server)
            ['id' => 1, 'item_id' => 1, 'item_description' => '128GB DDR4 RAM', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'item_id' => 1, 'item_description' => '2x 1.92TB SSD', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'item_id' => 1, 'item_description' => 'iDRAC Enterprise License', 'created_at' => now(), 'updated_at' => now()],
            
            // Specs for Item 2 (Cisco 9300 Switch)
            ['id' => 4, 'item_id' => 2, 'item_description' => '48-Port PoE+', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'item_id' => 2, 'item_description' => 'Network Advantage License', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'item_id' => 2, 'item_description' => 'Optional StackWise Cable', 'created_at' => now(), 'updated_at' => now()],

            // Specs for Item 3 (M365 License)
            ['id' => 7, 'item_id' => 3, 'item_description' => 'Per User/Month Subscription', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'item_id' => 3, 'item_description' => '5 Year Prepaid Term', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'item_id' => 3, 'item_description' => 'Microsoft Azure AD Premium 2', 'created_at' => now(), 'updated_at' => now()],
        ]);


        // ----------------------------------------------------
        //  III. TRANSACTIONAL DATA (3 ENTRIES EACH)
        // ----------------------------------------------------

        DB::table('purchase_orders')->insert([
            ['id' => 1, 'po_number' => 'PO-TS-001', 'po_date' => '2025-01-05', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'po_number' => 'PO-GM-002', 'po_date' => '2025-01-10', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'po_number' => 'PO-LR-003', 'po_date' => '2025-01-15', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('orders')->insert([
            [
                'id' => 1, 'ord_number' => 'ORD-2025-001', 'ord_date' => '2025-01-07', 'project_name' => 'Data Center Upgrade', 'cur' => 'IDR', 'amount' => 123000000.00, 
                'client_id' => 1, 'purchase_order_id' => 1, 'department_id' => 2, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 2, 'ord_number' => 'ORD-2025-002', 'ord_date' => '2025-01-12', 'project_name' => 'Network Refresh', 'cur' => 'USD', 'amount' => 50000.00, 
                'client_id' => 2, 'purchase_order_id' => 2, 'department_id' => 1, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 3, 'ord_number' => 'ORD-2025-003', 'ord_date' => '2025-01-17', 'project_name' => 'Software Licensing', 'cur' => 'IDR', 'amount' => 15000000.00, 
                'client_id' => 3, 'purchase_order_id' => 3, 'department_id' => 3, 'created_at' => now(), 'updated_at' => now()
            ],
        ]);

        DB::table('outgoing_invoices')->insert([
            [
                'id' => 1, 'inv_number' => 'OUT-INV-001', 'inv_date' => '2025-02-01', 'due_date' => '2025-03-03', 'fp_number' => 'FP-001', 'income_date' => '2025-03-01', 'cur' => 'IDR', 'amount' => 136530000.00, 'po_number' => 'PO-TS-001', 
                'order_id' => 1, 'client_id' => 1, 'department_id' => 2, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 2, 'inv_number' => 'OUT-INV-002', 'inv_date' => '2025-02-10', 'due_date' => '2025-03-10', 'fp_number' => 'FP-002', 'income_date' => '2025-03-08', 'cur' => 'USD', 'amount' => 55500.00, 'po_number' => 'PO-GM-002', 
                'order_id' => 2, 'client_id' => 2, 'department_id' => 1, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 3, 'inv_number' => 'OUT-INV-003', 'inv_date' => '2025-02-15', 'due_date' => '2025-03-15', 'fp_number' => 'FP-003', 'income_date' => '2025-03-12', 'cur' => 'IDR', 'amount' => 16650000.00, 'po_number' => 'PO-LR-003', 
                'order_id' => 3, 'client_id' => 3, 'department_id' => 3, 'created_at' => now(), 'updated_at' => now()
            ],
        ]);

        DB::table('incoming_invoices')->insert([
            [
                'id' => 1, 'inv_number' => 'IN-INV-V001', 'inv_received_date' => '2025-01-20', 'fp_date' => '2025-01-22', 'fp_number' => 'VFP-001', 'cur' => 'IDR', 'amount' => 88800000.00, 'profit_percentage' => 30.00, 'payment_date' => '2025-02-15', 
                'order_id' => 1, 'vendor_id' => 1, 'department_id' => 2, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 2, 'inv_number' => 'IN-INV-V002', 'inv_received_date' => '2025-01-25', 'fp_date' => '2025-01-27', 'fp_number' => 'VFP-002', 'cur' => 'USD', 'amount' => 30000.00, 'profit_percentage' => 40.00, 'payment_date' => '2025-02-20', 
                'order_id' => 2, 'vendor_id' => 2, 'department_id' => 1, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 3, 'inv_number' => 'IN-INV-V003', 'inv_received_date' => '2025-01-28', 'fp_date' => '2025-01-30', 'fp_number' => 'VFP-003', 'cur' => 'IDR', 'amount' => 10000000.00, 'profit_percentage' => 20.00, 'payment_date' => '2025-02-25', 
                'order_id' => 3, 'vendor_id' => 3, 'department_id' => 3, 'created_at' => now(), 'updated_at' => now()
            ],
        ]);


        // ----------------------------------------------------
        //  IV. PIVOT & LINE ITEM DATA
        // ----------------------------------------------------
        
        // Outgoing Invoice 1 has 3 line items, one for each Item (1, 2, 3)
        DB::table('invoice_items')->insert([
            ['id' => 1, 'outgoing_invoice_id' => 1, 'item_id' => 1, 'quantity' => 1, 'subtotal' => 75000000.00, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'outgoing_invoice_id' => 1, 'item_id' => 2, 'quantity' => 1, 'subtotal' => 45000000.00, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'outgoing_invoice_id' => 1, 'item_id' => 3, 'quantity' => 1, 'subtotal' => 3000000.00, 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // 9 Invoice Item Specs (3 specs for each line item)
        DB::table('invoice_item_specs')->insert([
            // Specs for Line Item 1 (Item 1: Server) -> Specs 1, 2, 3
            ['id' => 1, 'invoice_item_id' => 1, 'item_spec_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'invoice_item_id' => 1, 'item_spec_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'invoice_item_id' => 1, 'item_spec_id' => 3, 'created_at' => now(), 'updated_at' => now()],
            
            // Specs for Line Item 2 (Item 2: Switch) -> Specs 4, 5, 6
            ['id' => 4, 'invoice_item_id' => 2, 'item_spec_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'invoice_item_id' => 2, 'item_spec_id' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'invoice_item_id' => 2, 'item_spec_id' => 6, 'created_at' => now(), 'updated_at' => now()],

            // Specs for Line Item 3 (Item 3: License) -> Specs 7, 8, 9
            ['id' => 7, 'invoice_item_id' => 3, 'item_spec_id' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'invoice_item_id' => 3, 'item_spec_id' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'invoice_item_id' => 3, 'item_spec_id' => 9, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Outgoing Invoice Taxes (3 entries)
        DB::table('outgoing_invoice_taxes')->insert([
            // Link Inv 1 to VAT (Tax 1)
            ['id' => 1, 'outgoing_invoice_id' => 1, 'tax_id' => 1, 'created_at' => now(), 'updated_at' => now()], 
            // Link Inv 2 to PPH 23 (Tax 2)
            ['id' => 2, 'outgoing_invoice_id' => 2, 'tax_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            // Link Inv 3 to PPH 21 (Tax 3)
            ['id' => 3, 'outgoing_invoice_id' => 3, 'tax_id' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Incoming Invoice Taxes (3 entries)
        DB::table('incoming_invoice_taxes')->insert([
            // Link Incoming Inv 1 to VAT (Tax 1)
            ['id' => 1, 'incoming_invoice_id' => 1, 'tax_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            // Link Incoming Inv 2 to VAT (Tax 1) - Vendors usually charge VAT
            ['id' => 2, 'incoming_invoice_id' => 2, 'tax_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            // Link Incoming Inv 3 to PPH 23 (Tax 2)
            ['id' => 3, 'incoming_invoice_id' => 3, 'tax_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the database seeds.
     */
    public function down(): void
    {
        // Truncate all tables in reverse order to respect foreign keys
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        
        DB::table('incoming_invoice_taxes')->truncate();
        DB::table('outgoing_invoice_taxes')->truncate();
        DB::table('invoice_item_specs')->truncate();
        DB::table('invoice_items')->truncate();
        DB::table('incoming_invoices')->truncate();
        DB::table('outgoing_invoices')->truncate();
        DB::table('orders')->truncate();
        DB::table('purchase_orders')->truncate();
        DB::table('item_specs')->truncate();
        DB::table('items')->truncate();
        DB::table('taxes')->truncate();
        DB::table('departments')->truncate();
        DB::table('vendors')->truncate();
        DB::table('clients')->truncate();
        DB::table('permissions')->truncate();
        DB::table('users')->truncate();
        DB::table('levels')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}