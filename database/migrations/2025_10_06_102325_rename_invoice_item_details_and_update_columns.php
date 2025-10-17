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
        // 1. Rename the table
        Schema::rename('invoice_item_details', 'invoice_items');

        // 2. Drop the old foreign key column
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign('invoice_item_details_item_spec_id_foreign');
            $table->dropColumn('item_spec_id');
        });

        // 3. Add the new foreign key column
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('item_id')->constrained()->onDelete('cascade')->after('outgoing_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert the column changes
        Schema::table('invoice_item', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->dropColumn('item_id');
            $table->foreignId('item_spec_id')->constrained()->onDelete('cascade')->after('outgoing_invoice_id');
        });

        // 2. Rename the table back
        Schema::rename('invoice_items', 'invoice_item_details');
    }
};
