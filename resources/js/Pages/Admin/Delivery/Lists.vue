<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Row {
    booking_id: number;
    name: string;
    address: string;
    phone: string | null;
    notes: string | null;
    one_trip: boolean;
}

const props = defineProps<{
    date: string;
    runs: Record<string, Record<string, Row[]>>;
}>();

const changeDate = (event: Event) => {
    router.get(route('admin.delivery-lists.index'), {
        date: (event.target as HTMLInputElement).value,
    });
};

const deliveryRuns = [
    {
        key: 'lunch',
        label: 'Lunch run',
        tint: 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
        icon: 'M12 3v1.5M12 19.5V21M4.2 4.2l1.1 1.1M18.7 18.7l1.1 1.1M3 12h1.5M19.5 12H21M4.2 19.8l1.1-1.1M18.7 5.3l1.1-1.1M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z',
    },
    {
        key: 'dinner',
        label: 'Dinner run',
        tint: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300',
        icon: 'M20 14.5A8.5 8.5 0 1 1 9.5 4a7 7 0 0 0 10.5 10.5Z',
    },
] as const;

const zonesFor = (run: string): [string, Row[]][] => Object.entries(props.runs[run] ?? {});
const stopsFor = (run: string): number =>
    zonesFor(run).reduce((sum, [, rows]) => sum + rows.length, 0);

const totalStops = computed(() => deliveryRuns.reduce((sum, run) => sum + stopsFor(run.key), 0));
const totalZones = computed(() =>
    deliveryRuns.reduce((sum, run) => sum + zonesFor(run.key).length, 0),
);
const oneTripStops = computed(() =>
    deliveryRuns.reduce(
        (sum, run) =>
            sum +
            zonesFor(run.key).reduce(
                (s, [, rows]) => s + rows.filter((row) => row.one_trip).length,
                0,
            ),
        0,
    ),
);

const pluralize = (count: number, word: string) => `${count} ${word}${count === 1 ? '' : 's'}`;

const printPage = () => window.print();
</script>

<template>
    <AdminLayout>
        <Head title="Delivery lists" />

        <PageHeader title="Delivery lists" class="print:hidden">
            Stops to deliver, grouped by run and zone.
            <template #actions>
                <div class="flex items-center gap-3">
                    <input
                        type="date"
                        :value="date"
                        class="rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        @change="changeDate"
                    />
                    <SecondaryButton @click="printPage">
                        <svg
                            class="size-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path
                                d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1h-2"
                            />
                            <rect x="6" y="14" width="12" height="7" />
                        </svg>
                        Print
                    </SecondaryButton>
                </div>
            </template>
        </PageHeader>

        <h2 class="mb-4 font-display text-sm font-bold text-gray-900 dark:text-gray-50 print:mb-2">
            {{ formatDate(date) }}
        </h2>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3 print:hidden">
            <div
                class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300"
                >
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M3 17h2l1-6h11l2 6h2M6 11V7a1 1 0 0 1 1-1h7l3 5" />
                        <circle cx="7.5" cy="17.5" r="1.5" />
                        <circle cx="17.5" cy="17.5" r="1.5" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                        Total stops
                    </p>
                    <p class="text-lg font-semibold tabular-nums text-gray-900 dark:text-gray-50">
                        {{ totalStops }}
                    </p>
                </div>
            </div>
            <div
                class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                >
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M12 21s-7-5.4-7-11a7 7 0 0 1 14 0c0 5.6-7 11-7 11Z" />
                        <circle cx="12" cy="10" r="2.3" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                        Zones covered
                    </p>
                    <p class="text-lg font-semibold tabular-nums text-gray-900 dark:text-gray-50">
                        {{ totalZones }}
                    </p>
                </div>
            </div>
            <div
                class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                >
                    <svg
                        class="size-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M12 7v5l3 3" />
                        <circle cx="12" cy="12" r="9" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                        One-trip (+dinner)
                    </p>
                    <p class="text-lg font-semibold tabular-nums text-gray-900 dark:text-gray-50">
                        {{ oneTripStops }}
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div
                v-for="run in deliveryRuns"
                :key="run.key"
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm print:break-inside-avoid print:shadow-none dark:border-gray-800 dark:bg-gray-900"
            >
                <div
                    class="flex items-center gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800"
                >
                    <span
                        class="flex size-8 items-center justify-center rounded-lg"
                        :class="run.tint"
                    >
                        <svg
                            class="size-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path :d="run.icon" />
                        </svg>
                    </span>
                    <h3 class="font-display text-sm font-bold text-gray-900 dark:text-gray-50">
                        {{ run.label }}
                    </h3>
                    <span class="ml-auto text-xs font-semibold text-gray-500 dark:text-gray-400">
                        {{ pluralize(stopsFor(run.key), 'stop') }}
                    </span>
                </div>

                <div
                    v-if="zonesFor(run.key).length"
                    class="divide-y divide-gray-100 dark:divide-gray-800"
                >
                    <div v-for="[zone, rows] in zonesFor(run.key)" :key="zone">
                        <div
                            class="flex items-center gap-2 bg-gray-50 px-5 py-2.5 dark:bg-gray-800/50"
                        >
                            <h4
                                class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                {{ zone }}
                            </h4>
                            <span class="text-xs text-gray-400 dark:text-gray-500"
                                >· {{ pluralize(rows.length, 'stop') }}</span
                            >
                        </div>
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-gray-100 dark:border-gray-800">
                                <tr>
                                    <th
                                        class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                                    >
                                        Name
                                    </th>
                                    <th
                                        class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                                    >
                                        Address
                                    </th>
                                    <th
                                        class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                                    >
                                        Phone
                                    </th>
                                    <th
                                        class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                                    >
                                        Notes
                                    </th>
                                    <th
                                        class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                                    >
                                        Dinner
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr
                                    v-for="(row, i) in rows"
                                    :key="i"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800/60"
                                >
                                    <td class="px-5 py-3.5 font-medium">
                                        <Link
                                            :href="route('admin.bookings.show', row.booking_id)"
                                            class="text-indigo-600 hover:text-indigo-700 hover:underline dark:text-indigo-400"
                                        >
                                            {{ row.name }}
                                        </Link>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">
                                        {{ row.address }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-5 py-3.5 text-xs text-gray-600 dark:text-gray-400"
                                    >
                                        {{ row.phone ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">
                                        {{ row.notes ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <StatusBadge v-if="row.one_trip" tone="info"
                                            >+Dinner</StatusBadge
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <p v-else class="px-5 py-8 text-center text-sm text-gray-600 dark:text-gray-400">
                    Nothing scheduled for this run.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>
