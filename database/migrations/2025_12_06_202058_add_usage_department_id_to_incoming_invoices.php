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
            $table->foreignId('usage_department_id')->nullable()->after('department_id');
            $table->foreign('usage_department_id')
                ->references('id')->on('departments')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incoming_invoices', function (Blueprint $table) {
            $table->dropForeign(['usage_department_id']);
            $table->dropColumn('usage_department_id');
        });
    }
};
