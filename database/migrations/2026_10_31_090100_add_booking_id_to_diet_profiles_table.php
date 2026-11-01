<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Diet profiles used to be one-per-customer (unique `customer_id`),
     * shared across every booking that customer ever made. That silently
     * broke the moment one account had two concurrent bookings — e.g. two
     * wives expecting at once — since the second booking's allergies would
     * overwrite the first's. This makes diet profiles per-booking instead.
     */
    public function up(): void
    {
        Schema::table('diet_profiles', function (Blueprint $table) {
            $table->dropUnique(['customer_id']);
            $table->foreignId('booking_id')->nullable()->after('customer_id')->constrained('bookings')->cascadeOnDelete();
            $table->unique('booking_id');
        });

        // Best-effort backfill: attach each existing (customer-keyed) diet
        // profile to that customer's current live booking, if any, so an
        // already-active booking doesn't silently lose its diet
        // substitution the moment this ships. A profile with no live
        // booking to attach to is left unlinked — it was never going to be
        // read anywhere once DietProfile moves to booking_id lookups.
        $terminal = ['rejected', 'expired', 'cancelled', 'completed'];

        DB::table('diet_profiles')->whereNull('booking_id')->orderBy('id')->get(['id', 'customer_id'])
            ->each(function ($profile) use ($terminal) {
                $bookingId = DB::table('bookings')
                    ->where('customer_id', $profile->customer_id)
                    ->whereNotIn('status', $terminal)
                    ->orderByDesc('id')
                    ->value('id');

                if ($bookingId) {
                    DB::table('diet_profiles')->where('id', $profile->id)->update(['booking_id' => $bookingId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('diet_profiles', function (Blueprint $table) {
            $table->dropUnique(['booking_id']);
            $table->dropConstrainedForeignId('booking_id');
            $table->unique('customer_id');
        });
    }
};
