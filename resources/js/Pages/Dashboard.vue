<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link as InertiaLink } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    dailyClicks: {
        type: Array,
        required: true,
    },
});

const deviceLabel = (key) => {
    if (key === 'mobile') return 'Mobile';
    if (key === 'desktop') return 'Desktop';
    if (key === 'tablet') return 'Tablette';
    if (key === 'unknown') return 'Inconnu';
    return key;
};

const browserLabel = (key) => {
    if (key === 'unknown') return 'Inconnu';
    return key;
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Tableau de bord
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Cartes stats principales -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Liens -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Liens
                        </div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.links_count }}
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Liens trackés créés
                        </p>
                    </div>

                    <!-- Clics + uniques -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Clics
                        </div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.clicks_count }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ stats.unique_visitors }} visiteurs uniques
                        </p>
                    </div>

                    <!-- Campagnes -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Campagnes
                        </div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.campaigns_count }}
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Campagnes actives ou archivées
                        </p>
                    </div>

                    <!-- Sources -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Sources
                        </div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.sources_count }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ stats.countries_count }} pays touchés
                        </p>
                    </div>
                </div>

                <!-- Graphique simple : clics sur 7 jours -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                        Clics sur les 7 derniers jours
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Toutes sources et toutes campagnes confondues.
                    </p>

                    <div class="flex items-end gap-3 h-40">
                        <div
                            v-for="day in dailyClicks"
                            :key="day.date"
                            class="flex-1 flex flex-col items-center justify-end gap-1"
                        >
                            <div
                                class="w-full rounded-t-md bg-indigo-500/80 dark:bg-indigo-400 transition-all"
                                :style="{ height: `${day.count === 0 ? 4 : (day.count * 8)}px` }"
                            ></div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                {{ day.label }}
                            </div>
                            <div class="text-[10px] text-gray-700 dark:text-gray-200">
                                {{ day.count }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Résumé devices + navigateurs -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Devices -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Appareils
                        </h3>
                        <ul class="space-y-1 text-xs text-gray-600 dark:text-gray-300">
                            <li
                                v-for="(count, key) in stats.devices_breakdown"
                                :key="key"
                                class="flex justify-between"
                            >
                                <span>{{ deviceLabel(key) }}</span>
                                <span class="font-semibold">{{ count }}</span>
                            </li>
                            <li v-if="!Object.keys(stats.devices_breakdown || {}).length" class="text-gray-400">
                                Pas encore de données.
                            </li>
                        </ul>
                    </div>

                    <!-- Navigateurs -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Navigateurs
                        </h3>
                        <ul class="space-y-1 text-xs text-gray-600 dark:text-gray-300">
                            <li
                                v-for="(count, key) in stats.browsers_breakdown"
                                :key="key"
                                class="flex justify-between"
                            >
                                <span>{{ browserLabel(key) }}</span>
                                <span class="font-semibold">{{ count }}</span>
                            </li>
                            <li v-if="!Object.keys(stats.browsers_breakdown || {}).length" class="text-gray-400">
                                Pas encore de données.
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Raccourcis -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InertiaLink
                        :href="route('links.index')"
                        class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition"
                    >
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                            Gérer vos liens
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Créez, activez/désactivez et supprimez vos liens trackés.
                        </p>
                    </InertiaLink>

                    <InertiaLink
                        :href="route('campaigns.index')"
                        class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition"
                    >
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                            Gérer vos campagnes
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Organisez vos promos par campagne et archivez ce qui est terminé.
                        </p>
                    </InertiaLink>

                    <InertiaLink
                        :href="route('sources.index')"
                        class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition"
                    >
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                            Gérer vos sources
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Reliez vos liens à TikTok, Instagram, newsletter, etc.
                        </p>
                    </InertiaLink>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
