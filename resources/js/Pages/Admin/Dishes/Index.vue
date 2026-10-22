<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    dishes: Array<{
        id: number;
        name: { en: string; ms: string };
        tags: string[];
        active: boolean;
    }>;
}>();
</script>

<template>
    <AdminLayout>
        <Head title="Dishes" />

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Dishes</h1>
            <Link :href="route('admin.dishes.create')">
                <PrimaryButton>New dish</PrimaryButton>
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Tags</th>
                        <th class="px-4 py-3">Active</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="dish in dishes" :key="dish.id">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ dish.name.en }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ dish.tags.join(', ') }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ dish.active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.dishes.edit', dish.id)" class="text-indigo-600 hover:text-indigo-700">
                                Edit
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="dishes.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">No dishes yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
