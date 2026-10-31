<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { formatDate, formatDateTime } from '@/lib/formatDate';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface PaymentReceipt {
    id: number;
    original_name: string;
    created_at: string;
}

interface Booking {
    ref_no: string;
    status: string;
    edd: string;
    price_sen: number;
    variant: {
        birth_type: string;
        duration_days: number;
        package: { name: Record<string, string> };
    };
    payment_receipts: PaymentReceipt[];
}

interface ArchivedBooking {
    id: number;
    ref_no: string;
    status: string;
    edd: string;
    variant: {
        package: { name: Record<string, string> };
    };
}

defineProps<{
    booking: Booking | null;
    balanceDue: number | null;
    canCancel: boolean;
    archivedBookings: ArchivedBooking[];
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

const cancelling = ref(false);
const cancelForm = useForm({ reason: '' });

const cancel = () => {
    if (! confirm('Cancel this booking? This cannot be undone.')) {
        return;
    }

    cancelForm.post(route('customer.my-booking.cancel'));
};
</script>

<template>
    <CustomerLayout>
        <Head title="My Booking" />

        <h1 class="mb-4 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">My Booking</h1>

        <div
            v-if="!booking"
            class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6 text-center dark:border-gray-800 dark:bg-gray-900"
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
            class="space-y-3 rounded-2xl border border-gray-200 bg-white shadow-sm p-4 text-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Reference</span>
                <span class="text-gray-900 dark:text-gray-100">{{ booking.ref_no }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Status</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                    {{ statusLabel[booking.status] ?? booking.status }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Package</span>
                <span class="text-gray-900 dark:text-gray-100">{{
                    booking.variant.package.name.en
                }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Due date</span>
                <span class="text-gray-900 dark:text-gray-100">{{ formatDate(booking.edd) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Price</span>
                <span class="text-gray-900 dark:text-gray-100"
                    >RM {{ money(booking.price_sen) }}</span
                >
            </div>
            <div
                v-if="balanceDue"
                class="flex justify-between"
            >
                <span class="text-gray-600 dark:text-gray-400">Balance due</span>
                <span class="text-gray-900 dark:text-gray-100">RM {{ money(balanceDue) }}</span>
            </div>

            <div v-if="booking.payment_receipts.length" class="border-t border-gray-100 pt-3 dark:border-gray-800">
                <p class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400">
                    Receipts you uploaded
                </p>
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    <li
                        v-for="receipt in booking.payment_receipts"
                        :key="receipt.id"
                        class="flex items-center justify-between py-1.5"
                    >
                        <span class="text-gray-700 dark:text-gray-300">{{ receipt.original_name }}</span>
                        <span class="flex items-center gap-3">
                            <span class="text-gray-600 dark:text-gray-400">{{ formatDateTime(receipt.created_at) }}</span>
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

            <div class="flex flex-wrap items-center gap-3">
                <Link v-if="booking.status === 'awaiting_payment'" :href="route('customer.payment')">
                    <PrimaryButton>Pay now</PrimaryButton>
                </Link>
                <Link v-else-if="booking.status === 'reserved' && balanceDue" :href="route('customer.payment')">
                    <PrimaryButton>Pay balance</PrimaryButton>
                </Link>
                <Link v-else-if="booking.status === 'reserved'" :href="route('customer.start')">
                    <PrimaryButton>Start my package</PrimaryButton>
                </Link>
                <Link v-else-if="booking.status === 'active'" :href="route('customer.my-booking.schedule')">
                    <PrimaryButton>View schedule</PrimaryButton>
                </Link>

                <SecondaryButton v-if="canCancel && !cancelling" type="button" @click="cancelling = true">
                    Cancel booking
                </SecondaryButton>
            </div>

            <div v-if="canCancel && cancelling" class="space-y-3 border-t border-gray-100 pt-3 dark:border-gray-800">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    No payment has been made yet, so this booking can be cancelled with nothing to
                    refund.
                </p>
                <TextInput
                    v-model="cancelForm.reason"
                    type="text"
                    placeholder="Reason (optional)"
                    class="w-full"
                />
                <div class="flex items-center gap-3">
                    <DangerButton :disabled="cancelForm.processing" @click="cancel">
                        Confirm cancellation
                    </DangerButton>
                    <SecondaryButton type="button" @click="cancelling = false">Keep booking</SecondaryButton>
                </div>
            </div>
        </div>

        <div v-if="archivedBookings.length" class="mt-8">
            <h2 class="mb-3 font-display text-base font-extrabold text-gray-900 dark:text-gray-50">Archives</h2>
            <ul class="space-y-2">
                <li
                    v-for="archived in archivedBookings"
                    :key="archived.id"
                    class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <Link
                        :href="route('customer.my-booking.archive', archived.id)"
                        class="flex items-center justify-between p-4 text-sm"
                    >
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">
                                {{ archived.variant.package.name.en }}
                            </p>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ archived.ref_no }} &middot; {{ formatDate(archived.edd) }}
                            </p>
                        </div>
                        <span class="text-gray-900 dark:text-gray-100">
                            {{ statusLabel[archived.status] ?? archived.status }}
                        </span>
                    </Link>
                </li>
            </ul>
        </div>
    </CustomerLayout>
</template>
