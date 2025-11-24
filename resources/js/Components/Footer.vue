<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <footer class="bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-2">
                    <ApplicationLogo class="w-8 h-8 text-indigo-600 dark:text-indigo-400" />
                    <span class="font-semibold text-slate-900 dark:text-white">LinkForge</span>
                </div>

                <div class="flex flex-wrap justify-center gap-6 text-sm text-slate-600 dark:text-slate-400">
                    <Link :href="route('welcome')" class="hover:text-indigo-600 dark:hover:text-indigo-300 transition">
                        Accueil
                    </Link>
                    <Link :href="route('terms.show')" class="hover:text-indigo-600 dark:hover:text-indigo-300 transition">
                        CGU / CGV
                    </Link>
                    <Link :href="route('privacy.show')" class="hover:text-indigo-600 dark:hover:text-indigo-300 transition">
                        Confidentialité
                    </Link>
                    <Link :href="route('affiliation.index')" class="hover:text-indigo-600 dark:hover:text-indigo-300 transition">
                        Affiliation
                    </Link>
                    <a href="mailto:hello@linkforge.app" class="hover:text-indigo-600 dark:hover:text-indigo-300 transition">
                        Contact
                    </a>
                </div>

                <div class="text-sm text-slate-500 dark:text-slate-500">
                    &copy; {{ new Date().getFullYear() }} LinkForge. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
</template>
