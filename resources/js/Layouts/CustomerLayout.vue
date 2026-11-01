<script setup lang="ts">
import { computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const customer = computed(() => page.props.auth.customer);

const initials = computed(() =>
    (customer.value?.name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join(''),
);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-gray-50 font-sans dark:bg-gray-950">
        <nav class="border-b border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto flex max-w-xl items-center justify-between px-4 py-3.5">
                <Link href="/" class="flex items-center gap-2.5">
                    <span
                        class="size-7 shrink-0 rounded-lg bg-linear-to-br from-indigo-500 to-indigo-700"
                    />
                    <span
                        class="font-display text-base font-extrabold text-gray-900 dark:text-gray-50"
                        >MamaPulih</span
                    >
                </Link>

                <div v-if="customer" class="flex items-center gap-3 text-sm font-medium">
                    <Link
                        :href="route('customer.my-booking')"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        Booking
                    </Link>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded-full p-0.5 hover:ring-2 hover:ring-gray-200 dark:hover:ring-gray-700"
                            >
                                <span
                                    class="flex size-8 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-indigo-500 to-indigo-700 text-xs font-bold text-white"
                                >
                                    {{ initials }}
                                </span>
                            </button>
                        </template>

                        <template #content>
                            <div class="border-b border-gray-100 px-4 py-2 dark:border-gray-700">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ customer.name }}
                                </p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ customer.email }}
                                </p>
                            </div>
                            <DropdownLink :href="route('customer.profile.edit')">
                                Profile
                            </DropdownLink>
                            <DropdownLink
                                :href="route('customer.logout')"
                                method="post"
                                as="button"
                            >
                                Log out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>

                <div v-else class="flex items-center gap-4 text-sm font-medium">
                    <Link
                        :href="route('customer.login')"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="route('customer.register')"
                        class="text-indigo-600 hover:text-indigo-700"
                    >
                        Get started
                    </Link>
                </div>
            </div>
        </nav>

        <main class="mx-auto w-full max-w-xl flex-1 px-4 py-7">
            <slot />
        </main>
    </div>
</template>
