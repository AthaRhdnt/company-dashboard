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
        Schema::create('outgoing_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('inv_number')->unique();
            $table->date('inv_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('fp_number')->nullable();
            $table->date('income_date')->nullable();
            $table->string('cur', 10)->nullable();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outgoing_invoices');
    }
};
