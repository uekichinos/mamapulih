<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: { en: '', ms: '' },
});

const submit = () => {
    // BM input is hidden from the UI; mirror the English value so the
    // (unchanged) backend validation, which still requires both locales,
    // keeps passing.
    form.name.ms = form.name.en;
    form.post(route('admin.menu-rotations.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="New menu rotation" />

        <PageHeader title="New menu rotation">
            A repeating weekly cycle of dishes — add its days after creating it.
        </PageHeader>

        <form @submit.prevent="submit">
            <div
                class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <InputLabel for="name_en" value="Name" />
                <TextInput id="name_en" v-model="form.name.en" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors['name.en']" />
            </div>

            <div class="mt-4">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Create
                </PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>
