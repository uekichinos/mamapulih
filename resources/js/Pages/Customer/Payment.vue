<script setup lang="ts">
import CustomerAppLayout from '@/Layouts/CustomerAppLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import SectionCard from '@/Components/SectionCard.vue';
import BookingPaymentPanel from '@/Pages/Customer/Payment/BookingPaymentPanel.vue';
import { formatDateTime } from '@/lib/formatDate';
import { Head } from '@inertiajs/vue3';

interface Receipt {
    id: number;
    original_name: string;
    created_at: string;
}

interface BookingReceipts {
    id: number;
    ref_no: string;
    recipient_label: string;
    receipts: Receipt[];
}

interface PayableBooking {
    id: number;
    ref_no: string;
    recipient_label: string;
    status: string;
    payment_option: string | null;
    full_amount_sen: number;
    deposit_amount_sen: number;
    balance_due_sen: number;
}

defineProps<{
    payableBookings: PayableBooking[];
    manual: {
        bank_name: string | null;
        account_name: string | null;
        account_number: string | null;
        qr_image_url: string | null;
        instructions: { en: string; ms: string };
    };
    receiptsByBooking: BookingReceipts[];
}>();
</script>

<template>
    <CustomerAppLayout>
        <Head title="Payment" />

        <PageHeader title="Payment" />

        <div
            v-if="!payableBookings.length"
            class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 text-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <p class="text-gray-600 dark:text-gray-400">
                There's nothing to pay right now. Your receipt history is below.
            </p>
        </div>

        <div v-else class="space-y-6">
            <div
                v-for="booking in payableBookings"
                :key="booking.id"
                class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 dark:border-gray-800 dark:bg-gray-900"
            >
                <BookingPaymentPanel :booking="booking" :manual="manual" />
            </div>
        </div>

        <SectionCard v-if="receiptsByBooking.length" title="Receipts" class="mt-4 text-sm">
            <div
                v-for="group in receiptsByBooking"
                :key="group.id"
                class="mt-3 border-t border-gray-100 pt-3 first:mt-0 first:border-t-0 first:pt-0 dark:border-gray-800"
            >
                <p
                    class="mb-1 text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
                >
                    {{ group.recipient_label }} — {{ group.ref_no }}
                </p>
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    <li
                        v-for="receipt in group.receipts"
                        :key="receipt.id"
                        class="flex items-center justify-between py-1.5"
                    >
                        <span class="text-gray-700 dark:text-gray-300">{{
                            receipt.original_name
                        }}</span>
                        <span class="flex items-center gap-3">
                            <span class="text-gray-600 dark:text-gray-400">{{
                                formatDateTime(receipt.created_at)
                            }}</span>
                            <a
                                :href="route('customer.my-booking.receipts.view', receipt.id)"
                                target="_blank"
                                class="font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                            >
                                View
                            </a>
                        </span>
                    </li>
                </ul>
            </div>
        </SectionCard>
    </CustomerAppLayout>
</template>
