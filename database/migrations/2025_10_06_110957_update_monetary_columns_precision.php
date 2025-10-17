<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 🚨 Update orders, outgoing_invoices, and incoming_invoices 'amount'
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('outgoing_invoices', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('incoming_invoices', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        // 🚨 Update items 'item_price'
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('item_price', 15, 2)->change();
        });

        // 🚨 Update invoice_item 'subtotal'
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert columns back to (10, 2)
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });

        Schema::table('outgoing_invoices', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });

        Schema::table('incoming_invoices', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->decimal('item_price', 10, 2)->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->change();
        });
    }
};
