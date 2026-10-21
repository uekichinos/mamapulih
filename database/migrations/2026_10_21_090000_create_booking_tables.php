<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('preferred_locale')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('do_not_contact')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('diet_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->unique()->constrained('customers')->cascadeOnDelete();
            $table->jsonb('allergies')->default('[]');
            $table->jsonb('exclusions')->default('[]');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained('zones')->restrictOnDelete();
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('postcode');
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('variant_id')->constrained('variants')->restrictOnDelete();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->date('edd');
            $table->string('expected_birth_type');
            $table->unsignedSmallInteger('duration_days');
            $table->string('meal_plan');
            $table->boolean('is_twins')->default(false);
            $table->unsignedTinyInteger('slots_used')->default(1);
            $table->string('status')->default('pending_approval');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('price_sen');
            $table->unsignedBigInteger('daily_rate_sen');
            $table->jsonb('policy_snapshot');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // No Blueprint helper exists for Postgres' range types, so the
        // containment column used by the capacity engine (docs §4) is
        // added with raw SQL, with a GiST index for the `@>` queries.
        DB::statement('ALTER TABLE bookings ADD COLUMN projected_period daterange NOT NULL');
        DB::statement('CREATE INDEX bookings_projected_period_gist ON bookings USING GIST (projected_period)');

        Schema::create('booking_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained('addons')->restrictOnDelete();
            $table->unsignedBigInteger('price_sen');
            $table->unsignedSmallInteger('qty')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_addons');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('diet_profiles');
        Schema::dropIfExists('customers');
    }
};
