<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

interface Row {
    name: string;
    address: string;
    phone: string | null;
    notes: string | null;
    one_trip: boolean;
}

const props = defineProps<{
    date: string;
    runs: Record<string, Record<string, Row[]>>;
}>();

const changeDate = (event: Event) => {
    router.get(route('admin.delivery-lists.index'), {
        date: (event.target as HTMLInputElement).value,
    });
};

const zonesFor = (run: string): [string, Row[]][] => Object.entries(props.runs[run] ?? {});
</script>

<template>
    <AdminLayout>
        <Head title="Delivery lists" />

        <div class="mb-6 flex items-center justify-between print:hidden">
            <h1 class="font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
                Delivery lists
            </h1>
            <input
                type="date"
                :value="date"
                class="rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                @change="changeDate"
            />
        </div>

        <h2 class="mb-2 font-display text-sm font-bold text-gray-900 dark:text-gray-50">
            {{ date }}
        </h2>

        <div v-for="run in ['lunch', 'dinner']" :key="run" class="mb-6">
            <h3 class="mb-2 font-medium capitalize text-gray-700 dark:text-gray-300">
                {{ run }} run
            </h3>

            <div v-for="[zone, rows] in zonesFor(run)" :key="zone" class="mb-4">
                <h4 class="mb-1 text-sm font-semibold text-gray-600 dark:text-gray-400">
                    {{ zone }}
                </h4>
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-gray-100 text-xs font-bold uppercase tracking-wide text-gray-400 dark:border-gray-800 dark:text-gray-400"
                    >
                        <tr>
                            <th class="py-2">Name</th>
                            <th class="py-2">Address</th>
                            <th class="py-2">Phone</th>
                            <th class="py-2">Notes</th>
                            <th class="py-2">+Dinner</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="(row, i) in rows" :key="i">
                            <td class="py-2 text-gray-900 dark:text-gray-100">{{ row.name }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">{{ row.address }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">{{ row.phone }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">{{ row.notes }}</td>
                            <td class="py-2 text-gray-600 dark:text-gray-400">
                                {{ row.one_trip ? 'Yes' : '' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p v-if="zonesFor(run).length === 0" class="text-sm text-gray-600">
                Nothing scheduled.
            </p>
        </div>
    </AdminLayout>
</template>
