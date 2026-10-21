<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

interface BookingRow {
    id: number;
    ref_no: string;
    status: string;
    edd: string;
    price_sen: number;
    customer: { name: string };
    variant: { package: { name: Record<string, string> } };
}

defineProps<{
    bookings: {
        data: BookingRow[];
        prev_page_url: string | null;
        next_page_url: string | null;
    };
}>();

const money = (sen: number) => (sen / 100).toFixed(2);
</script>

<template>
    <AdminLayout>
        <Head title="Bookings" />

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Bookings</h1>
            <Link :href="route('admin.bookings.create')">
                <PrimaryButton>Create booking</PrimaryButton>
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">Ref</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Package</th>
                        <th class="px-4 py-3">EDD</th>
                        <th class="px-4 py-3">Price</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="booking in bookings.data" :key="booking.id">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                            {{ booking.ref_no }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ booking.customer.name }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ booking.variant.package.name.en }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ booking.edd }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            RM {{ money(booking.price_sen) }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ booking.status }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link
                                :href="route('admin.bookings.show', booking.id)"
                                class="text-indigo-600 hover:text-indigo-700"
                            >
                                View
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="bookings.data.length === 0">
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                            No bookings yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-between text-sm">
            <Link
                v-if="bookings.prev_page_url"
                :href="bookings.prev_page_url"
                class="text-indigo-600"
                >Previous</Link
            >
            <span v-else></span>
            <Link
                v-if="bookings.next_page_url"
                :href="bookings.next_page_url"
                class="text-indigo-600"
                >Next</Link
            >
        </div>
    </AdminLayout>
</template>
