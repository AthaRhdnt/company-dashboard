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
        Schema::table('outgoing_invoices', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable()->after('cur');
            $table->string('po_number')->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outgoing_invoices', function (Blueprint $table) {
            $table->dropColumn(['amount', 'po_number']);
        });
    }
};
