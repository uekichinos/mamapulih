<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface Check {
    name: string;
    pass: boolean;
    message: string;
}

defineProps<{
    checks: Check[];
    ready: boolean;
}>();

const recheck = () => router.reload();
</script>

<template>
    <GuestLayout>
        <Head title="Install — environment check" />

        <h1 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Environment check</h1>

        <ul class="mb-4 divide-y divide-gray-200 dark:divide-gray-700">
            <li
                v-for="check in checks"
                :key="check.name"
                class="flex items-center justify-between py-2 text-sm"
            >
                <span class="text-gray-700 dark:text-gray-300">{{ check.name }}</span>
                <span :class="check.pass ? 'text-green-600' : 'text-red-600'">
                    {{ check.pass ? '✓' : '✗' }} {{ check.message }}
                </span>
            </li>
        </ul>

        <div class="flex items-center justify-between">
            <SecondaryButton @click="recheck">Re-check</SecondaryButton>

            <Link v-if="ready" :href="route('install.database')">
                <PrimaryButton>Continue</PrimaryButton>
            </Link>
        </div>
    </GuestLayout>
</template>
