<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Booking {
    ref_no: string;
    status: string;
    edd: string;
    expected_birth_type: string;
    duration_days: number;
    meal_plan: string;
    is_twins: boolean;
    price_sen: number;
    rejection_reason: string | null;
    customer: { name: string; email: string | null; phone: string | null };
    variant: { package: { name: Record<string, string> } };
    address: {
        line1: string;
        line2: string | null;
        postcode: string;
        zone: { name: string };
    } | null;
    addons: { addon: { name: Record<string, string> }; qty: number; price_sen: number }[];
    approver: { name: string } | null;
}

defineProps<{ booking: Booking }>();

const money = (sen: number) => (sen / 100).toFixed(2);
</script>

<template>
    <AdminLayout>
        <Head :title="`Booking ${booking.ref_no}`" />

        <h1 class="mb-6 text-2xl font-semibold text-gray-900 dark:text-gray-100">
            Booking {{ booking.ref_no }}
        </h1>

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Customer</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.customer.name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.customer.email }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.customer.phone }}</p>
            </div>

            <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Plan</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ booking.variant.package.name.en }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ booking.expected_birth_type === 'csec' ? 'C-sec' : 'Normal' }}
                    · {{ booking.duration_days }} days ·
                    {{ booking.meal_plan === 'lunch_dinner' ? 'Lunch + Dinner' : 'Lunch only' }}
                    <span v-if="booking.is_twins">· Twins</span>
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">EDD: {{ booking.edd }}</p>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    RM {{ money(booking.price_sen) }}
                </p>
            </div>

            <div v-if="booking.address" class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Delivery</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ booking.address.line1
                    }}<span v-if="booking.address.line2">, {{ booking.address.line2 }}</span
                    >,
                    {{ booking.address.postcode }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Zone: {{ booking.address.zone.name }}
                </p>
            </div>

            <div
                v-if="booking.addons.length"
                class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800"
            >
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Add-ons</h2>
                <p
                    v-for="(line, i) in booking.addons"
                    :key="i"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >
                    {{ line.addon.name.en }} × {{ line.qty }} — RM {{ money(line.price_sen) }}
                </p>
            </div>

            <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                <h2 class="mb-2 font-medium text-gray-800 dark:text-gray-200">Status</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ booking.status }}</p>
                <p v-if="booking.approver" class="text-sm text-gray-600 dark:text-gray-400">
                    Approved by {{ booking.approver.name }}
                </p>
                <p v-if="booking.rejection_reason" class="text-sm text-red-600 dark:text-red-400">
                    {{ booking.rejection_reason }}
                </p>
            </div>
        </div>
    </AdminLayout>
</template>
