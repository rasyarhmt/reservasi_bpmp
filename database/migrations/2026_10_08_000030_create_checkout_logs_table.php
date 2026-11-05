<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reservation_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('processed_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('actual_checkout_time');
            $table->unsignedInteger('late_hours')->default(0);
            $table->decimal('hourly_charge_rate', 10, 2)->default(17000.00);
            $table->decimal('total_late_charge', 12, 2)->default(0);
            $table->decimal('unpaid_base_amount', 12, 2)->default(0);
            $table->decimal('final_settlement_amount', 12, 2)->default(0);
            $table->boolean('is_settled')->default(false);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_logs');
    }
};
