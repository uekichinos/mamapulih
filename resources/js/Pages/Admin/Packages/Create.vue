<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: { en: '', ms: '' },
    description: { en: '', ms: '' },
    active: true,
});

const submit = () => {
    // BM input is hidden from the UI; mirror the English value so the
    // (unchanged) backend validation, which still requires both locales,
    // keeps passing.
    form.name.ms = form.name.en;
    form.description.ms = form.description.en;
    form.post(route('admin.packages.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="New package" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">New package</h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="name_en" value="Name" />
                <TextInput
                    id="name_en"
                    v-model="form.name.en"
                    class="mt-1 block w-full"
                    required
                />
                <InputError class="mt-2" :message="form.errors['name.en']" />
            </div>

            <div>
                <InputLabel for="description_en" value="Description" />
                <textarea
                    id="description_en"
                    v-model="form.description.en"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                ></textarea>
                <InputError class="mt-2" :message="form.errors['description.en']" />
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
