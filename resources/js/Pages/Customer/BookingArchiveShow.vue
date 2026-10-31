<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { formatDate } from '@/lib/formatDate';
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

const props = defineProps<{
    booking: Booking;
    amountPaid: number;
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
        <Head title="Booking archive" />

        <div class="mb-4 flex items-center justify-between">
            <h1 class="font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
                Archived booking
            </h1>
            <Link :href="route('customer.my-booking')" class="text-sm text-indigo-600 underline">
                Back to My Booking
            </Link>
        </div>

        <div
            class="space-y-3 rounded-2xl border border-gray-200 bg-white shadow-sm p-4 text-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Reference</span>
                <span class="font-medium text-gray-900 dark:text-gray-50">{{
                    props.booking.ref_no
                }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Status</span>
                <span class="font-medium text-gray-900 dark:text-gray-50">
                    {{ statusLabel[props.booking.status] ?? props.booking.status }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Package</span>
                <span class="font-medium text-gray-900 dark:text-gray-50">
                    {{ props.booking.variant.package.name.en }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Expected delivery date</span>
                <span class="font-medium text-gray-900 dark:text-gray-50">{{
                    formatDate(props.booking.edd)
                }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Price</span>
                <span class="font-medium text-gray-900 dark:text-gray-50"
                    >RM {{ money(props.booking.price_sen) }}</span
                >
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Amount paid</span>
                <span class="font-medium text-gray-900 dark:text-gray-50"
                    >RM {{ money(props.amountPaid) }}</span
                >
            </div>
            <div
                v-if="props.booking.rejection_reason"
                class="border-t border-gray-100 pt-3 dark:border-gray-800"
            >
                <span class="text-gray-600 dark:text-gray-400">Rejection reason</span>
                <p class="mt-1 text-gray-900 dark:text-gray-50">
                    {{ props.booking.rejection_reason }}
                </p>
            </div>
        </div>
    </CustomerLayout>
</template>
