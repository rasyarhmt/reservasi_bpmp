<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->enum('item_type', ['room', 'hall']);
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('hall_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('unit_price_snapshot', 12, 2);

            $table->unsignedInteger('guest_count')->nullable()->default(4);
            $table->unsignedInteger('extra_guests')->default(0);
            $table->decimal('extra_person_rate_snapshot', 12, 2)->default(0);

            $table->dateTime('hall_start_time')->nullable();
            $table->dateTime('hall_end_time')->nullable();
            $table->decimal('hall_hours', 5, 2)->default(0);

            $table->decimal('subtotal', 12, 2);
            $table->timestamp('created_at')->nullable();

            $table->index(['hall_id', 'hall_start_time', 'hall_end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_items');
    }
};
