<script setup lang="ts">
const model = defineModel<string | number | null>({ required: true });
</script>

<template>
    <select
        v-model="model"
        class="block w-full rounded-lg border-gray-300 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-indigo-500 dark:focus:ring-indigo-500"
    >
        <slot />
    </select>
</template>
