<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        tone?: 'accent' | 'success' | 'warning' | 'danger' | 'info' | 'neutral';
    }>(),
    {
        tone: 'neutral',
    },
);

const toneClasses: Record<string, string> = {
    accent: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300',
    success: 'bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300',
    warning: 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
    danger: 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300',
    info: 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    neutral: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
};

const classes = computed(() => toneClasses[props.tone]);
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="classes"
    >
        <slot />
    </span>
</template>
