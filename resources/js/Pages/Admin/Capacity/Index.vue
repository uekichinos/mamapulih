<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
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
</script>

<template>
    <AdminLayout>
        <Head title="Capacity" />

        <h1 class="mb-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">Capacity</h1>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            Regular capacity {{ regularCapacity }} / day, buffer {{ bufferCapacity }}. Closure days
            are not shown (no deliveries).
        </p>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Slots used</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="day in days" :key="day.date">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ day.date }}</td>
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
