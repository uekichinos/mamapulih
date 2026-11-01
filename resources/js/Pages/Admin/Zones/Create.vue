<script setup lang="ts">
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const postcodesInput = ref('');

const form = useForm({
    name: '',
    postcodes: [] as string[],
    free_delivery: true,
    transport_cost_per_trip_sen: 0,
    one_trip_allowed: true,
});

const submit = () => {
    form.postcodes = postcodesInput.value
        .split(',')
        .map((p) => p.trim())
        .filter(Boolean);

    form.post(route('admin.zones.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="New zone" />

        <PageHeader title="New zone">
            A delivery area, matched to a booking by its postcode.
        </PageHeader>

        <form @submit.prevent="submit">
            <div
                class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div>
                    <InputLabel for="name" value="Zone name" />
                    <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="postcodes" value="Postcodes (comma-separated)" />
                    <TextInput
                        id="postcodes"
                        v-model="postcodesInput"
                        class="mt-1 block w-full"
                        placeholder="47130, 47620, 47600"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.postcodes" />
                </div>
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
