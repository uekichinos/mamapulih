<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

interface Row {
    dish: { name: { en: string; ms: string } };
    count: number;
    substituted: number;
}

const props = defineProps<{
    date: string;
    meals: Record<string, Record<string, Row>>;
}>();

const changeDate = (event: Event) => {
    router.get(route('admin.kitchen.prep-sheet'), { date: (event.target as HTMLInputElement).value });
};

const rowsFor = (meal: string): Row[] => Object.values(props.meals[meal] ?? {});
</script>

<template>
    <AdminLayout>
        <Head title="Kitchen prep sheet" />

        <div class="mb-6 flex items-center justify-between print:hidden">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Prep sheet</h1>
            <input type="date" :value="date" class="rounded-md border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" @change="changeDate" />
        </div>

        <h2 class="mb-2 text-lg font-medium text-gray-800 dark:text-gray-200">{{ date }}</h2>

        <div v-for="meal in ['lunch', 'dinner']" :key="meal" class="mb-6">
            <h3 class="mb-2 font-medium capitalize text-gray-700 dark:text-gray-300">{{ meal }}</h3>
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="py-2">Dish</th>
                        <th class="py-2">Portions</th>
                        <th class="py-2">Substituted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-for="(row, i) in rowsFor(meal)" :key="i">
                        <td class="py-2 text-gray-900 dark:text-gray-100">{{ row.dish.name.en }}</td>
                        <td class="py-2 text-gray-600 dark:text-gray-400">{{ row.count }}</td>
                        <td class="py-2 text-gray-600 dark:text-gray-400">
                            <span v-if="row.substituted">{{ row.substituted }} (exclusion swap)</span>
                        </td>
                    </tr>
                    <tr v-if="rowsFor(meal).length === 0">
                        <td colspan="3" class="py-4 text-center text-gray-500">Nothing scheduled.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
