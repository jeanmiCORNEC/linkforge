<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Footer from '@/Components/Footer.vue';
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col transition-colors duration-300">
        <!-- Header -->
        <header class="border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-950/95 backdrop-blur sticky top-0 z-50">
            <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
                <Link :href="route('welcome')" class="flex items-center gap-2">
                    <ApplicationLogo class="w-9 h-9 text-indigo-600 dark:text-indigo-400" />
                    <span class="font-semibold text-lg tracking-tight">LinkForge</span>
                </Link>

                <nav class="flex items-center gap-4 text-sm font-medium">
                    <Link :href="route('welcome')" class="hover:text-indigo-600 dark:hover:text-indigo-300 transition">
                        Retour à l'accueil
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 max-w-4xl mx-auto px-4 py-12 w-full">
            <div class="bg-white dark:bg-slate-900 p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <slot />
            </div>
        </main>

        <Footer />
    </div>
</template>
