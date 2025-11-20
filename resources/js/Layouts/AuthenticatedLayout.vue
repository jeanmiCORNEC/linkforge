<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

// Helper route active
const isActive = (pattern) => route().current(pattern);

// Styles nav
const navLinkBase =
    'relative inline-flex items-center px-3 py-2 text-sm font-medium transition-colors duration-150';

const navLinkClasses = (active) =>
    [
        navLinkBase,
        active
            ? 'text-white'
            : 'text-slate-400 hover:text-slate-50',
    ].join(' ');
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
        <!-- Top nav -->
        <nav class="border-b border-slate-800 bg-slate-950/95 backdrop-blur">
            <div class="mx-auto w-[95%] px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex shrink-0 items-center">
                            <Link :href="route('welcome')">
                                <ApplicationLogo class="block h-9 w-auto text-indigo-400" />
                            </Link>
                        </div>

                        <!-- Navigation Links (desktop) -->
                        <div class="hidden sm:flex sm:ms-10 items-center space-x-6">
                            <!-- Dashboard -->
                            <Link
                                :href="route('dashboard')"
                                :class="navLinkClasses(isActive('dashboard'))"
                                :aria-current="isActive('dashboard') ? 'page' : undefined"
                            >
                                <span>Tableau de bord</span>
                                <span
                                    v-if="isActive('dashboard')"
                                    class="pointer-events-none absolute inset-x-2 -bottom-1 h-0.5 rounded-full bg-indigo-500"
                                />
                            </Link>

                            <!-- Liens -->
                            <Link
                                :href="route('links.index')"
                                :class="navLinkClasses(isActive('links.index'))"
                                :aria-current="isActive('links.index') ? 'page' : undefined"
                            >
                                <span>Liens</span>
                                <span
                                    v-if="isActive('links.index')"
                                    class="pointer-events-none absolute inset-x-2 -bottom-1 h-0.5 rounded-full bg-indigo-500"
                                />
                            </Link>

                            <!-- Campagnes -->
                            <Link
                                :href="route('campaigns.index')"
                                :class="navLinkClasses(isActive('campaigns.*'))"
                                :aria-current="isActive('campaigns.*') ? 'page' : undefined"
                            >
                                <span>Campagnes</span>
                                <span
                                    v-if="isActive('campaigns.*')"
                                    class="pointer-events-none absolute inset-x-2 -bottom-1 h-0.5 rounded-full bg-indigo-500"
                                />
                            </Link>

                            <!-- Sources -->
                            <Link
                                :href="route('sources.index')"
                                :class="navLinkClasses(isActive('sources.*'))"
                                :aria-current="isActive('sources.*') ? 'page' : undefined"
                            >
                                <span>Sources</span>
                                <span
                                    v-if="isActive('sources.*')"
                                    class="pointer-events-none absolute inset-x-2 -bottom-1 h-0.5 rounded-full bg-indigo-500"
                                />
                            </Link>

                        </div>
                    </div>

                    <!-- User dropdown -->
                    <div class="hidden sm:ms-6 sm:flex sm:items-center">
                        <div class="relative ms-3">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-full">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full border border-slate-700 bg-slate-900/80 px-3 py-2 text-xs font-medium text-slate-200 shadow-sm shadow-slate-900/40 transition hover:border-indigo-500 hover:text-white hover:bg-slate-900"
                                        >
                                            {{ $page.props.auth.user.name }}

                                            <svg
                                                class="-me-0.5 ms-2 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">
                                        Profil
                                    </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">
                                        Déconnexion
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Hamburger (mobile) -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-slate-200 focus:bg-slate-800 focus:text-slate-200 focus:outline-none"
                        >
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex': !showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex': showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div
                :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                class="sm:hidden"
            >
                <div class="space-y-1 pb-3 pt-2 border-t border-slate-800 bg-slate-950">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        Tableau de bord
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('links.index')" :active="route().current('links.index')">
                        Liens
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('campaigns.index')" :active="route().current('campaigns.*')">
                        Campagnes
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('sources.index')" :active="route().current('sources.*')">
                        Sources
                    </ResponsiveNavLink>
                </div>

                <!-- Responsive Settings Options -->
                <div class="border-t border-slate-800 pb-1 pt-4 bg-slate-950">
                    <div class="px-4">
                        <div class="text-sm font-medium text-slate-100">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="text-xs font-medium text-slate-400">
                            {{ $page.props.auth.user.email }}
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">
                            Profil
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                            Déconnexion
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="border-b border-slate-900 bg-slate-950">
            <div class="mx-auto w-[95%] px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1">
            <slot />
        </main>
    </div>
</template>
