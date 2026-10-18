<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();

const locales: Array<{ code: 'en' | 'ms'; label: string }> = [
    { code: 'en', label: 'EN' },
    { code: 'ms', label: 'BM' },
];

function switchTo(code: 'en' | 'ms'): void {
    router.patch(route('locale.update', code), {}, { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <div class="flex items-center gap-1 text-sm font-medium text-gray-500 dark:text-gray-400">
        <button
            v-for="locale in locales"
            :key="locale.code"
            type="button"
            class="rounded px-2 py-1 transition duration-150 ease-in-out"
            :class="
                page.props.locale === locale.code
                    ? 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-100'
                    : 'hover:text-gray-700 dark:hover:text-gray-200'
            "
            @click="switchTo(locale.code)"
        >
            {{ locale.label }}
        </button>
    </div>
</template>
