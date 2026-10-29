<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RowActions from '@/Components/RowActions.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    packages: {
        data: Array<{
            id: number;
            name: { en: string; ms: string };
            active: boolean;
            variants_count: number;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <AdminLayout>
        <Head title="Packages" />

        <div class="mb-6 flex items-center justify-between">
            <h1 class="font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">Packages</h1>
            <Link :href="route('admin.packages.create')">
                <PrimaryButton>New package</PrimaryButton>
            </Link>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-100 text-xs font-bold uppercase tracking-wide text-gray-400 dark:border-gray-800 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Variants</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="pkg in packages.data" :key="pkg.id">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                            {{ pkg.name.en }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ pkg.variants_count }}
                        </td>
                        <td class="px-4 py-3">
                            <span :class="pkg.active ? 'text-green-600' : 'text-gray-400'">
                                {{ pkg.active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <RowActions>
                                <DropdownLink :href="route('admin.packages.edit', pkg.id)">Edit</DropdownLink>
                            </RowActions>
                        </td>
                    </tr>
                    <tr v-if="packages.data.length === 0">
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                            No packages yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="packages.links" />
    </AdminLayout>
</template>
