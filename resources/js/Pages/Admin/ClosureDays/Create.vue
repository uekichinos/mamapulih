<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
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

        <PageHeader title="New closure day">
            No deliveries go out on this date; affected customers can be notified.
        </PageHeader>

        <form @submit.prevent="submit">
            <div
                class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
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
            </div>

            <div class="mt-4">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Add closure day
                </PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>
