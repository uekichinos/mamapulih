<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    date: '',
    scope: 'all',
    reason: '',
});

const submit = () => form.post(route('admin.closure-days.store'));
</script>

<template>
    <AdminLayout>
        <Head title="New closure day" />

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
            New closure day
        </h1>

        <form class="max-w-md space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="date" value="Date" />
                <TextInput
                    id="date"
                    v-model="form.date"
                    type="date"
                    class="mt-1 block w-full"
                    required
                />
                <InputError class="mt-2" :message="form.errors.date" />
            </div>

            <div>
                <InputLabel for="reason" value="Reason" />
                <TextInput
                    id="reason"
                    v-model="form.reason"
                    class="mt-1 block w-full"
                    placeholder="e.g. Hari Raya"
                />
                <InputError class="mt-2" :message="form.errors.reason" />
            </div>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Add closure day
            </PrimaryButton>
        </form>
    </AdminLayout>
</template>
