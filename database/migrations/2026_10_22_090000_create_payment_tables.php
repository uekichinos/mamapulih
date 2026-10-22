<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('actual_birth_type')->nullable()->after('expected_birth_type');
            $table->date('start_date')->nullable()->after('actual_birth_type');
            $table->date('end_date')->nullable()->after('start_date');
            $table->string('payment_option')->nullable()->after('status');
            $table->timestamp('payment_due_at')->nullable()->after('payment_option');
        });

        Schema::create('payment_providers', function (Blueprint $table) {
            $table->id();
            $table->string('driver');
            $table->boolean('enabled')->default(true);
            $table->string('mode')->default('live');
            // Encrypted ciphertext isn't valid JSON, so this can't be jsonb.
            $table->text('credentials')->nullable();
            $table->string('display_name');
            $table->jsonb('instructions')->nullable();
            $table->timestamps();

            $table->unique('driver');
        });

        // The manual driver is core, not a demo artifact — every install
        // needs this row to record payments against.
        DB::table('payment_providers')->insert([
            'driver' => 'manual',
            'enabled' => true,
            'mode' => 'live',
            'credentials' => null,
            'display_name' => 'Bank transfer / DuitNow QR',
            'instructions' => json_encode(['en' => '', 'ms' => '']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('payment_providers')->restrictOnDelete();
            $table->string('type');
            $table->unsignedBigInteger('amount_sen');
            $table->date('payment_date');
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('verified');
            $table->timestamps();
        });

        Schema::create('plan_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('from_variant_id')->constrained('variants')->restrictOnDelete();
            $table->foreignId('to_variant_id')->constrained('variants')->restrictOnDelete();
            $table->date('effective_date');
            $table->unsignedSmallInteger('remaining_days');
            $table->bigInteger('amount_sen');
            $table->string('override_reason')->nullable();
            $table->string('status')->default('awaiting_consent');
            $table->timestamps();
        });

        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_change_id')->constrained('plan_changes')->cascadeOnDelete();
            $table->string('token_hash')->unique();
            $table->timestamp('expires_at');
            $table->string('decision')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->jsonb('snapshot');
            $table->string('terms_version')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('plan_change_id')->nullable()->constrained('plan_changes')->nullOnDelete();
            $table->string('reason');
            $table->unsignedBigInteger('amount_sen');
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->string('proof_path')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('consents');
        Schema::dropIfExists('plan_changes');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_providers');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['actual_birth_type', 'start_date', 'end_date', 'payment_option', 'payment_due_at']);
        });
    }
};
