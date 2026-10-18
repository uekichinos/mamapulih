<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps<{
    closureDays: Array<{
        id: number;
        date: string;
        reason: string | null;
    }>;
}>();

const destroy = (id: number) => {
    if (confirm('Remove this closure day?')) {
        router.delete(route('admin.closure-days.destroy', id));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Closure days" />

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Closure days</h1>
            <Link :href="route('admin.closure-days.create')">
                <PrimaryButton>New closure day</PrimaryButton>
            </Link>
        </div>

        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Full-day closures only in this version. No deliveries on these dates; active packages
            shift their end date out by one day per closure.
        </p>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Reason</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="day in closureDays" :key="day.id">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ day.date }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ day.reason ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <DangerButton @click="destroy(day.id)">Remove</DangerButton>
                        </td>
                    </tr>
                    <tr v-if="closureDays.length === 0">
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">
                            No upcoming closure days.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
