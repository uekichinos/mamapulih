<script setup lang="ts">
defineProps<{
    title: string;
    /**
     * Use this instead of the default slot when the subtitle text is
     * conditional — a slot's presence is a compile-time fact in Vue, so
     * `v-if` inside a plain default-slot usage would still leave an empty
     * (but mb-spacing-bearing) paragraph behind.
     */
    subtitle?: string | null;
}>();
</script>

<template>
    <div class="mb-6 flex items-start justify-between gap-3">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="font-display text-xl font-extrabold text-gray-900 dark:text-gray-50">
                    {{ title }}
                </h1>
                <slot name="suffix" />
            </div>
            <p
                v-if="subtitle || $slots.default"
                class="mt-0.5 text-sm text-gray-600 dark:text-gray-400"
            >
                <template v-if="subtitle">{{ subtitle }}</template>
                <slot v-else />
            </p>
        </div>
        <div v-if="$slots.actions" class="shrink-0">
            <slot name="actions" />
        </div>
    </div>
</template>
