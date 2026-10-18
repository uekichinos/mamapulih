<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    zones: Array<{
        id: number;
        name: string;
        postcodes: string[];
        free_delivery: boolean;
    }>;
}>();
</script>

<template>
    <AdminLayout>
        <Head title="Zones" />

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Delivery zones</h1>
            <Link :href="route('admin.zones.create')">
                <PrimaryButton>New zone</PrimaryButton>
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Postcodes</th>
                        <th class="px-4 py-3">Delivery</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="zone in zones" :key="zone.id">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ zone.name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ zone.postcodes.join(', ') }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ zone.free_delivery ? 'Free' : 'Paid' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link
                                :href="route('admin.zones.edit', zone.id)"
                                class="text-indigo-600 hover:text-indigo-700"
                            >
                                Edit
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="zones.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                            No zones yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
