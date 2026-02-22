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
        Schema::table('invoice_items', function (Blueprint $table) {
            // Drop FK: outgoing_invoice_id
            if (Schema::hasColumn('invoice_items', 'outgoing_invoice_id')) {
                $table->dropForeign('invoice_item_details_outgoing_invoice_id_foreign');
            }

            // Drop FK: item_id
            if (Schema::hasColumn('invoice_items', 'item_id')) {
                $table->dropForeign('invoice_items_item_id_foreign');
            }
        });

        // Now rename table
        Schema::rename('invoice_items', 'outgoing_invoice_items');

        // Re-add foreign keys under new table name
        Schema::table('outgoing_invoice_items', function (Blueprint $table) {
            $table->foreign('outgoing_invoice_id')
                ->references('id')->on('outgoing_invoices')
                ->onDelete('cascade');

            $table->foreign('item_id')
                ->references('id')->on('items')
                ->onDelete('cascade');
        });

        Schema::create('incoming_invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('incoming_invoice_id')
                ->constrained('incoming_invoices')
                ->onDelete('cascade');

            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('subtotal', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incoming_invoice_items');

        Schema::table('outgoing_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['outgoing_invoice_id']);
            $table->dropForeign(['item_id']);
        });

        Schema::rename('outgoing_invoice_items', 'invoice_items');

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreign('outgoing_invoice_id')
                ->references('id')->on('outgoing_invoices')
                ->onDelete('cascade');

            $table->foreign('item_id')
                ->references('id')->on('items')
                ->onDelete('cascade');
        });
    }
};
