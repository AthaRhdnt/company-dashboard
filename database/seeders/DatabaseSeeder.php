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

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            LevelSeeder::class,
            UserSeeder::class, // Depends on LevelSeeder

            // ClientSeeder::class,
            // VendorSeeder::class,
            // DepartmentSeeder::class,
            // ItemSeeder::class,
            // PurchaseOrderSeeder::class,

            // ItemSpecSeeder::class, // Depends on ItemSeeder
            // OrderSeeder::class, // Depends on Client, Dept, PO Seeders

            // OutgoingInvoiceSeeder::class, // Depends on Order & Client Seeders
            // IncomingInvoiceSeeder::class, // Depends on Order & Vendor Seeders

            // InvoiceItemDetailSeeder::class, // Depends on OutgoingInvoice & ItemSpec Seeders

            // DummySeeder::class
            ProductionDataSeeder::class,
        ]);
    }
}
