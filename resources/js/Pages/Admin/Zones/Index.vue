<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RowActions from '@/Components/RowActions.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    zones: {
        data: Array<{
            id: number;
            name: string;
            postcodes: string[];
            free_delivery: boolean;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <AdminLayout>
        <Head title="Zones" />

        <div class="mb-6 flex items-center justify-between">
            <h1 class="font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">Delivery zones</h1>
            <Link :href="route('admin.zones.create')">
                <PrimaryButton>New zone</PrimaryButton>
            </Link>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-100 text-xs font-bold uppercase tracking-wide text-gray-400 dark:border-gray-800 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Postcodes</th>
                        <th class="px-4 py-3">Delivery</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="zone in zones.data" :key="zone.id">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ zone.name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ zone.postcodes.join(', ') }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ zone.free_delivery ? 'Free' : 'Paid' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <RowActions>
                                <DropdownLink :href="route('admin.zones.edit', zone.id)">Edit</DropdownLink>
                            </RowActions>
                        </td>
                    </tr>
                    <tr v-if="zones.data.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-600">
                            No zones yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="zones.links" />
    </AdminLayout>
</template>
