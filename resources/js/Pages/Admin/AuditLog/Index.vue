<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

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

// Laravel's paginator labels only ever contain a couple of known HTML
// entities (never user input), so decode just those rather than using
// v-html, which isn't safe to bind on a component like <Link>.
const decodeLabel = (label: string) =>
    label
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&hellip;/g, '…');
</script>

<template>
    <AdminLayout>
        <Head title="Audit log" />

        <h1 class="mb-6 text-2xl font-semibold text-gray-900 dark:text-gray-100">Audit log</h1>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">When</th>
                        <th class="px-4 py-3">Who</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Subject</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="log in logs.data" :key="log.id">
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ log.created_at }}
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
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                            No entries yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="logs.links.length > 3" class="mt-4 flex flex-wrap gap-1">
            <Link
                v-for="(link, index) in logs.links"
                :key="index"
                :href="link.url ?? ''"
                class="rounded px-3 py-1 text-sm"
                :class="
                    link.active
                        ? 'bg-indigo-600 text-white'
                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'
                "
            >
                {{ decodeLabel(link.label) }}
            </Link>
        </div>
    </AdminLayout>
</template>
