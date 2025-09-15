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
        Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
    $table->string('transaction_code')->unique();
    $table->string('payment_method')->nullable();
    $table->decimal('amount', 12, 2)->default(0);
    $table->decimal('total_amount', 12, 2)->default(0);
    $table->string('payment_status')->default('Pending');
    $table->string('midtrans_order_id')->nullable();
    $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
