<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface DietProfile {
    allergies: string[];
    exclusions: string[];
    notes: string | null;
}

interface BookingRow {
    id: number;
    ref_no: string;
    recipient_name: string | null;
    status: string;
    edd: string;
    price_sen: number;
    variant: { package: { name: Record<string, string> } };
    diet_profile: DietProfile | null;
}

interface AddressRow {
    id: number;
    line1: string;
    line2: string | null;
    postcode: string;
    notes: string | null;
    zone: { name: string };
}

const props = defineProps<{
    customer: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        do_not_contact: boolean;
        created_at: string;
        addresses: AddressRow[];
        bookings: BookingRow[];
    };
}>();

const bookingsWithDietProfile = computed(() =>
    props.customer.bookings.filter((booking) => booking.diet_profile !== null),
);

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

const tagLabel = (tag: string) =>
    tag
        .split('_')
        .map((word) => word[0].toUpperCase() + word.slice(1))
        .join(' ');
</script>

<template>
    <AdminLayout>
        <Head :title="customer.name" />

        <PageHeader :title="customer.name">
            Customer since {{ formatDate(customer.created_at) }}
            <template #actions>
                <Link
                    :href="route('admin.bookings.index', { customer_id: customer.id })"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                >
                    All bookings →
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-1">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">
                        Contact
                    </h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ customer.email }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500 dark:text-gray-400">Phone</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                {{ customer.phone ?? '—' }}
                            </dd>
                        </div>
                    </dl>

                    <div
                        v-if="customer.do_not_contact"
                        class="mt-4 flex items-center gap-2 rounded-lg bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 dark:bg-red-950 dark:text-red-300"
                    >
                        <svg
                            class="size-4 shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="9" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                        Do not contact
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">
                        Address
                    </h2>
                    <template v-if="customer.addresses.length">
                        <div
                            v-for="(address, i) in customer.addresses"
                            :key="address.id"
                            :class="{
                                'mt-3 border-t border-gray-100 pt-3 dark:border-gray-800': i > 0,
                            }"
                        >
                            <p
                                v-if="customer.addresses.length > 1"
                                class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400"
                            >
                                {{ i === 0 ? 'Most recent' : 'Earlier address' }}
                            </p>
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                {{ address.line1
                                }}<span v-if="address.line2">, {{ address.line2 }}</span
                                >, {{ address.postcode }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Zone: {{ address.zone.name }}
                            </p>
                            <p
                                v-if="address.notes"
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ address.notes }}
                            </p>
                        </div>
                    </template>
                    <p v-else class="text-sm text-gray-600 dark:text-gray-400">
                        No address recorded yet.
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <h2 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-gray-50">
                        Diet profile
                    </h2>
                    <template v-if="bookingsWithDietProfile.length">
                        <div
                            v-for="(booking, i) in bookingsWithDietProfile"
                            :key="booking.id"
                            :class="{
                                'mt-3 border-t border-gray-100 pt-3 dark:border-gray-800': i > 0,
                            }"
                        >
                            <p
                                v-if="bookingsWithDietProfile.length > 1"
                                class="mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400"
                            >
                                {{ i === 0 ? 'Most recent' : 'Earlier' }} ·
                                {{ booking.recipient_name ?? booking.ref_no }}
                            </p>
                            <div v-if="booking.diet_profile!.allergies.length" class="mb-3">
                                <p
                                    class="mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400"
                                >
                                    Allergies
                                </p>
                                <div class="flex flex-wrap gap-1.5">
                                    <StatusBadge
                                        v-for="tag in booking.diet_profile!.allergies"
                                        :key="tag"
                                        tone="danger"
                                    >
                                        {{ tagLabel(tag) }}
                                    </StatusBadge>
                                </div>
                            </div>
                            <div v-if="booking.diet_profile!.exclusions.length" class="mb-3">
                                <p
                                    class="mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400"
                                >
                                    Exclusions
                                </p>
                                <div class="flex flex-wrap gap-1.5">
                                    <StatusBadge
                                        v-for="tag in booking.diet_profile!.exclusions"
                                        :key="tag"
                                        tone="warning"
                                    >
                                        {{ tagLabel(tag) }}
                                    </StatusBadge>
                                </div>
                            </div>
                            <p
                                v-if="booking.diet_profile!.notes"
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ booking.diet_profile!.notes }}
                            </p>
                            <p
                                v-if="
                                    !booking.diet_profile!.allergies.length &&
                                    !booking.diet_profile!.exclusions.length &&
                                    !booking.diet_profile!.notes
                                "
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                No allergies or exclusions recorded.
                            </p>
                        </div>
                    </template>
                    <p v-else class="text-sm text-gray-600 dark:text-gray-400">
                        No diet profile recorded yet.
                    </p>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm lg:col-span-2 dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h2 class="font-display text-sm font-bold text-gray-900 dark:text-gray-50">
                        Bookings
                    </h2>
                </div>
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr
                            v-for="booking in customer.bookings"
                            :key="booking.id"
                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/60"
                            @click="router.visit(route('admin.bookings.show', booking.id))"
                        >
                            <td
                                class="whitespace-nowrap px-5 py-3.5 text-xs text-gray-900 dark:text-gray-100"
                            >
                                {{ booking.ref_no }}
                                <p
                                    v-if="booking.recipient_name"
                                    class="font-sans text-xs font-normal text-gray-500 dark:text-gray-400"
                                >
                                    for {{ booking.recipient_name }}
                                </p>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">
                                {{ booking.variant.package.name.en }}
                            </td>
                            <td
                                class="whitespace-nowrap px-5 py-3.5 text-gray-600 dark:text-gray-400"
                            >
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
                        </tr>
                        <tr v-if="customer.bookings.length === 0">
                            <td
                                colspan="5"
                                class="px-5 py-8 text-center text-sm text-gray-600 dark:text-gray-400"
                            >
                                No bookings yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
