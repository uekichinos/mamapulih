<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: { en: '', ms: '' },
});

const submit = () => form.post(route('admin.menu-rotations.store'));
</script>

<template>
    <AdminLayout>
        <Head title="New menu rotation" />

        <h1 class="mb-6 text-2xl font-semibold text-gray-900 dark:text-gray-100">New menu rotation</h1>

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

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Create
            </PrimaryButton>
        </form>
    </AdminLayout>
</template>
