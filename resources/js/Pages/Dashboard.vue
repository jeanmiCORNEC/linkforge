<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link as InertiaLink } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-2">
                            Bienvenue sur LinkForge 👋
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Pour créer et gérer tes liens trackés, rends-toi dans l’onglet
                            <span class="font-semibold">Liens</span> en haut de la page.
                        </p>

                        <div class="mt-4">
                            <InertiaLink
                                :href="route('links.index')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent
                                       rounded-md font-semibold text-xs text-white uppercase tracking-widest
                                       hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-700
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                       dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                            >
                                Aller à mes liens
                            </InertiaLink>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
