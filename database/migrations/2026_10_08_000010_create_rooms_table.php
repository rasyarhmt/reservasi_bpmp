<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->string('room_number', 20)->unique();
            $table->string('room_name', 100);
            $table->unsignedInteger('base_capacity')->default(4);
            $table->decimal('base_price', 12, 2)->default(359294.25);
            $table->decimal('extra_person_rate', 12, 2)->default(89823.00);
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
