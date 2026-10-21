<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const isAuthed = () => Boolean((page.props as { auth?: { customer?: unknown } }).auth?.customer);
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <nav class="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="mx-auto flex max-w-xl items-center justify-between px-4 py-3">
                <Link href="/" class="flex items-center gap-2">
                    <ApplicationLogo class="h-8 w-8 fill-current text-gray-500" />
                    <span class="font-semibold text-gray-800 dark:text-gray-200">MamaPulih</span>
                </Link>

                <div class="flex items-center gap-4 text-sm">
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
                    <LanguageSwitcher />
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-xl px-4 py-6">
            <slot />
        </main>
    </div>
</template>
