<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->json('cost_components')->nullable()->after('extra_person_rate');
        });

        Schema::table('halls', function (Blueprint $table): void {
            $table->json('cost_components')->nullable()->after('price_per_hour');
        });

        Schema::table('reservation_items', function (Blueprint $table): void {
            $table->json('cost_components')->nullable()->after('unit_price_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('reservation_items', function (Blueprint $table): void {
            $table->dropColumn('cost_components');
        });

        Schema::table('halls', function (Blueprint $table): void {
            $table->dropColumn('cost_components');
        });

        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropColumn('cost_components');
        });
    }
};
