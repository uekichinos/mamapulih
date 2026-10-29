<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link } from '@inertiajs/vue3';

interface Meal {
    meal: string;
    dish: { id: number; name: Record<string, string> };
    substituted: boolean;
}

interface Day {
    date: string;
    status: string;
    meals: Meal[];
}

defineProps<{
    booking: { ref_no: string; start_date: string; end_date: string };
    days: Day[];
}>();

const mealLabel: Record<string, string> = {
    lunch: 'Lunch',
    dinner: 'Dinner',
};
</script>

<template>
    <CustomerLayout>
        <Head title="My schedule" />

        <div class="mb-4 flex items-center justify-between">
            <h1 class="font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">My schedule</h1>
            <Link :href="route('customer.my-booking')" class="text-sm text-indigo-600 underline">
                Back to My Booking
            </Link>
        </div>

        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ booking.ref_no }} &middot; {{ formatDate(booking.start_date) }} to
            {{ formatDate(booking.end_date) }}
        </p>

        <ul class="space-y-2">
            <li
                v-for="day in days"
                :key="day.date"
                class="rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ formatDate(day.date) }}</p>
                    <span v-if="day.status === 'skipped'" class="text-gray-500 dark:text-gray-400">
                        No delivery (closure day)
                    </span>
                </div>

                <ul v-if="day.meals.length" class="mt-2 space-y-1">
                    <li v-for="meal in day.meals" :key="meal.meal" class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">{{ mealLabel[meal.meal] ?? meal.meal }}</span>
                        <span class="text-gray-900 dark:text-gray-100">
                            {{ meal.dish.name.en }}
                            <span v-if="meal.substituted" class="text-gray-500 dark:text-gray-400">
                                (substituted)
                            </span>
                        </span>
                    </li>
                </ul>
            </li>
        </ul>
    </CustomerLayout>
</template>
