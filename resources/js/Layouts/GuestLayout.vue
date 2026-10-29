<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-50 pt-6 font-sans sm:justify-center sm:pt-0 dark:bg-gray-950"
    >
        <Link href="/" class="flex items-center gap-2.5">
            <span class="size-10 shrink-0 rounded-xl bg-linear-to-br from-indigo-500 to-indigo-700" />
            <span class="font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">MamaPulih</span>
        </Link>

        <div
            class="mt-6 w-full overflow-hidden rounded-2xl border border-gray-200 bg-white px-6 py-5 shadow-sm sm:max-w-md dark:border-gray-800 dark:bg-gray-900"
        >
            <slot />
        </div>
    </div>
</template>
