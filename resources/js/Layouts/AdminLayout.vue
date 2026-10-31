<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingMobileSidebar = ref(false);

type NavLink = { label: string; route: string; roles: string[] };
type NavGroup = { key: string; label: string; icon: string; links: NavLink[] };

const pinnedLink: NavLink = { label: 'Dashboard', route: 'admin.dashboard', roles: ['owner'] };

const allNavGroups: NavGroup[] = [
    {
        key: 'bookings',
        label: 'Bookings',
        icon: '<rect x="3" y="5" width="18" height="16" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="7" y1="2" x2="7" y2="6"/><line x1="17" y1="2" x2="17" y2="6"/>',
        links: [
            { label: 'Approvals', route: 'admin.booking-approvals.index', roles: ['owner'] },
            { label: 'Bookings', route: 'admin.bookings.index', roles: ['owner'] },
            { label: 'Capacity', route: 'admin.capacity.index', roles: ['owner'] },
        ],
    },
    {
        key: 'catalog',
        label: 'Catalog',
        icon: '<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="3" x2="12" y2="21"/><line x1="3" y1="12" x2="21" y2="12"/>',
        links: [
            { label: 'Packages', route: 'admin.packages.index', roles: ['owner'] },
            { label: 'Add-ons', route: 'admin.addons.index', roles: ['owner'] },
            { label: 'Dishes', route: 'admin.dishes.index', roles: ['owner'] },
            { label: 'Menu rotations', route: 'admin.menu-rotations.index', roles: ['owner'] },
        ],
    },
    {
        key: 'kitchen',
        label: 'Kitchen & delivery',
        icon: '<rect x="2" y="7" width="20" height="4" rx="1"/><circle cx="8" cy="17" r="3"/><circle cx="16" cy="17" r="3"/>',
        links: [
            { label: 'Prep sheet', route: 'admin.kitchen.prep-sheet', roles: ['owner', 'kitchen'] },
            { label: 'Delivery lists', route: 'admin.delivery-lists.index', roles: ['owner', 'kitchen'] },
        ],
    },
    {
        key: 'operations',
        label: 'Operations',
        icon: '<circle cx="12" cy="9" r="4"/><polygon points="12,21 7,12 17,12"/>',
        links: [
            { label: 'Zones', route: 'admin.zones.index', roles: ['owner'] },
            { label: 'Closures', route: 'admin.closure-days.index', roles: ['owner'] },
        ],
    },
    {
        key: 'admin',
        label: 'Administration',
        icon: '<polygon points="12,2 20,6 20,12 12,22 4,12 4,6"/>',
        links: [
            { label: 'Staff', route: 'admin.staff.index', roles: ['owner'] },
            { label: 'Settings', route: 'admin.settings.edit', roles: ['owner'] },
            { label: 'Payment settings', route: 'admin.payment-settings.edit', roles: ['owner'] },
            { label: 'Audit log', route: 'admin.audit-log.index', roles: ['owner'] },
        ],
    },
];

const page = usePage();
const role = computed(() => page.props.auth.user?.role ?? 'owner');

