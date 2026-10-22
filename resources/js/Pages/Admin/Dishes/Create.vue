<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    dishes: Array<{ id: number; name: { en: string; ms: string } }>;
    tags: Array<{ value: string; label: string }>;
}>();

const form = useForm({
    name: { en: '', ms: '' },
    tags: [] as string[],
    substitute_dish_id: null as number | null,
    active: true,
});

const submit = () => form.post(route('admin.dishes.store'));
</script>

<template>
    <AdminLayout>
        <Head title="New dish" />

        <h1 class="mb-6 text-2xl font-semibold text-gray-900 dark:text-gray-100">New dish</h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="name_en" value="Name (English)" />
                    <TextInput id="name_en" v-model="form.name.en" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors['name.en']" />
                </div>
                <div>
                    <InputLabel for="name_ms" value="Name (Bahasa Melayu)" />
                    <TextInput id="name_ms" v-model="form.name.ms" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors['name.ms']" />
                </div>
            </div>

            <div>
                <InputLabel value="Tags" />
                <div class="mt-2 flex flex-wrap gap-3">
                    <label v-for="tag in tags" :key="tag.value" class="flex items-center gap-1 text-sm text-gray-700 dark:text-gray-300">
                        <input v-model="form.tags" type="checkbox" :value="tag.value" />
                        {{ tag.label }}
                    </label>
                </div>
            </div>

            <div>
                <InputLabel for="substitute_dish_id" value="Substitute dish (used when a customer excludes a tag above)" />
                <select
                    id="substitute_dish_id"
                    v-model="form.substitute_dish_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option :value="null">None</option>
                    <option v-for="dish in dishes" :key="dish.id" :value="dish.id">{{ dish.name.en }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.substitute_dish_id" />
            </div>

            <label class="flex items-center gap-2">
                <input v-model="form.active" type="checkbox" class="rounded border-gray-300" />
                <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Create
            </PrimaryButton>
        </form>
    </AdminLayout>
</template>
