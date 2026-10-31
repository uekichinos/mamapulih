<script setup lang="ts">
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
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

        <h1 class="mb-6 font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
            New zone
        </h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
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

            <label class="flex items-center gap-2">
                <input
                    v-model="form.free_delivery"
                    type="checkbox"
                    class="rounded border-gray-300"
                />
                <span class="text-sm text-gray-700 dark:text-gray-300">Free delivery</span>
            </label>

            <label class="flex items-center gap-2">
                <input
                    v-model="form.one_trip_allowed"
                    type="checkbox"
                    class="rounded border-gray-300"
                />
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    One-trip delivery allowed (v1.1)
                </span>
            </label>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Create
            </PrimaryButton>
        </form>
    </AdminLayout>
</template>
