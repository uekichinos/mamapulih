<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps<{
    options: string[];
    placeholder?: string;
    id?: string;
}>();

const model = defineModel<string>({ required: true });

const query = ref(model.value);
const open = ref(false);
const highlighted = ref(0);

const filtered = computed(() => {
    const needle = query.value.trim().toLowerCase();

    const matches = needle
        ? props.options.filter((option) => option.toLowerCase().includes(needle))
        : props.options;

    return matches.slice(0, 20);
});

const sync = () => {
    model.value = query.value;
};

const select = (option: string) => {
    query.value = option;
    model.value = option;
    open.value = false;
};

const onInput = () => {
    open.value = true;
    highlighted.value = 0;
    sync();
};

const onFocus = () => {
    open.value = true;
    highlighted.value = 0;
};

const onBlur = () => {
    // Let a mousedown on an option register (via @mousedown.prevent below)
    // before the list disappears.
    setTimeout(() => {
        open.value = false;
        sync();
    }, 100);
};

const moveDown = () => {
    if (!open.value) {
        open.value = true;

        return;
    }

    highlighted.value = Math.min(highlighted.value + 1, filtered.value.length - 1);
};

const moveUp = () => {
    highlighted.value = Math.max(highlighted.value - 1, 0);
};

const chooseHighlighted = () => {
    const option = filtered.value[highlighted.value];

    if (option) {
        select(option);
    }
};
</script>

<template>
    <div class="relative">
        <input
            :id="id"
            v-model="query"
            type="text"
            :placeholder="placeholder"
            autocomplete="off"
            class="block w-full rounded-lg border-gray-300 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-600 dark:focus:border-indigo-500 dark:focus:ring-indigo-500"
            @input="onInput"
            @focus="onFocus"
            @blur="onBlur"
            @keydown.down.prevent="moveDown"
            @keydown.up.prevent="moveUp"
            @keydown.enter.prevent="chooseHighlighted"
            @keydown.escape="open = false"
        />

        <ul
            v-if="open && filtered.length"
            class="absolute z-10 mt-1 max-h-56 w-full overflow-auto rounded-lg border border-gray-200 bg-white py-1 text-sm shadow-lg dark:border-gray-700 dark:bg-gray-900"
        >
            <li
                v-for="(option, index) in filtered"
                :key="option"
                class="cursor-pointer px-3 py-1.5"
                :class="
                    index === highlighted
                        ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300'
                        : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800'
                "
                @mousedown.prevent="select(option)"
            >
                {{ option }}
            </li>
        </ul>

        <p
            v-else-if="open && query.trim()"
            class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-600 shadow-lg dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400"
        >
            No matching postcode.
        </p>
    </div>
</template>
