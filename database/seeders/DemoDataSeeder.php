<?php

namespace Database\Seeders;

use App\Enums\DietTag;
use App\Models\Addon;
use App\Models\BusinessProfile;
use App\Models\ClosureDay;
use App\Models\Dish;
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

        $dishes = collect([
            ['name' => ['en' => 'Ginger Chicken', 'ms' => 'Ayam Masak Halia'], 'tags' => []],
            ['name' => ['en' => 'Steamed Fish', 'ms' => 'Ikan Kukus Stim'], 'tags' => [DietTag::Seafood->value]],
            ['name' => ['en' => 'Spinach & Beef Bone Soup', 'ms' => 'Sup Tulang Bayam'], 'tags' => [DietTag::Beef->value]],
            ['name' => ['en' => 'Herbal Omelette', 'ms' => 'Telur Dadar Herba'], 'tags' => [DietTag::Egg->value]],
            ['name' => ['en' => 'Prawn Coconut Curry', 'ms' => 'Udang Masak Lemak Cili Padi'], 'tags' => [DietTag::Seafood->value, DietTag::Spicy->value]],
            ['name' => ['en' => 'Mixed Boiled Vegetables', 'ms' => 'Sayur Campur Rebus'], 'tags' => [DietTag::CsecSafe->value]],
            ['name' => ['en' => 'Plain Rice Porridge', 'ms' => 'Bubur Nasi Putih'], 'tags' => [DietTag::CsecSafe->value]],
            ['name' => ['en' => 'Chicken Korma', 'ms' => 'Ayam Kurma'], 'tags' => [DietTag::Nuts->value]],
            ['name' => ['en' => 'Beef in Soy Sauce', 'ms' => 'Daging Masak Kicap'], 'tags' => [DietTag::Beef->value]],
            ['name' => ['en' => 'Snakehead Fish Soup', 'ms' => 'Sup Ikan Haruan'], 'tags' => [DietTag::Seafood->value]],
            ['name' => ['en' => 'Chicken Mee Sua Soup', 'ms' => 'Mee Sua Ayam'], 'tags' => [DietTag::Gluten->value]],
            ['name' => ['en' => 'Black Glutinous Rice Porridge', 'ms' => 'Bubur Pulut Hitam Santan'], 'tags' => [DietTag::Dairy->value]],
        ])->mapWithKeys(fn (array $dish) => [$dish['name']['en'] => Dish::query()->create($dish)]);

        // Give every tagged dish a plain, universally safe fallback — the
        // prep sheet substitutes into this whenever a customer's exclusion
        // matches the original dish's tags.
        $dishes['Steamed Fish']->update(['substitute_dish_id' => $dishes['Ginger Chicken']->id]);
        $dishes['Spinach & Beef Bone Soup']->update(['substitute_dish_id' => $dishes['Mixed Boiled Vegetables']->id]);
        $dishes['Prawn Coconut Curry']->update(['substitute_dish_id' => $dishes['Ginger Chicken']->id]);
        $dishes['Chicken Korma']->update(['substitute_dish_id' => $dishes['Ginger Chicken']->id]);
        $dishes['Beef in Soy Sauce']->update(['substitute_dish_id' => $dishes['Ginger Chicken']->id]);
        $dishes['Snakehead Fish Soup']->update(['substitute_dish_id' => $dishes['Mixed Boiled Vegetables']->id]);
        $dishes['Chicken Mee Sua Soup']->update(['substitute_dish_id' => $dishes['Plain Rice Porridge']->id]);
        $dishes['Black Glutinous Rice Porridge']->update(['substitute_dish_id' => $dishes['Plain Rice Porridge']->id]);
        $dishes['Herbal Omelette']->update(['substitute_dish_id' => $dishes['Mixed Boiled Vegetables']->id]);

        $standardMenu = [
            1 => ['lunch' => 'Ginger Chicken', 'dinner' => 'Spinach & Beef Bone Soup'],
            2 => ['lunch' => 'Steamed Fish', 'dinner' => 'Mixed Boiled Vegetables'],
            3 => ['lunch' => 'Beef in Soy Sauce', 'dinner' => 'Plain Rice Porridge'],
            4 => ['lunch' => 'Chicken Korma', 'dinner' => 'Snakehead Fish Soup'],
            5 => ['lunch' => 'Prawn Coconut Curry', 'dinner' => 'Herbal Omelette'],
            6 => ['lunch' => 'Chicken Mee Sua Soup', 'dinner' => 'Black Glutinous Rice Porridge'],
            7 => ['lunch' => 'Ginger Chicken', 'dinner' => 'Mixed Boiled Vegetables'],
        ];

        // Gentler on the stomach for a C-sec recovery — the extra herbal
        // soup already comes from the variant's included add-on, so this
        // rotation skips the spiciest/heaviest dishes.
        $csecMenu = [
            1 => ['lunch' => 'Ginger Chicken', 'dinner' => 'Plain Rice Porridge'],
            2 => ['lunch' => 'Steamed Fish', 'dinner' => 'Mixed Boiled Vegetables'],
            3 => ['lunch' => 'Herbal Omelette', 'dinner' => 'Spinach & Beef Bone Soup'],
            4 => ['lunch' => 'Beef in Soy Sauce', 'dinner' => 'Plain Rice Porridge'],
            5 => ['lunch' => 'Chicken Korma', 'dinner' => 'Mixed Boiled Vegetables'],
            6 => ['lunch' => 'Snakehead Fish Soup', 'dinner' => 'Chicken Mee Sua Soup'],
            7 => ['lunch' => 'Ginger Chicken', 'dinner' => 'Black Glutinous Rice Porridge'],
        ];

        foreach (['standard' => [$standardRotation, $standardMenu], 'csec' => [$csecRotation, $csecMenu]] as [$rotation, $menu]) {
            foreach ($menu as $day => $meals) {
                foreach ($meals as $meal => $dishName) {
                    $rotation->rotationDays()->create([
                        'day_no' => $day,
                        'meal' => $meal,
                        'dish_id' => $dishes[$dishName]->id,
                    ]);
                }
            }
        }

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
