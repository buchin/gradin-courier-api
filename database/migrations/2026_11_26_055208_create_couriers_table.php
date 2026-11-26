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
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('phone', 30)->unique();
            $table->string('email')->unique();
            $table->string('vehicle_type', 50);
            $table->string('license_plate', 20)->nullable()->unique();
            $table->unsignedTinyInteger('level')->default(1)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('registered_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
