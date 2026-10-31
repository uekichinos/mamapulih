<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { formatDateTime } from '@/lib/formatDate';
import { Head } from '@inertiajs/vue3';

interface AuditLogEntry {
    id: number;
    action: string;
    subject_type: string | null;
    subject_id: number | null;
    created_at: string;
    actor: { id: number; name: string } | null;
}

defineProps<{
    logs: {
        data: AuditLogEntry[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <AdminLayout>
        <Head title="Audit log" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">Audit log</h1>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-100 text-xs font-bold uppercase tracking-wide text-gray-400 dark:border-gray-800 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">When</th>
                        <th class="px-4 py-3">Who</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Subject</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="log in logs.data" :key="log.id">
                        <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ formatDateTime(log.created_at) }}
                        </td>
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                            {{ log.actor?.name ?? 'System' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ log.action }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            <span v-if="log.subject_type">
                                {{ log.subject_type.split('\\').pop() }} #{{ log.subject_id }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="logs.data.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-600">
                            No entries yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="logs.links" />
    </AdminLayout>
</template>
