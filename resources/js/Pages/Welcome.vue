<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

interface Variant {
    id: number;
    birth_type: string;
    duration_days: number;
    meal_plan: string;
    price_sen: number;
}

interface Package {
    id: number;
    name: Record<string, string>;
    description: Record<string, string> | null;
    variants: Variant[];
}

defineProps<{
    packages: Package[];
}>();

const page = usePage();
const isCustomer = () => Boolean((page.props as { auth?: { customer?: unknown } }).auth?.customer);
const money = (sen: number) => (sen / 100).toFixed(2);
</script>

<template>
    <CustomerLayout>
        <Head title="MamaPulih — confinement meal packages" />

        <h1 class="font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
            Confinement meal packages
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Daily cooked confinement meals, delivered. Book against your due date.
        </p>

        <div class="mt-6 space-y-3">
            <div
                v-for="pkg in packages"
                :key="pkg.id"
                class="rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <h2 class="font-display text-base font-extrabold text-gray-900 dark:text-gray-50">
                    {{ pkg.name.en }}
                </h2>
                <p v-if="pkg.description" class="mt-1 text-gray-600 dark:text-gray-400">
                    {{ pkg.description.en }}
                </p>

                <ul class="mt-3 divide-y divide-gray-100 dark:divide-gray-800">
                    <li
                        v-for="variant in pkg.variants"
                        :key="variant.id"
                        class="flex items-center justify-between py-2"
                    >
                        <span class="text-gray-600 dark:text-gray-400">
                            {{ variant.birth_type === 'csec' ? 'C-sec' : 'Normal' }}
                            · {{ variant.duration_days }} days ·
                            {{
                                variant.meal_plan === 'lunch_dinner'
                                    ? 'Lunch + Dinner'
                                    : 'Lunch only'
                            }}
                        </span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">
                            RM {{ money(variant.price_sen) }}
                        </span>
                    </li>
                </ul>
            </div>

            <p v-if="packages.length === 0" class="text-gray-600 dark:text-gray-400">
                No packages are available yet.
            </p>
        </div>

        <div class="mt-6">
            <Link
                :href="isCustomer() ? route('customer.booking.build') : route('customer.register')"
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
            >
                Book a package
            </Link>
        </div>
    </CustomerLayout>
</template>
