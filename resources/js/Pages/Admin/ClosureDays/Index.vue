<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DropdownButton from '@/Components/DropdownButton.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RowActions from '@/Components/RowActions.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps<{
    closureDays: {
        data: Array<{
            id: number;
            date: string;
            reason: string | null;
            affected_count: number;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

const destroy = (id: number) => {
    if (confirm('Remove this closure day?')) {
        router.delete(route('admin.closure-days.destroy', id));
    }
};

const notify = (id: number) => router.post(route('admin.closure-days.notify', id));
</script>

<template>
    <AdminLayout>
        <Head title="Closure days" />

        <div class="mb-6 flex items-center justify-between">
            <h1 class="font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">Closure days</h1>
            <Link :href="route('admin.closure-days.create')">
                <PrimaryButton>New closure day</PrimaryButton>
            </Link>
        </div>

        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Full-day closures only in this version. No deliveries on these dates; active packages
            shift their end date out by one day per closure.
        </p>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-100 text-xs font-bold uppercase tracking-wide text-gray-400 dark:border-gray-800 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Reason</th>
                        <th class="px-4 py-3">Affected bookings</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="day in closureDays.data" :key="day.id">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ formatDate(day.date) }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ day.reason ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ day.affected_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <RowActions>
                                <DropdownButton v-if="day.affected_count > 0" @click="notify(day.id)">
                                    Notify customers
                                </DropdownButton>
                                <DropdownButton class="text-red-600 dark:text-red-400" @click="destroy(day.id)">
                                    Remove
                                </DropdownButton>
                            </RowActions>
                        </td>
                    </tr>
                    <tr v-if="closureDays.data.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-600">
                            No upcoming closure days.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="closureDays.links" />
    </AdminLayout>
</template>
