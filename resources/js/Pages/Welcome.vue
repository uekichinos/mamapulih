<script setup lang="ts">
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
    <Head title="MamaPulih — confinement meal packages" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <header class="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-4">
                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                    >MamaPulih</span
                >
                <nav class="flex items-center gap-4 text-sm">
                    <Link
                        v-if="isCustomer()"
                        :href="route('customer.my-booking')"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        My Booking
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('customer.login')"
                            class="text-gray-600 dark:text-gray-400"
                            >Log in</Link
                        >
                        <Link :href="route('customer.register')" class="font-medium text-indigo-600"
                            >Get started</Link
                        >
                    </template>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-4 py-10">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                Confinement meal packages
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Daily cooked confinement meals, delivered. Book against your due date.
            </p>

            <div class="mt-8 space-y-6">
                <div
                    v-for="pkg in packages"
                    :key="pkg.id"
                    class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800"
                >
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ pkg.name.en }}
                    </h2>
                    <p v-if="pkg.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ pkg.description.en }}
                    </p>

                    <ul class="mt-4 divide-y divide-gray-100 dark:divide-gray-700">
                        <li
                            v-for="variant in pkg.variants"
                            :key="variant.id"
                            class="flex items-center justify-between py-2 text-sm"
                        >
                            <span class="text-gray-700 dark:text-gray-300">
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

                <p v-if="packages.length === 0" class="text-gray-500">
                    No packages are available yet.
                </p>
            </div>

            <div class="mt-10">
                <Link
                    :href="
                        isCustomer() ? route('customer.booking.build') : route('customer.register')
                    "
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    Book a package
                </Link>
            </div>
        </main>
    </div>
</template>
