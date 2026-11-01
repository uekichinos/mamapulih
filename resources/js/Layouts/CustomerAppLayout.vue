<script setup lang="ts">
import { computed, ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingMobileSidebar = ref(false);

type NavLink = { label: string; route: string; icon: string; activePatterns?: string[] };

const navLinks: NavLink[] = [
    {
        label: 'Dashboard',
        route: 'customer.dashboard',
        icon: 'M4 11h16M6 11V7a1 1 0 0 1 1-1h7l3 5v6a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-6Z',
    },
    {
        label: 'Booking',
        route: 'customer.my-booking',
        icon: 'M3 5h18M3 5v14h18V5M8 3v4M16 3v4',
        // A customer can have more than one live booking at once, so
        // "Schedule"/"Start" aren't single flat destinations any more —
        // they're reached per-booking from this page and Dashboard instead.
        activePatterns: [
            'customer.my-booking',
            'customer.my-booking.cancel',
            'customer.my-booking.receipts.view',
            'customer.my-booking.archive',
            'customer.my-booking.schedule',
            'customer.start',
            'customer.start.confirm',
        ],
    },
    {
        label: 'Payment',
        route: 'customer.payment',
        icon: 'M3 7h18v10H3zM3 10h18M7 14h3',
    },
];

const page = usePage();

const initials = computed(() =>
    (page.props.auth.customer?.name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join(''),
);

const isLinkActive = (link: NavLink) =>
    (link.activePatterns ?? [link.route]).some((pattern) => route().current(pattern));
</script>

<template>
    <div class="flex min-h-screen bg-gray-50 font-sans dark:bg-gray-950">
        <!-- Mobile backdrop -->
        <div
            v-if="showingMobileSidebar"
            class="fixed inset-0 z-30 bg-black/50 sm:hidden"
            @click="showingMobileSidebar = false"
        />

        <!-- Sidebar (fixed on desktop, slide-over on mobile) -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col border-r border-gray-100 bg-white transition-transform duration-200 ease-in-out dark:border-gray-800 dark:bg-gray-900 sm:translate-x-0"
            :class="showingMobileSidebar ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-16 shrink-0 items-center justify-between px-4">
                <Link :href="route('customer.dashboard')" class="flex items-center gap-2.5">
                    <span
                        class="size-8 shrink-0 rounded-lg bg-linear-to-br from-indigo-500 to-indigo-700"
                    />
                    <span class="flex flex-col leading-tight">
                        <span
                            class="font-display text-base font-extrabold text-gray-900 dark:text-gray-50"
                            >MamaPulih</span
                        >
                        <span
                            class="text-[10px] font-semibold tracking-wider text-gray-600 dark:text-gray-400"
                            >CUSTOMER</span
                        >
                    </span>
                </Link>

                <button
                    type="button"
                    class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-600 sm:hidden dark:hover:bg-gray-800 dark:hover:text-gray-400"
                    aria-label="Close menu"
                    @click="showingMobileSidebar = false"
                >
                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <Link
                    v-for="link in navLinks"
                    :key="link.route"
                    :href="route(link.route)"
                    class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-semibold transition duration-150 ease-in-out"
                    :class="
                        isLinkActive(link)
                            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200'
                    "
                    @click="showingMobileSidebar = false"
                >
                    <svg
                        class="size-4.5 shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        aria-hidden="true"
                    >
                        <path :d="link.icon" />
                    </svg>
                    <span>{{ link.label }}</span>
                </Link>
            </nav>

            <div class="border-t border-gray-100 p-3 dark:border-gray-800">
                <Dropdown align="right" width="48" direction="up">
                    <template #trigger>
                        <button
                            type="button"
                            class="flex w-full items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            <span
                                class="flex size-7 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-indigo-500 to-indigo-700 text-xs font-bold text-white"
                            >
                                {{ initials }}
                            </span>
                            <span class="flex-1 truncate text-left font-semibold">{{
                                $page.props.auth.customer?.name
                            }}</span>
                            <svg
                                class="size-3.5 shrink-0 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 15l7-7 7 7"
                                />
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink :href="route('customer.profile.edit')">Profile</DropdownLink>
                        <DropdownLink :href="route('customer.logout')" method="post" as="button">
                            Log out
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </aside>

        <!-- Main column -->
        <div class="flex min-h-screen w-full flex-1 flex-col sm:ms-64">
            <!-- Mobile top bar -->
            <div
                class="flex h-16 items-center justify-between border-b border-gray-100 bg-white px-4 sm:hidden dark:border-gray-800 dark:bg-gray-900"
            >
                <Link :href="route('customer.dashboard')" class="flex items-center gap-2.5">
                    <span
                        class="size-8 shrink-0 rounded-lg bg-linear-to-br from-indigo-500 to-indigo-700"
                    />
                    <span
                        class="font-display text-base font-extrabold text-gray-900 dark:text-gray-50"
                        >MamaPulih</span
                    >
                </Link>

                <button
                    type="button"
                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-400"
                    aria-label="Open menu"
                    @click="showingMobileSidebar = true"
                >
                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>
            </div>

            <main class="flex-1">
                <div class="mx-auto max-w-5xl px-4 py-7 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