const initials = computed(() =>
    (page.props.auth.user?.name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join(''),
);

const topLink = computed(() =>
    pinnedLink.roles.includes(role.value) ? pinnedLink : null,
);

const navGroups = computed(() =>
    allNavGroups
        .map((group) => ({
            ...group,
            links: group.links.filter((link) => link.roles.includes(role.value)),
        }))
        .filter((group) => group.links.length > 0),
);

const isLinkActive = (link: NavLink) => route().current(link.route + '*');
const isGroupActive = (group: NavGroup) => group.links.some(isLinkActive);

const openGroup = ref<string | null>(null);

watch(
    () => page.url,
    () => {
        const active = navGroups.value.find(isGroupActive);

        if (active) {
            openGroup.value = active.key;
        }
    },
    { immediate: true },
);

const toggleGroup = (key: string) => {
    openGroup.value = openGroup.value === key ? null : key;
};
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
                <Link :href="route('admin.dashboard')" class="flex items-center gap-2.5">
                    <span class="size-8 shrink-0 rounded-lg bg-linear-to-br from-indigo-500 to-indigo-700" />
                    <span class="flex flex-col leading-tight">
                        <span class="font-display text-base font-extrabold text-gray-900 dark:text-gray-50">MamaPulih</span>
                        <span class="text-[10px] font-semibold tracking-wider text-gray-600 dark:text-gray-400">ADMIN</span>
                    </span>
                </Link>

                <button
                    type="button"
                    class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-600 sm:hidden dark:hover:bg-gray-800 dark:hover:text-gray-400"
                    aria-label="Close menu"
                    @click="showingMobileSidebar = false"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <Link
                    v-if="topLink"
                    :href="route(topLink.route)"
                    class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-semibold transition duration-150 ease-in-out"
                    :class="
                        isLinkActive(topLink)
                            ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200'
                    "
                    @click="showingMobileSidebar = false"
                >
                    <svg class="size-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <rect x="4" y="11" width="16" height="9" rx="1" /><polyline points="2,12 12,4 22,12" /><rect x="10" y="14" width="4" height="6" />
                    </svg>
                    <span>{{ topLink.label }}</span>
                </Link>

                <div v-if="topLink && navGroups.length" class="my-2 border-t border-gray-100 dark:border-gray-800" />

                <div v-for="group in navGroups" :key="group.key">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-left transition duration-150 ease-in-out"
                        :class="
                            isGroupActive(group)
                                ? 'text-indigo-700 dark:text-indigo-300'
                                : 'text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                        "
                        @click="toggleGroup(group.key)"
                    >
                        <span class="flex items-center gap-2.5 text-[11px] font-bold uppercase tracking-wider">
                            <!-- eslint-disable-next-line vue/no-v-html -- static icon markup authored in this file, not user input -->
                            <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" v-html="group.icon" />
                            <span>{{ group.label }}</span>
                        </span>
                        <svg
                            class="size-3.5 shrink-0 transition-transform duration-150"
                            :class="openGroup === group.key ? '' : '-rotate-90'"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <polyline points="6,9 12,15 18,9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div v-show="openGroup === group.key" class="mt-1 space-y-0.5 pb-2 ps-7">
                        <Link
                            v-for="link in group.links"
                            :key="link.route"
                            :href="route(link.route)"
                            class="block rounded-lg px-3 py-1.5 text-sm font-medium transition duration-150 ease-in-out"
                            :class="
                                isLinkActive(link)
                                    ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300'
                                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200'
                            "
                            @click="showingMobileSidebar = false"
                        >
                            {{ link.label }}
                        </Link>
                    </div>
                </div>
            </nav>

            <div class="border-t border-gray-100 p-3 dark:border-gray-800">
                <Dropdown align="right" width="48" direction="up">
                    <template #trigger>
                        <button
                            type="button"
                            class="flex w-full items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-indigo-500 to-indigo-700 text-xs font-bold text-white">
                                {{ initials }}
                            </span>
                            <span class="flex-1 truncate text-left font-semibold">{{ $page.props.auth.user.name }}</span>
                            <svg class="size-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">
                            Log Out
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </aside>

        <!-- Main column -->
        <div class="flex min-h-screen w-full flex-1 flex-col sm:ms-64">
            <!-- Mobile top bar -->
            <div class="flex h-16 items-center justify-between border-b border-gray-100 bg-white px-4 sm:hidden dark:border-gray-800 dark:bg-gray-900">
                <Link :href="route('admin.dashboard')" class="flex items-center gap-2.5">
                    <span class="size-8 shrink-0 rounded-lg bg-linear-to-br from-indigo-500 to-indigo-700" />
                    <span class="font-display text-base font-extrabold text-gray-900 dark:text-gray-50">MamaPulih</span>
                </Link>

                <button
                    type="button"
                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-400"
                    aria-label="Open menu"
                    @click="showingMobileSidebar = true"
                >
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <header v-if="$slots.header" class="border-b border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main class="flex-1">
                <div class="mx-auto max-w-5xl px-4 py-7 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
