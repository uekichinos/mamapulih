<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    checklist: {
        business_profile: boolean;
        email: boolean;
        zones: boolean;
        packages: boolean;
    };
}>();

const items = [
    { key: 'business_profile', label: 'Business profile', route: 'admin.business-profile.edit' },
    { key: 'email', label: 'Email (SMTP) settings', route: 'admin.email-settings.edit' },
    { key: 'zones', label: 'Delivery zones', route: 'admin.zones.index' },
    { key: 'packages', label: 'Packages & variants', route: 'admin.packages.index' },
] as const;
</script>

<template>
    <AdminLayout>
        <Head title="Admin dashboard" />

        <h1 class="mb-6 text-2xl font-semibold text-gray-900 dark:text-gray-100">Dashboard</h1>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Setup checklist
                </h2>
            </div>

            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                <li
                    v-for="item in items"
                    :key="item.key"
                    class="flex items-center justify-between px-4 py-3"
                >
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ item.label }}</span>

                    <Link
                        :href="route(item.route)"
                        class="text-sm font-medium"
                        :class="
                            checklist[item.key]
                                ? 'text-green-600 hover:text-green-700'
                                : 'text-indigo-600 hover:text-indigo-700'
                        "
                    >
                        {{ checklist[item.key] ? '✓ Done — edit' : 'Set up →' }}
                    </Link>
                </li>
            </ul>
        </div>
    </AdminLayout>
</template>
