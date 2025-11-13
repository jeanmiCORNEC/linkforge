<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Dashboard – LinkForge" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Bandeau de bienvenue -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <p class="text-sm text-gray-400">
                            Bienvenue sur ton espace LinkForge
                        </p>
                        <h1 class="mt-1 text-2xl font-semibold">
                            Salut, {{ user.username ?? user.name }} 👋
                        </h1>
                        <p class="mt-2 text-sm text-gray-300">
                            Plan actuel :
                            <span
                                class="inline-flex items-center rounded-full border border-emerald-500/40 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-300 uppercase tracking-wide"
                            >
                                {{ (user.plan || 'free') }} plan
                            </span>
                        </p>
                        <p class="mt-3 text-sm text-gray-300">
                            Tu pourras bientôt créer tes premiers liens trackés,
                            les relier à tes vidéos TikTok / Shorts / Reels,
                            et voir quelles sources génèrent vraiment des clics.
                        </p>
                    </div>
                </div>

                <!-- Cartes de stats placeholder -->
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">
                                Clics (7 derniers jours)
                            </p>
                            <p class="text-2xl font-semibold text-gray-100">
                                0
                            </p>
                            <p class="mt-1 text-xs text-gray-400">
                                Les premiers clics apparaîtront ici dès que tu partageras tes liens.
                            </p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">
                                Liens trackés
                            </p>
                            <p class="text-2xl font-semibold text-gray-100">
                                0
                            </p>
                            <p class="mt-1 text-xs text-gray-400">
                                On ajoutera bientôt la création de liens avec courte URL.
                            </p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">
                                Sources (vidéos / posts)
                            </p>
                            <p class="text-2xl font-semibold text-gray-100">
                                0
                            </p>
                            <p class="mt-1 text-xs text-gray-400">
                                Chaque lien sera rattaché à une vidéo, story ou post précis.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section “Prochaines étapes” -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sm text-gray-300 space-y-2">
                        <h2 class="text-base font-semibold text-gray-100">
                            Prochaines étapes dans LinkForge
                        </h2>
                        <ol class="list-decimal list-inside space-y-1 text-gray-300">
                            <li>Définir ton pseudo et ton plan (déjà fait 👌).</li>
                            <li>Créer tes premières <strong>sources</strong> (vidéos, stories, posts).</li>
                            <li>Créer des liens trackés par source.</li>
                            <li>Partager ces liens dans tes bios et descriptions.</li>
                            <li>Analyser les clics et optimiser ton contenu.</li>
                        </ol>
                        <p class="text-xs text-gray-500 mt-3">
                            Pour l’instant, ce dashboard est une V0. On va brancher
                            étape par étape les vraies données à ces blocs.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
