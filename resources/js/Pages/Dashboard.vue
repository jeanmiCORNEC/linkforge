<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    dailyClicks: {
        type: Array,
        default: () => [],
    },
});

// Max pour normaliser la hauteur des barres
const maxClicks = computed(() => {
    if (!props.dailyClicks.length) {
        return 0;
    }
    return Math.max(...props.dailyClicks.map((d) => d.count));
});
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
                <!-- Cartes de stats globales -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Liens -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 flex flex-col gap-1"
                    >
                        <span class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            Liens
                        </span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.links_count ?? 0 }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Liens trackés créés
                        </span>
                    </div>

                    <!-- Clics -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 flex flex-col gap-1"
                    >
                        <span class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            Clics
                        </span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.clicks_count ?? 0 }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Clics enregistrés sur tous vos liens
                        </span>
                    </div>

                    <!-- Campagnes -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 flex flex-col gap-1"
                    >
                        <span class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            Campagnes
                        </span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.campaigns_count ?? 0 }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Campagnes actives ou archivées
                        </span>
                    </div>

                    <!-- Sources -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 flex flex-col gap-1"
                    >
                        <span class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            Sources
                        </span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.sources_count ?? 0 }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Points d’entrée (TikTok, IG, newsletter, ...)
                        </span>
                    </div>
                </div>

                <!-- Graphique des clics sur 7 jours -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Clics sur les 7 derniers jours
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Toutes sources et toutes campagnes confondues.
                            </p>
                        </div>
                    </div>

                    <div v-if="!dailyClicks.length || maxClicks === 0" class="text-sm text-gray-500 dark:text-gray-400">
                        Pas encore de clics enregistrés. Partagez vos liens pour voir les stats ici 📈
                    </div>

                    <div
                        v-else
                        class="mt-4 h-40 flex items-end gap-2 border-t border-gray-200 dark:border-gray-700 pt-4"
                    >
                        <div
                            v-for="day in dailyClicks"
                            :key="day.date"
                            class="flex-1 flex flex-col items-center gap-1"
                        >
                            <!-- Barre -->
                            <div
                                class="w-full rounded-t-md bg-indigo-500 dark:bg-indigo-400 transition-all duration-200"
                                :style="{
                                    height: maxClicks
                                        ? Math.max((day.count / maxClicks) * 100, 5) + '%'
                                        : '5%',
                                }"
                            ></div>

                            <!-- Label jour -->
                            <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                {{ day.label }}
                            </div>

                            <!-- Count -->
                            <div class="text-[10px] text-gray-700 dark:text-gray-200">
                                {{ day.count }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA vers les zones principales -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <a
                        :href="route('links.index')"
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border border-transparent hover:border-indigo-500 transition"
                    >
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
                            Gérer vos liens
                        </h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Créez, activez/désactivez et supprimez vos liens trackés.
                        </p>
                    </a>

                    <a
                        :href="route('campaigns.index')"
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border border-transparent hover:border-indigo-500 transition"
                    >
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
                            Gérer vos campagnes
                        </h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Organisez vos promotions par campagne et archivez ce qui est terminé.
                        </p>
                    </a>

                    <a
                        :href="route('sources.index')"
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border border-transparent hover:border-indigo-500 transition"
                    >
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
                            Gérer vos sources
                        </h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Reliiez vos liens à TikTok, Instagram, newsletter, etc.
                        </p>
                    </a>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
