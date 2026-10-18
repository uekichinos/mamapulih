<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

defineProps<{
    staff: Array<{
        id: number;
        name: string;
        email: string;
        role: 'owner' | 'kitchen';
    }>;
}>();

const page = usePage();

const destroy = (id: number) => {
    if (confirm('Remove this staff account?')) {
        router.delete(route('admin.staff.destroy', id));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Staff" />

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Staff</h1>
            <Link :href="route('admin.staff.create')">
                <PrimaryButton>New staff account</PrimaryButton>
            </Link>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="member in staff" :key="member.id">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                            {{ member.name }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ member.email }}
                        </td>
                        <td class="px-4 py-3 capitalize text-gray-600 dark:text-gray-400">
                            {{ member.role }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link
                                :href="route('admin.staff.edit', member.id)"
                                class="mr-3 text-indigo-600 hover:text-indigo-700"
                            >
                                Edit
                            </Link>
                            <DangerButton
                                v-if="member.id !== page.props.auth.user.id"
                                @click="destroy(member.id)"
                            >
                                Remove
                            </DangerButton>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
