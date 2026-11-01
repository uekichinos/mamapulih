<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        tone?: 'indigo' | 'blue' | 'green' | 'amber' | 'red' | 'slate';
    }>(),
    {
        title: undefined,
        description: undefined,
        tone: 'indigo',
    },
);

const toneClasses: Record<string, string> = {
    indigo: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300',
    blue: 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    green: 'bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300',
    amber: 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
    red: 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300',
    slate: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
};

const classes = computed(() => toneClasses[props.tone]);
</script>

<template>
    <div
        class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
    >
        <div v-if="title || $slots.icon" class="mb-3 flex items-start gap-3">
            <span
                v-if="$slots.icon"
                class="flex size-8 shrink-0 items-center justify-center rounded-lg"
                :class="classes"
            >
                <slot name="icon" />
            </span>
            <div v-if="title">
                <h2 class="font-display text-sm font-bold text-gray-900 dark:text-gray-50">
                    {{ title }}
                </h2>
                <p v-if="description" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    {{ description }}
                </p>
            </div>
        </div>
        <slot />
    </div>
</template>
