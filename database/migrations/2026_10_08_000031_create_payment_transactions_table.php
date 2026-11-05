<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->restrictOnDelete();
            $table->foreignId('checkout_log_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('payment_stage', ['down_payment', 'settlement', 'late_charge_settlement']);
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer'])->default('cash');
            $table->dateTime('transaction_date')->useCurrent();
            $table->string('notes', 255)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
