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
        Schema::table('incoming_invoices', function (Blueprint $table) {
            $table->string('inv_number')->nullable()->change();
            $table->decimal('amount', 10, 2)->nullable()->change();
            $table->decimal('profit_percentage', 5, 2)->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_invoices', function (Blueprint $table) {
            $table->string('inv_number')->nullable(false)->change();
            $table->decimal('amount', 10, 2)->nullable(false)->change();
            $table->dropColumn('profit_percentage');
        });
    }
};
