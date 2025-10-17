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
        // 1. Add 'remark' column to orders, incoming_invoices, and outgoing_invoices
        Schema::table('orders', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('department_id');
        });

        Schema::table('incoming_invoices', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('department_id');
        });

        Schema::table('outgoing_invoices', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('department_id');
        });

        // 2. Make order_id in incoming_invoices nullable
        // NOTE: You must have the 'doctrine/dbal' package installed for ->change() to work.
        Schema::table('incoming_invoices', function (Blueprint $table) {
            // FIX: Use the underlying column type (unsignedBigInteger) and skip foreignId()/constrained()
            $table->unsignedBigInteger('order_id')->nullable()->change();
        });
    }

    // ---

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop 'remark' column
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('remark');
        });

        Schema::table('incoming_invoices', function (Blueprint $table) {
            $table->dropColumn('remark');
        });

        Schema::table('outgoing_invoices', function (Blueprint $table) {
            $table->dropColumn('remark');
        });

        // 2. Revert order_id in incoming_invoices to NOT nullable
        Schema::table('incoming_invoices', function (Blueprint $table) {
            // FIX: Use the underlying column type (unsignedBigInteger) and skip foreignId()/constrained()
            // Setting nullable(false) reverts the column to NOT NULL.
            $table->unsignedBigInteger('order_id')->nullable(false)->change();
        });
    }
};