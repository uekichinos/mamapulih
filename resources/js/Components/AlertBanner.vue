<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        tone?: 'amber' | 'red' | 'blue';
    }>(),
    {
        tone: 'amber',
    },
);

const toneClasses: Record<string, string> = {
    amber: 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300',
    red: 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300',
    blue: 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300',
};

const classes = computed(() => toneClasses[props.tone]);
</script>

<template>
    <div class="rounded-xl border p-4 text-sm" :class="classes">
        <slot />
    </div>
</template>
