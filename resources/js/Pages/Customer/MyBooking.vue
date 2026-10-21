<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Booking {
    ref_no: string;
    status: string;
    edd: string;
    price_sen: number;
    rejection_reason?: string | null;
    variant: {
        birth_type: string;
        duration_days: number;
        package: { name: Record<string, string> };
    };
}

defineProps<{
    booking: Booking | null;
}>();

const statusLabel: Record<string, string> = {
    pending_approval: 'Pending approval',
    rejected: 'Rejected',
    awaiting_payment: 'Awaiting payment',
    expired: 'Expired',
    reserved: 'Reserved',
    active: 'Active',
    paused: 'Paused',
    completed: 'Completed',
    cancelled: 'Cancelled',
};

const money = (sen: number) => (sen / 100).toFixed(2);
</script>

<template>
    <CustomerLayout>
        <Head title="My Booking" />

        <h1 class="mb-4 text-xl font-semibold text-gray-900 dark:text-gray-100">My Booking</h1>

        <div
            v-if="!booking"
            class="rounded-lg border border-gray-200 p-6 text-center dark:border-gray-700"
        >
            <p class="text-gray-600 dark:text-gray-400">You don't have a booking yet.</p>
            <Link
                :href="route('customer.booking.build')"
                class="mt-2 inline-block text-indigo-600 underline"
            >
                Build a package
            </Link>
        </div>

        <div
            v-else
            class="space-y-3 rounded-lg border border-gray-200 p-4 text-sm dark:border-gray-700"
        >
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Reference</span>
                <span class="text-gray-900 dark:text-gray-100">{{ booking.ref_no }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Status</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                    {{ statusLabel[booking.status] ?? booking.status }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Package</span>
                <span class="text-gray-900 dark:text-gray-100">{{
                    booking.variant.package.name.en
                }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Due date</span>
                <span class="text-gray-900 dark:text-gray-100">{{ booking.edd }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Price</span>
                <span class="text-gray-900 dark:text-gray-100"
                    >RM {{ money(booking.price_sen) }}</span
                >
            </div>
            <div
                v-if="booking.status === 'rejected' && booking.rejection_reason"
                class="rounded-md bg-red-50 p-3 text-red-700 dark:bg-red-900/30 dark:text-red-300"
            >
                {{ booking.rejection_reason }}
            </div>
        </div>
    </CustomerLayout>
</template>
