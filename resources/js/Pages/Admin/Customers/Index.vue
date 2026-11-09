<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Pagination from '@/Components/Pagination.vue';
import RowActions from '@/Components/RowActions.vue';
import { formatDate } from '@/lib/formatDate';
import { Head, Link } from '@inertiajs/vue3';

interface CustomerRow {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    bookings_count: number;
    do_not_contact: boolean;
    created_at: string;
}

defineProps<{
    customers: {
        data: CustomerRow[];
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
</script>

<template>
    <AdminLayout>
        <Head title="Customers" />

        <PageHeader title="Customers">{{ customers.total }} customers</PageHeader>

        <div
            class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <table class="w-full text-left text-sm">
                <thead
                    class="border-b border-gray-100 text-xs font-bold uppercase tracking-wide text-gray-400 dark:border-gray-800 dark:text-gray-400"
                >
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3 text-right">Bookings</th>
                        <th class="px-4 py-3">Joined</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="customer in customers.data" :key="customer.id">
                        <td class="px-4 py-3 font-medium">
                            <Link
                                :href="route('admin.customers.show', customer.id)"
                                class="text-indigo-600 hover:text-indigo-700 hover:underline dark:text-indigo-400"
                            >
                                {{ customer.name }}
                            </Link>
                            <span
                                v-if="customer.do_not_contact"
                                class="ml-1.5 inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700 dark:bg-red-950 dark:text-red-300"
                            >
                                Do not contact
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ customer.email }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ customer.phone ?? '—' }}
                        </td>
                        <td
                            class="px-4 py-3 text-right text-xs tabular-nums text-gray-900 dark:text-gray-100"
                        >
                            {{ customer.bookings_count }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ formatDate(customer.created_at) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <RowActions>
                                <DropdownLink :href="route('admin.customers.show', customer.id)">
                                    View detail
                                </DropdownLink>
                            </RowActions>
                        </td>
                    </tr>
                    <tr v-if="customers.data.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-gray-600">
                            No customers yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="customers.links" />
    </AdminLayout>
</template>
