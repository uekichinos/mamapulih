<script setup lang="ts">
withDefaults(defineProps<{ size?: string; rounded?: string }>(), {
    size: 'size-8',
    rounded: 'rounded-lg',
});
</script>

<template>
    <span
        :class="[size, rounded]"
        class="relative flex shrink-0 items-center justify-center bg-linear-to-br from-indigo-500 to-indigo-700"
    >
        <svg viewBox="0 0 64 64" class="h-[62%] w-[62%]" aria-hidden="true">
            <path d="M16 32C16 41,23 47,32 47C41 47,48 41,48 32Z" fill="#ffffff" />
            <ellipse cx="32" cy="32" rx="16" ry="4.2" fill="#c7d2fe" />
            <path
                d="M25 26C22 22,26 18,23 14"
                fill="none"
                stroke="#ffffff"
                stroke-width="3"
                stroke-linecap="round"
            />
            <path
                d="M33 26C30 21,35 17,32 12"
                fill="none"
                stroke="#ffffff"
                stroke-width="3"
                stroke-linecap="round"
            />
            <path
                d="M41 26C38 22,42 18,39 14"
                fill="none"
                stroke="#ffffff"
                stroke-width="3"
                stroke-linecap="round"
            />
        </svg>
    </span>
</template>
