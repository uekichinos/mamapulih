<script setup lang="ts">
import PageHeader from '@/Components/PageHeader.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Row {
    dish: { name: { en: string; ms: string } };
    count: number;
    substituted: number;
    bookings: { id: number; ref_no: string; customer_name: string }[];
}

const props = defineProps<{
    date: string;
    meals: Record<string, Record<string, Row>>;
}>();

const changeDate = (event: Event) => {
    router.get(route('admin.kitchen.prep-sheet'), {
        date: (event.target as HTMLInputElement).value,
    });
};

const mealRuns = [
    {
        key: 'lunch',
        label: 'Lunch',
        tint: 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
        icon: 'M12 3v1.5M12 19.5V21M4.2 4.2l1.1 1.1M18.7 18.7l1.1 1.1M3 12h1.5M19.5 12H21M4.2 19.8l1.1-1.1M18.7 5.3l1.1-1.1M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z',
    },
    {
        key: 'dinner',
        label: 'Dinner',
        tint: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300',
        icon: 'M20 14.5A8.5 8.5 0 1 1 9.5 4a7 7 0 0 0 10.5 10.5Z',
    },
] as const;

const rowsFor = (meal: string): Row[] => Object.values(props.meals[meal] ?? {});

const totalPortions = computed(() =>
    mealRuns.reduce((sum, meal) => sum + rowsFor(meal.key).reduce((s, row) => s + row.count, 0), 0),
);
const totalSubstitutions = computed(() =>
    mealRuns.reduce(
        (sum, meal) => sum + rowsFor(meal.key).reduce((s, row) => s + row.substituted, 0),
        0,
    ),
);
const totalDishes = computed(() =>
    mealRuns.reduce((sum, meal) => sum + rowsFor(meal.key).length, 0),
);

const pluralize = (count: number, word: string) => `${count} ${word}${count === 1 ? '' : 's'}`;

const printPage = () => window.print();
</script>

<template>
    <AdminLayout>
        <Head title="Kitchen prep sheet" />

        <PageHeader title="Prep sheet" class="print:hidden">
            Portions to cook, by dish and meal run.
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
                        <path d="M4 19h16M6 19V9m4 10V5m4 14v-7m4 7V3" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                        Total portions
                    </p>
                    <p
                        class="font-mono text-lg font-semibold tabular-nums text-gray-900 dark:text-gray-50"
                    >
                        {{ totalPortions }}
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
                        <path d="M4 7h16M4 12h10M4 17h7" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                        Distinct dishes
                    </p>
                    <p
                        class="font-mono text-lg font-semibold tabular-nums text-gray-900 dark:text-gray-50"
                    >
                        {{ totalDishes }}
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
                        <path d="M7 7h.01M12 3a9 9 0 1 0 9 9 9 9 0 0 0-9-9Z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                        Exclusion swaps
                    </p>
                    <p
                        class="font-mono text-lg font-semibold tabular-nums text-gray-900 dark:text-gray-50"
                    >
                        {{ totalSubstitutions }}
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div
                v-for="meal in mealRuns"
                :key="meal.key"
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm print:break-inside-avoid print:shadow-none dark:border-gray-800 dark:bg-gray-900"
            >
                <div
                    class="flex items-center gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800"
                >
                    <span
                        class="flex size-8 items-center justify-center rounded-lg"
                        :class="meal.tint"
                    >
                        <svg
                            class="size-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path :d="meal.icon" />
                        </svg>
                    </span>
                    <h3 class="font-display text-sm font-bold text-gray-900 dark:text-gray-50">
                        {{ meal.label }}
                    </h3>
                    <span class="ml-auto text-xs font-semibold text-gray-500 dark:text-gray-400">
                        {{ pluralize(rowsFor(meal.key).length, 'dish') }}
                    </span>
                </div>

                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th
                                class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                            >
                                Dish
                            </th>
                            <th
                                class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-400"
                            >
                                Portions
                            </th>
                            <th
                                class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                            >
                                Substituted
                            </th>
                            <th
                                class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-gray-400"
                            >
                                Bookings
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr
                            v-for="(row, i) in rowsFor(meal.key)"
                            :key="i"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800/60"
                        >
                            <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-gray-100">
                                {{ row.dish.name.en }}
                            </td>
                            <td
                                class="px-5 py-3.5 text-right font-mono text-xs tabular-nums text-gray-900 dark:text-gray-100"
                            >
                                {{ row.count }}
                            </td>
                            <td class="px-5 py-3.5">
                                <StatusBadge v-if="row.substituted" tone="warning">
                                    {{ row.substituted }} swapped
                                </StatusBadge>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="flex flex-wrap gap-x-1.5 gap-y-1">
                                    <template
                                        v-for="(booking, bi) in row.bookings"
                                        :key="booking.id"
                                    >
                                        <Link
                                            :href="route('admin.bookings.show', booking.id)"
                                            class="text-xs text-indigo-600 hover:text-indigo-700 hover:underline dark:text-indigo-400"
                                        >
                                            {{ booking.customer_name }}
                                        </Link>
                                        <span
                                            v-if="bi < row.bookings.length - 1"
                                            class="text-xs text-gray-300 dark:text-gray-600"
                                            >,</span
                                        >
                                    </template>
                                </span>
                            </td>
                        </tr>
                        <tr v-if="rowsFor(meal.key).length === 0">
                            <td
                                colspan="4"
                                class="px-5 py-8 text-center text-sm text-gray-600 dark:text-gray-400"
                            >
                                Nothing scheduled for {{ meal.label.toLowerCase() }}.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
