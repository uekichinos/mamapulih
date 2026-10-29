<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const isAuthed = () => Boolean((page.props as { auth?: { customer?: unknown } }).auth?.customer);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-gray-50 font-sans dark:bg-gray-950">
        <nav class="border-b border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto flex max-w-xl items-center justify-between px-4 py-3.5">
                <Link href="/" class="flex items-center gap-2.5">
                    <span class="size-7 shrink-0 rounded-lg bg-linear-to-br from-indigo-500 to-indigo-700" />
                    <span class="font-display text-base font-extrabold text-gray-900 dark:text-gray-50">MamaPulih</span>
                </Link>

                <div class="flex items-center gap-4 text-sm font-medium">
                    <Link
                        v-if="isAuthed()"
                        :href="route('customer.my-booking')"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        My Booking
                    </Link>
                    <Link
                        v-if="isAuthed()"
                        :href="route('customer.logout')"
                        method="post"
                        as="button"
                        class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        Log out
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('customer.login')"
                            class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                        >
                            Log in
                        </Link>
                        <Link :href="route('customer.register')" class="text-indigo-600 hover:text-indigo-700">
                            Get started
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <main class="mx-auto w-full max-w-xl flex-1 px-4 py-7">
            <slot />
        </main>
    </div>
</template>
