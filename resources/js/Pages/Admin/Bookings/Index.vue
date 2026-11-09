<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RowActions from '@/Components/RowActions.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link } from '@inertiajs/vue3';

interface BookingRow {
    id: number;
    ref_no: string;
    recipient_name: string | null;
    status: string;
    edd: string;
    price_sen: number;
    customer: { name: string };
    variant: { package: { name: Record<string, string> } };
}

defineProps<{
    bookings: {
        data: BookingRow[];
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    customerFilter: { id: number; name: string } | null;
}>();

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
</script>

<template>
    <AdminLayout>
        <Head title="Bookings" />

        <PageHeader title="Bookings">
            {{ bookings.total }} bookings
            <template #actions>
                <Link :href="route('admin.bookings.create')">
                    <PrimaryButton>
                        <svg
                            class="size-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                            aria-hidden="true"
                        >
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        New booking
                    </PrimaryButton>
                </Link>
            </template>
        </PageHeader>

        <div
            v-if="customerFilter"
            class="mb-4 flex items-center gap-2 rounded-lg bg-indigo-50 px-4 py-2.5 text-sm text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300"
        >
            <span
                >Showing bookings for <strong>{{ customerFilter.name }}</strong></span
            >
            <Link :href="route('admin.bookings.index')" class="font-semibold underline">
                Clear filter
            </Link>
        </div>

        <div
            class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th
                            class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                        >
                            Ref no.
                        </th>
                        <th
                            class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                        >
                            Customer
                        </th>
                        <th
                            class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                        >
                            Package
                        </th>
                        <th
                            class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                        >
                            EDD
                        </th>
                        <th
                            class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-400"
                        >
                            Price
                        </th>
                        <th
                            class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                        >
                            Status
                        </th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr
                        v-for="booking in bookings.data"
                        :key="booking.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-800/60"
                    >
                        <td
                            class="whitespace-nowrap px-5 py-3.5 text-xs text-gray-900 dark:text-gray-100"
                        >
                            {{ booking.ref_no }}
                        </td>
                        <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-gray-100">
                            {{ booking.customer.name }}
                            <p
                                v-if="
                                    booking.recipient_name &&
                                    booking.recipient_name !== booking.customer.name
                                "
                                class="text-xs font-normal text-gray-500 dark:text-gray-400"
                            >
                                for {{ booking.recipient_name }}
                            </p>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">
                            {{ booking.variant.package.name.en }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3.5 text-gray-600 dark:text-gray-400">
                            {{ formatDate(booking.edd) }}
                        </td>
                        <td
                            class="whitespace-nowrap px-5 py-3.5 text-right text-xs tabular-nums text-gray-900 dark:text-gray-100"
                        >
                            RM {{ money(booking.price_sen) }}
                        </td>
                        <td class="px-5 py-3.5">
                            <StatusBadge :tone="statusTones[booking.status] ?? 'neutral'">{{
                                statusLabel(booking.status)
                            }}</StatusBadge>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <RowActions>
                                <DropdownLink :href="route('admin.bookings.show', booking.id)"
                                    >View</DropdownLink
                                >
                            </RowActions>
                        </td>
                    </tr>
                    <tr v-if="bookings.data.length === 0">
                        <td
                            colspan="7"
                            class="px-5 py-8 text-center text-sm text-gray-600 dark:text-gray-400"
                        >
                            No bookings yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="bookings.links" />
    </AdminLayout>
</template>
