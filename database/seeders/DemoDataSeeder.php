<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\BusinessProfile;
use App\Models\ClosureDay;
use App\Models\MenuRotation;
use App\Models\Package;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;

/**
 * Realistic demo catalogue for a fresh install (business profile, zones,
 * packages/variants, add-ons, one closure day). Bookings are intentionally
 * not seeded here: the Booking model arrives in Sprint 2, so there is
 * nothing to seed yet.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        BusinessProfile::current()->update([
            'name' => 'MamaPulih Demo Kitchen',
            'contact_phone' => '+60123456789',
            'email' => 'hello@example.com',
            'address' => "Lot 1, Jalan Demo\n40000 Shah Alam, Selangor",
        ]);

        $zones = [
            ['name' => 'BSP', 'postcodes' => ['47810']],
            ['name' => 'Putra Heights', 'postcodes' => ['47130']],
            ['name' => 'USJ', 'postcodes' => ['47600', '47610', '47620']],
            ['name' => 'Putrajaya', 'postcodes' => ['62000']],
            ['name' => 'Cyberjaya', 'postcodes' => ['63000']],
            ['name' => 'Shah Alam', 'postcodes' => ['40000']],
        ];

        foreach ($zones as $zone) {
            Zone::query()->firstOrCreate(['name' => $zone['name']], [
                'postcodes' => $zone['postcodes'],
                'free_delivery' => true,
                'transport_cost_per_trip_sen' => 0,
                'one_trip_allowed' => false,
            ]);
        }

        if (Package::query()->exists()) {
            return;
        }

        $standardRotation = MenuRotation::query()->create(['name' => ['en' => 'Standard', 'ms' => 'Standard']]);
        $csecRotation = MenuRotation::query()->create(['name' => ['en' => 'C-Sec', 'ms' => 'C-Sec']]);

        $standard = Package::query()->create([
            'name' => ['en' => 'Standard (Normal birth)', 'ms' => 'Standard (Bersalin normal)'],
            'description' => [
                'en' => 'Daily confinement meals for a normal birth.',
                'ms' => 'Makanan pantang harian untuk bersalin normal.',
            ],
        ]);

        $csec = Package::query()->create([
            'name' => ['en' => 'C-Sec Care', 'ms' => 'Jagaan Pembedahan Caesarean'],
            'description' => [
                'en' => 'Daily confinement meals with extra recovery-focused dishes for a C-sec birth.',
                'ms' => 'Makanan pantang harian dengan hidangan tambahan untuk pemulihan pembedahan caesarean.',
            ],
        ]);

        foreach ([7, 14, 21, 28] as $days) {
            $standard->variants()->create([
                'birth_type' => 'normal',
                'duration_days' => $days,
                'meal_plan' => 'lunch_dinner',
                'meals_per_day' => 2,
                'menu_rotation_id' => $standardRotation->id,
                'price_sen' => $days * 5000,
            ]);

            $csec->variants()->create([
                'birth_type' => 'csec',
                'duration_days' => $days,
                'meal_plan' => 'lunch_dinner',
                'meals_per_day' => 2,
                'menu_rotation_id' => $csecRotation->id,
                'included_items' => ['daily_herbal_soup'],
                'price_sen' => $days * 6000,
            ]);
        }

        Addon::query()->firstOrCreate(['per' => 'day'], [
            'name' => ['en' => 'Extra portion (husband)', 'ms' => 'Porsi tambahan (suami)'],
            'price_sen' => 1500,
            'active' => true,
        ]);

        Addon::query()->firstOrCreate(['per' => 'booking'], [
            'name' => ['en' => 'Herbal drink', 'ms' => 'Air herba'],
            'price_sen' => 3000,
            'active' => true,
        ]);

        if ($owner = User::query()->where('role', 'owner')->first()) {
            ClosureDay::query()->firstOrCreate(['date' => now()->addMonths(2)->startOfMonth()->addDays(9)], [
                'scope' => 'all',
                'reason' => 'Sample public holiday (edit or delete me)',
                'source' => 'manual',
                'created_by' => $owner->id,
            ]);
        }
    }
}
