<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    adminEmail: string;
}>();

const form = useForm({ demo_data: false });

const finish = (demo: boolean) => {
    form.demo_data = demo;
    form.post(route('install.complete'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Install — finish" />

        <h1 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Almost done</h1>

        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
            Owner account <strong>{{ adminEmail }}</strong> is ready. Would you like to explore with
            sample packages, zones and menus, or start with a clean, empty install?
        </p>

        <div class="flex justify-end gap-3">
            <SecondaryButton :disabled="form.processing" @click="finish(false)">
                Start empty
            </SecondaryButton>

            <PrimaryButton :disabled="form.processing" @click="finish(true)">
                Load demo data
            </PrimaryButton>
        </div>
    </GuestLayout>
</template>
