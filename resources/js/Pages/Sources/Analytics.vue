<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const { source, stats, filters } = defineProps({
    source: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

// Sélecteur de période (7 / 30 jours)
const selectedDays = ref(filters.days || 7);

const changeDays = () => {
    router.get(
        route('sources.analytics.show', source.id),
        { days: selectedDays.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

// Helpers d'affichage

const totalClicks = computed(() => stats.total_clicks ?? 0);
const uniqueVisitors = computed(() => stats.unique_visitors ?? 0);

const devices = computed(() => stats.devices_breakdown || {});
const browsers = computed(() => stats.browsers_breakdown || {});
const clicksPerDay = computed(() => stats.clicks_per_day || []);

const hasClicks = computed(() => totalClicks.value > 0);

const formatDayLabel = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('fr-FR', {
        weekday: 'short',
        day: 'numeric',
    });
};

const goBack = () => {
    router.get(route('sources.index'));
};
</script>

<template>
    <Head :title="`Stats source - ${source.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Statistiques de la source
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Source liée à vos campagnes et liens trackés.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium border border-gray-300
                           dark:border-gray-600 text-gray-700 dark:text-gray-200
                           hover:bg-gray-50 dark:hover:bg-gray-700/60 transition"
                    @click="goBack"
                >
                    ← Retour aux sources
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Header source + filtre période -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ source.name }}
                                </h3>
                                <span
                                    v-if="source.platform"
                                    class="px-2 py-0.5 rounded-full text-[10px] font-medium
                                           bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-200"
                                >
                                    {{ source.platform }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Période analysée : depuis le {{ stats.period?.since }} ({{ stats.period?.days }} jours)
                            </p>
                        </div>

                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Période :</span>
                            <select
                                v-model.number="selectedDays"
                                @change="changeDays"
                                class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900
                                       dark:text-gray-100 text-sm"
                            >
                                <option :value="7">7 jours</option>
                                <option :value="30">30 jours</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Cartes principales -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Clics
                        </div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ totalClicks }}
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Clics totaux sur cette source
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Visiteurs uniques
                        </div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ uniqueVisitors }}
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Basé sur le hash visiteur (IP + user-agent)
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Appareils
                        </div>
                        <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ Object.keys(devices).length || 0 }}
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Types d’appareils détectés
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Navigateurs
                        </div>
                        <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ Object.keys(browsers).length || 0 }}
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Navigateurs utilisés
                        </p>
                    </div>
                </div>

                <!-- Graphique clics / jour -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                        Clics sur la période
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Évolution du trafic pour cette source.
                    </p>

                    <div v-if="hasClicks" class="flex items-end gap-3 h-40">
                        <div
                            v-for="day in clicksPerDay"
                            :key="day.date"
                            class="flex-1 flex flex-col items-center justify-end gap-1"
                        >
                            <div
                                class="w-full rounded-t-md bg-indigo-500/80 dark:bg-indigo-400 transition-all"
                                :style="{ height: `${day.total === 0 ? 4 : (day.total * 8)}px` }"
                            ></div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                {{ formatDayLabel(day.date) }}
                            </div>
                            <div class="text-[10px] text-gray-700 dark:text-gray-200">
                                {{ day.total }}
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-xs text-gray-500 dark:text-gray-400">
                        Pas encore de clics pour cette source sur la période sélectionnée.
                    </div>
                </div>

                <!-- Breakdown appareils + navigateurs -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Devices -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Appareils
                        </h3>
                        <ul class="space-y-1 text-xs text-gray-600 dark:text-gray-300">
                            <li
                                v-for="(count, key) in devices"
                                :key="key"
                                class="flex justify-between"
                            >
                                <span>
                                    <span v-if="key === 'mobile'">Mobile</span>
                                    <span v-else-if="key === 'desktop'">Desktop</span>
                                    <span v-else-if="key === 'tablet'">Tablette</span>
                                    <span v-else-if="key === 'unknown'">Inconnu</span>
                                    <span v-else>{{ key }}</span>
                                </span>
                                <span class="font-semibold">{{ count }}</span>
                            </li>
                            <li
                                v-if="!Object.keys(devices || {}).length"
                                class="text-gray-400"
                            >
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
                                v-for="(count, key) in browsers"
                                :key="key"
                                class="flex justify-between"
                            >
                                <span>
                                    <span v-if="key === 'unknown'">Inconnu</span>
                                    <span v-else>{{ key }}</span>
                                </span>
                                <span class="font-semibold">{{ count }}</span>
                            </li>
                            <li
                                v-if="!Object.keys(browsers || {}).length"
                                class="text-gray-400"
                            >
                                Pas encore de données.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
