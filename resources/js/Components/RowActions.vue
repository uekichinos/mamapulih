<script setup lang="ts">
import Dropdown from '@/Components/Dropdown.vue';
</script>

<template>
    <Dropdown align="right" width="48">
        <template #trigger>
            <button
                type="button"
                class="rounded-lg p-1.5 text-gray-500 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                aria-label="Actions"
            >
                <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <circle cx="12" cy="5" r="1.75" />
                    <circle cx="12" cy="12" r="1.75" />
                    <circle cx="12" cy="19" r="1.75" />
                </svg>
            </button>
        </template>

        <template #content>
            <slot />
        </template>
    </Dropdown>
</template>
