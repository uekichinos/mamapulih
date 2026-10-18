<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    locked?: boolean;
}>();

const form = useForm({ code: '' });

const submit = () => {
    form.post(route('install.verify'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Install — enter setup code" />

        <h1 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Install MamaPulih</h1>

        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Enter the one-time setup code printed in the application log (check
            <code class="rounded bg-gray-100 px-1 dark:bg-gray-900"
                >docker compose logs laravel.test</code
            >
            or your server log) when the application first started.
        </p>

        <div
            v-if="locked"
            class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-300"
        >
            Too many wrong attempts. Please wait 15 minutes before trying again.
        </div>

        <form @submit.prevent="submit">
            <InputLabel for="code" value="Setup code" />

            <TextInput
                id="code"
                v-model="form.code"
                type="text"
                class="mt-1 block w-full uppercase"
                autofocus
                :disabled="locked"
                placeholder="XXXX-XXXX"
            />

            <InputError class="mt-2" :message="form.errors.code" />

            <div class="mt-4 flex justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing || locked"
                >
                    Continue
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
