<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    checklist: {
        business_profile: boolean;
        email: boolean;
        zones: boolean;
        packages: boolean;
    };
    stats: {
        active_mothers: number;
        todays_deliveries: number;
        awaiting_approval: number;
        pending_payments: number;
    };
    bufferAlerts: { date: string; slots: number }[];
}>();

const items = [
    { key: 'business_profile', label: 'Business profile', route: 'admin.business-profile.edit' },
    { key: 'email', label: 'Email (SMTP) settings', route: 'admin.email-settings.edit' },
    { key: 'zones', label: 'Delivery zones', route: 'admin.zones.index' },
    { key: 'packages', label: 'Packages & variants', route: 'admin.packages.index' },
] as const;

const tiles = [
    { key: 'active_mothers', label: 'Active mothers', route: 'admin.bookings.index', tint: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' },
    { key: 'todays_deliveries', label: "Today's deliveries", route: 'admin.delivery-lists.index', tint: 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' },
    { key: 'awaiting_approval', label: 'Awaiting approval', route: 'admin.booking-approvals.index', tint: 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300' },
    { key: 'pending_payments', label: 'Pending payments', route: 'admin.bookings.index', tint: 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300' },
] as const;
</script>

<template>
    <AdminLayout>
        <Head title="Admin dashboard" />

        <h1 class="mb-1 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">Dashboard</h1>
        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">An at-a-glance view of today's operations.</p>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Link
                v-for="tile in tiles"
                :key="tile.key"
                :href="route(tile.route)"
                class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ tile.label }}</span>
                    <span class="flex size-8 items-center justify-center rounded-lg" :class="tile.tint">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                        </svg>
                    </span>
                </div>
                <span class="font-mono text-2xl font-semibold tabular-nums text-gray-900 dark:text-gray-50">{{ stats[tile.key] }}</span>
            </Link>
        </div>

        <div
            v-if="bufferAlerts.length"
            class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950"
        >
            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-white text-amber-700 dark:bg-gray-900 dark:text-amber-300">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" /><line x1="12" y1="8" x2="12" y2="13" /><circle cx="12" cy="16.3" r="0.6" fill="currentColor" />
                </svg>
            </span>
            <div>
                <h2 class="mb-1 text-sm font-semibold text-amber-800 dark:text-amber-200">
                    Buffer capacity in use — next 7 days
                </h2>
                <ul class="space-y-0.5 text-sm tabular-nums text-amber-700 dark:text-amber-300">
                    <li v-for="alert in bufferAlerts" :key="alert.date">
                        {{ formatDate(alert.date) }} — {{ alert.slots }} slots booked
                    </li>
                </ul>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="font-display text-sm font-bold text-gray-900 dark:text-gray-50">
                    Setup checklist
                </h2>
            </div>

            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                <li
                    v-for="item in items"
                    :key="item.key"
                    class="flex items-center justify-between px-5 py-3.5"
                >
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ item.label }}</span>

                    <Link
                        v-if="checklist[item.key]"
                        :href="route(item.route)"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-700 hover:text-green-800 dark:text-green-400"
                    >
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                            <polyline points="4,12 9,18 20,6" />
                        </svg>
                        Done
                    </Link>
                    <Link
                        v-else
                        :href="route(item.route)"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                    >
                        Set up →
                    </Link>
                </li>
            </ul>
        </div>
    </AdminLayout>
</template>
