<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    link: {
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

const deviceLabel = (key) => {
    if (key === 'mobile') return 'Mobile';
    if (key === 'desktop') return 'Desktop';
    if (key === 'tablet') return 'Tablette';
    if (!key) return 'Inconnu';
    return key;
};

const browserLabel = (key) => key || 'Inconnu';
</script>

<template>
    <Head :title="`Stats du lien - ${link.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Statistiques du lien
                </h2>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ link.title }}
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- KPIs -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Clics
                        </div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.totalClicks }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Sur les {{ stats.period.days }} derniers jours
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Visiteurs uniques
                        </div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.uniqueVisitors }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Basé sur le visitor_hash
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                            Période
                        </div>
                        <div class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Derniers {{ stats.period.days }} jours
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Depuis le {{ stats.period.since }}
                        </p>
                    </div>
                </div>

                <!-- Graph clics / jour -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                        Clics par jour
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Évolution des clics pour ce lien.
                    </p>

                    <div class="flex items-end gap-3 h-40">
                        <div
                            v-for="day in stats.clicksPerDay"
                            :key="day.date"
                            class="flex-1 flex flex-col items-center justify-end gap-1"
                        >
                            <div
                                class="w-full rounded-t-md bg-indigo-500/80 dark:bg-indigo-400 transition-all"
                                :style="{ height: `${day.total === 0 ? 4 : (day.total * 8)}px` }"
                            ></div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400">
                                {{ day.date }}
                            </div>
                            <div class="text-[10px] text-gray-700 dark:text-gray-200">
                                {{ day.total }}
                            </div>
                        </div>
                        <div
                            v-if="stats.clicksPerDay.length === 0"
                            class="text-xs text-gray-400"
                        >
                            Pas encore de clics enregistrés sur la période.
                        </div>
                    </div>
                </div>

                <!-- Devices & browsers -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Appareils
                        </h3>
                        <ul class="space-y-1 text-xs text-gray-600 dark:text-gray-300">
                            <li
                                v-for="(count, key) in stats.devices"
                                :key="key"
                                class="flex justify-between"
                            >
                                <span>{{ deviceLabel(key) }}</span>
                                <span class="font-semibold">{{ count }}</span>
                            </li>
                            <li v-if="!Object.keys(stats.devices || {}).length" class="text-gray-400">
                                Pas encore de données.
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Navigateurs
                        </h3>
                        <ul class="space-y-1 text-xs text-gray-600 dark:text-gray-300">
                            <li
                                v-for="(count, key) in stats.browsers"
                                :key="key"
                                class="flex justify-between"
                            >
                                <span>{{ browserLabel(key) }}</span>
                                <span class="font-semibold">{{ count }}</span>
                            </li>
                            <li v-if="!Object.keys(stats.browsers || {}).length" class="text-gray-400">
                                Pas encore de données.
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
