<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->jsonb('postcodes');
            $table->boolean('free_delivery')->default(true);
            $table->unsignedBigInteger('transport_cost_per_trip_sen')->default(0);
            $table->boolean('one_trip_allowed')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
