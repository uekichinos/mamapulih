<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->jsonb('name');
            $table->jsonb('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Minimal stub now so `variants.menu_rotation_id` has somewhere to point;
        // the full menu/rotation-day structure is built in Sprint 4.
        Schema::create('menu_rotations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->string('birth_type');
            $table->unsignedSmallInteger('duration_days');
            $table->string('meal_plan');
            $table->unsignedTinyInteger('meals_per_day');
            $table->foreignId('menu_rotation_id')->nullable()->constrained('menu_rotations')->nullOnDelete();
            $table->jsonb('included_items')->nullable();
            $table->unsignedBigInteger('price_sen');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->jsonb('name');
            $table->unsignedBigInteger('price_sen');
            $table->string('per');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addons');
        Schema::dropIfExists('variants');
        Schema::dropIfExists('menu_rotations');
        Schema::dropIfExists('packages');
    }
};
