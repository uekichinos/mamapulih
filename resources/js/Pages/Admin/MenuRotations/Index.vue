<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    menuRotations: Array<{ id: number; name: { en: string; ms: string } }>;
}>();
</script>

<template>
    <AdminLayout>
        <Head title="Menu rotations" />

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Menu rotations</h1>
            <Link :href="route('admin.menu-rotations.create')">
                <PrimaryButton>New rotation</PrimaryButton>
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="rotation in menuRotations" :key="rotation.id">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ rotation.name.en }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.menu-rotations.edit', rotation.id)" class="text-indigo-600 hover:text-indigo-700">
                                Edit
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="menuRotations.length === 0">
                        <td colspan="2" class="px-4 py-6 text-center text-gray-500">No menu rotations yet.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
