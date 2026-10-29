<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: Array<{ url: string | null; label: string; active: boolean }>;
}>();

// Laravel's paginator labels only ever contain a couple of known HTML
// entities (never user input), so decode just those rather than using
// v-html, which isn't safe to bind on a component like <Link>.
const decodeLabel = (label: string) =>
    label
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&hellip;/g, '…');
</script>

<template>
    <div v-if="links.length > 3" class="mt-4 flex flex-wrap items-center gap-1">
        <Link
            v-for="(link, index) in links"
            :key="index"
            :href="link.url ?? ''"
            class="rounded-lg px-3 py-1.5 text-sm font-medium transition duration-150 ease-in-out"
            :class="[
                link.active
                    ? 'bg-indigo-600 text-white'
                    : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800',
                link.url === null ? 'pointer-events-none opacity-40' : '',
            ]"
        >
            {{ decodeLabel(link.label) }}
        </Link>
    </div>
</template>
