<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DropdownButton from '@/Components/DropdownButton.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RowActions from '@/Components/RowActions.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

defineProps<{
    staff: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            role: 'owner' | 'kitchen';
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
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
            <h1 class="font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">Staff</h1>
            <Link :href="route('admin.staff.create')">
                <PrimaryButton>New staff account</PrimaryButton>
            </Link>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-100 text-xs font-bold uppercase tracking-wide text-gray-400 dark:border-gray-800 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="member in staff.data" :key="member.id">
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
                            <RowActions>
                                <DropdownLink :href="route('admin.staff.edit', member.id)">Edit</DropdownLink>
                                <DropdownButton
                                    v-if="member.id !== page.props.auth.user.id"
                                    class="text-red-600 dark:text-red-400"
                                    @click="destroy(member.id)"
                                >
                                    Remove
                                </DropdownButton>
                            </RowActions>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="staff.links" />
    </AdminLayout>
</template>
