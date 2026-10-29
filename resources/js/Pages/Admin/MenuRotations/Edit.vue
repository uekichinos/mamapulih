<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

interface RotationDay {
    id: number;
    day_no: number;
    meal: string;
    dish_id: number;
}

const props = defineProps<{
    menuRotation: { id: number; name: { en: string; ms: string } };
    rotationDays: RotationDay[];
    dishes: Array<{ id: number; name: { en: string; ms: string } }>;
    days: number[];
    meals: string[];
}>();

const form = useForm({
    name: { ...props.menuRotation.name },
});

const submit = () => {
    // BM input is hidden from the UI; mirror the English value so the
    // (unchanged) backend validation, which still requires both locales,
    // keeps passing.
    form.name.ms = form.name.en;
    form.put(route('admin.menu-rotations.update', props.menuRotation.id));
};

const dishFor = (dayNo: number, meal: string): number | null => {
    const match = props.rotationDays.find((rd) => rd.day_no === dayNo && rd.meal === meal);

    return match?.dish_id ?? null;
};

const setDish = (dayNo: number, meal: string, event: Event) => {
    const dishId = (event.target as HTMLSelectElement).value;

    if (!dishId) return;

    router.post(route('admin.menu-rotations.rotation-days.store', props.menuRotation.id), {
        day_no: dayNo,
        meal,
        dish_id: Number(dishId),
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Edit menu rotation" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">Edit menu rotation</h1>

        <form class="mb-8 max-w-xl space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="name_en" value="Name" />
                <TextInput id="name_en" v-model="form.name.en" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors['name.en']" />
            </div>
            <PrimaryButton :disabled="form.processing">Save name</PrimaryButton>
        </form>

        <h2 class="mb-4 font-display text-sm font-bold text-gray-900 dark:text-gray-50">Weekly rotation</h2>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-100 text-xs font-bold uppercase tracking-wide text-gray-400 dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Day</th>
                        <th v-for="meal in meals" :key="meal" class="px-4 py-3 capitalize">{{ meal }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-for="day in days" :key="day">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">Day {{ day }}</td>
                        <td v-for="meal in meals" :key="meal" class="px-4 py-3">
                            <select
                                :value="dishFor(day, meal)"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-indigo-500 dark:focus:ring-indigo-500"
                                @change="(e) => setDish(day, meal, e)"
                            >
                                <option :value="null">—</option>
                                <option v-for="dish in dishes" :key="dish.id" :value="dish.id">{{ dish.name.en }}</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
