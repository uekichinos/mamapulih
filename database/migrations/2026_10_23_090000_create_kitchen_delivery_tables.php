<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // menu_rotations.name was a Sprint 1 stub (plain string) — bring it
        // in line with every other catalog model's BM/EN jsonb name.
        Schema::table('menu_rotations', function (Blueprint $table) {
            $table->renameColumn('name', 'name_old');
        });

        Schema::table('menu_rotations', function (Blueprint $table) {
            $table->jsonb('name')->nullable()->after('id');
        });

        DB::table('menu_rotations')->get()->each(function ($rotation) {
            DB::table('menu_rotations')->where('id', $rotation->id)->update([
                'name' => json_encode(['en' => $rotation->name_old, 'ms' => $rotation->name_old]),
            ]);
        });

        Schema::table('menu_rotations', function (Blueprint $table) {
            $table->dropColumn('name_old');
        });

        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->jsonb('name');
            $table->jsonb('tags')->default('[]');
            $table->foreignId('substitute_dish_id')->nullable()->constrained('dishes')->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('rotation_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_rotation_id')->constrained('menu_rotations')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_no');
            $table->string('meal');
            $table->foreignId('dish_id')->constrained('dishes')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['menu_rotation_id', 'day_no', 'meal']);
        });

        Schema::create('booking_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('menu_rotation_id')->nullable()->constrained('menu_rotations')->nullOnDelete();
            $table->string('status')->default('scheduled');
            $table->timestamps();

            $table->unique(['booking_id', 'date']);
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_day_id')->constrained('booking_days')->cascadeOnDelete();
            $table->string('run');
            $table->boolean('includes_dinner')->default(false);
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->unsignedBigInteger('transport_cost_sen')->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('pauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->string('reason')->default('normal');
            $table->boolean('limit_overridden')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pauses');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('booking_days');
        Schema::dropIfExists('rotation_days');
        Schema::dropIfExists('dishes');

        Schema::table('menu_rotations', function (Blueprint $table) {
            $table->renameColumn('name', 'name_old');
        });

        Schema::table('menu_rotations', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        DB::table('menu_rotations')->get()->each(function ($rotation) {
            $decoded = json_decode((string) $rotation->name_old, true);
            DB::table('menu_rotations')->where('id', $rotation->id)->update([
                'name' => $decoded['en'] ?? null,
            ]);
        });

        Schema::table('menu_rotations', function (Blueprint $table) {
            $table->dropColumn('name_old');
        });
    }
};
