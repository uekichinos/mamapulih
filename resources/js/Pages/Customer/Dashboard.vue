<script setup lang="ts">
import CustomerAppLayout from '@/Layouts/CustomerAppLayout.vue';
import AlertBanner from '@/Components/AlertBanner.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SectionCard from '@/Components/SectionCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Booking {
    id: number;
    ref_no: string;
    recipient_label: string;
    status: string;
    edd: string;
    price_sen: number;
    package_name: Record<string, string>;
    balance_due: number;
    pending_consent: boolean;
}

defineProps<{
    bookings: Booking[];
}>();

const page = usePage();
const customerName = computed(() => page.props.auth.customer?.name ?? '');

const money = (sen: number) => (sen / 100).toFixed(2);

const statusTones: Record<
    string,
    'accent' | 'success' | 'warning' | 'danger' | 'info' | 'neutral'
> = {
    waitlisted: 'neutral',
    pending_approval: 'warning',
    rejected: 'danger',
    awaiting_payment: 'info',
    expired: 'neutral',
    reserved: 'accent',
    active: 'success',
    paused: 'warning',
    completed: 'neutral',
    cancelled: 'danger',
};

const statusLabel = (status: string) =>
    status
        .split('_')
        .map((word) => word[0].toUpperCase() + word.slice(1))
        .join(' ');

const nextStep = (booking: Booking) => {
    if (booking.status === 'awaiting_payment') {
        return { label: 'Pay now', route: 'customer.payment', params: undefined };
    }
    if (booking.status === 'reserved' && booking.balance_due) {
        return { label: 'Pay balance', route: 'customer.payment', params: undefined };
    }
    if (booking.status === 'reserved') {
        return { label: 'Start my package', route: 'customer.start', params: booking.id };
    }
    if (booking.status === 'active') {
        return {
            label: 'View schedule',
            route: 'customer.my-booking.schedule',
            params: booking.id,
        };
    }

    return null;
};
</script>

<template>
    <CustomerAppLayout>
        <Head title="Dashboard" />

        <PageHeader :title="`Welcome back, ${customerName}`">
            An at-a-glance view of your bookings.
        </PageHeader>

        <div
            v-if="!bookings.length"
            class="rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">
                You don't have a booking yet.
            </p>
            <Link :href="route('customer.booking.build')">
                <PrimaryButton>Build a package</PrimaryButton>
            </Link>
        </div>

        <div v-else class="mb-6 space-y-6">
            <div v-for="booking in bookings" :key="booking.id">
                <h2 class="mb-2 font-display text-sm font-bold text-gray-900 dark:text-gray-50">
                    {{ booking.recipient_label }}
                </h2>

                <AlertBanner v-if="booking.pending_consent" tone="amber" class="mb-3">
                    <div class="flex items-start gap-3">
                        <span
                            class="flex size-7 shrink-0 items-center justify-center rounded-full bg-white text-amber-700 dark:bg-gray-900 dark:text-amber-300"
                        >
                            <svg
                                class="size-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="12" r="9" />
                                <line x1="12" y1="8" x2="12" y2="13" />
                                <circle cx="12" cy="16.3" r="0.6" fill="currentColor" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="mb-1 font-semibold">We're waiting on you</h3>
                            <p>A plan change needs your review — check your email for the link.</p>
                        </div>
                    </div>
                </AlertBanner>

                <div class="mb-3 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <Link
                        :href="route('customer.my-booking')"
                        class="flex flex-col gap-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
                    >
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400"
                            >Booking status</span
                        >
                        <StatusBadge :tone="statusTones[booking.status] ?? 'neutral'">{{
                            statusLabel(booking.status)
                        }}</StatusBadge>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{
                            booking.ref_no
                        }}</span>
                    </Link>

                    <Link
                        :href="route('customer.payment')"
                        class="flex flex-col gap-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
                    >
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400"
                            >Balance due</span
                        >
                        <span
                            v-if="booking.balance_due"
                            class="text-sm font-bold tabular-nums text-gray-900 dark:text-gray-50"
                            >RM {{ money(booking.balance_due) }}</span
                        >
                        <span
                            v-else
                            class="font-display text-sm font-bold text-gray-900 dark:text-gray-50"
                            >Fully paid</span
                        >
                    </Link>

                    <Link
                        v-if="nextStep(booking)"
                        :href="route(nextStep(booking)!.route, nextStep(booking)!.params)"
                        class="flex flex-col justify-between gap-2 rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm transition hover:shadow-md dark:border-indigo-900 dark:bg-indigo-950"
                    >
                        <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300"
                            >Next step</span
                        >
                        <span
                            class="font-display text-sm font-bold text-indigo-900 dark:text-indigo-100"
                            >{{ nextStep(booking)!.label }} →</span
                        >
                    </Link>
                </div>

                <SectionCard :title="booking.package_name.en">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Due date: {{ formatDate(booking.edd) }}
                    </p>
                    <p class="text-sm text-gray-900 dark:text-gray-100">
                        RM {{ money(booking.price_sen) }}
                    </p>
                </SectionCard>
            </div>
        </div>
    </CustomerAppLayout>
</template>
