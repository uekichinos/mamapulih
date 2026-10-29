<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    from: string;
    to: string;
    regularCapacity: number;
    bufferCapacity: number;
    days: { date: string; slots: number; status: 'regular' | 'buffer' | 'full' }[];
}>();

const statusClass: Record<string, string> = {
    regular: 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
    buffer: 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    full: 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
};

const shiftedFrom = (days: number) => {
    const date = new Date(`${props.from}T00:00:00`);
    date.setDate(date.getDate() + days);

    return date.toISOString().slice(0, 10);
};

const previousWindowUrl = computed(() => route('admin.capacity.index', { from: shiftedFrom(-30) }));
const nextWindowUrl = computed(() => route('admin.capacity.index', { from: shiftedFrom(30) }));
const isCurrentWindow = computed(() => props.from === new Date().toISOString().slice(0, 10));
</script>

<template>
    <AdminLayout>
        <Head title="Capacity" />

        <h1 class="mb-2 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">Capacity</h1>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            Regular capacity {{ regularCapacity }} / day, buffer {{ bufferCapacity }}. Closure days
            are not shown (no deliveries).
        </p>

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <Link :href="previousWindowUrl">
                <SecondaryButton>← Previous 30 days</SecondaryButton>
            </Link>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ formatDate(from) }} – {{ formatDate(to) }}</span>
                <Link v-if="!isCurrentWindow" :href="route('admin.capacity.index')" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                    Jump to today
                </Link>
            </div>
            <Link :href="nextWindowUrl">
                <SecondaryButton>Next 30 days →</SecondaryButton>
            </Link>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-100 text-xs font-bold uppercase tracking-wide text-gray-400 dark:border-gray-800 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Slots used</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="day in days" :key="day.date">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ formatDate(day.date) }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ day.slots }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-1 text-xs"
                                :class="statusClass[day.status]"
                            >
                                {{ day.status }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
