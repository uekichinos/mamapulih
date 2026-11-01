<script setup lang="ts">
import CustomerAppLayout from '@/Layouts/CustomerAppLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SectionCard from '@/Components/SectionCard.vue';
import TextInput from '@/Components/TextInput.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Booking {
    id: number;
    ref_no: string;
    recipient_label: string;
    status: string;
    edd: string;
    price_sen: number;
    variant: {
        birth_type: string;
        duration_days: number;
        package: { name: Record<string, string> };
    };
    balance_due: number;
    can_cancel: boolean;
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
    bookings: Booking[];
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

const cancellingId = ref<number | null>(null);
const cancelForm = useForm({ reason: '' });

const cancel = (bookingId: number) => {
    if (!confirm('Cancel this booking? This cannot be undone.')) {
        return;
    }

    cancelForm.post(route('customer.my-booking.cancel', bookingId), {
        onSuccess: () => (cancellingId.value = null),
    });
};
</script>

<template>
    <CustomerAppLayout>
        <Head title="Booking" />

        <PageHeader title="Booking" />

        <div
            v-if="!bookings.length"
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

        <div v-else class="space-y-4">
            <SectionCard
                v-for="booking in bookings"
                :key="booking.id"
                :title="booking.recipient_label"
                class="space-y-3 text-sm"
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
                    <span class="text-gray-900 dark:text-gray-100">{{
                        formatDate(booking.edd)
                    }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Price</span>
                    <span class="text-gray-900 dark:text-gray-100"
                        >RM {{ money(booking.price_sen) }}</span
                    >
                </div>
                <div v-if="booking.balance_due" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Balance due</span>
                    <span class="text-gray-900 dark:text-gray-100"
                        >RM {{ money(booking.balance_due) }}</span
                    >
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        v-if="booking.status === 'awaiting_payment'"
                        :href="route('customer.payment')"
                    >
                        <PrimaryButton>Pay now</PrimaryButton>
                    </Link>
                    <Link
                        v-else-if="booking.status === 'reserved' && booking.balance_due"
                        :href="route('customer.payment')"
                    >
                        <PrimaryButton>Pay balance</PrimaryButton>
                    </Link>
                    <Link
                        v-else-if="booking.status === 'reserved'"
                        :href="route('customer.start', booking.id)"
                    >
                        <PrimaryButton>Start my package</PrimaryButton>
                    </Link>
                    <Link
                        v-else-if="booking.status === 'active'"
                        :href="route('customer.my-booking.schedule', booking.id)"
                    >
                        <PrimaryButton>View schedule</PrimaryButton>
                    </Link>

                    <SecondaryButton
                        v-if="booking.can_cancel && cancellingId !== booking.id"
                        type="button"
                        @click="cancellingId = booking.id"
                    >
                        Cancel booking
                    </SecondaryButton>
                </div>

                <div
                    v-if="booking.can_cancel && cancellingId === booking.id"
                    class="space-y-3 border-t border-gray-100 pt-3 dark:border-gray-800"
                >
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        No payment has been made yet, so this booking can be cancelled with nothing
                        to refund.
                    </p>
                    <TextInput
                        v-model="cancelForm.reason"
                        type="text"
                        placeholder="Reason (optional)"
                        class="w-full"
                    />
                    <div class="flex items-center gap-3">
                        <DangerButton :disabled="cancelForm.processing" @click="cancel(booking.id)">
                            Confirm cancellation
                        </DangerButton>
                        <SecondaryButton type="button" @click="cancellingId = null"
                            >Keep booking</SecondaryButton
                        >
                    </div>
                </div>
            </SectionCard>
        </div>

        <div v-if="archivedBookings.length" class="mt-8">
            <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">
                Archives
            </h2>
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
    </CustomerAppLayout>
</template>
