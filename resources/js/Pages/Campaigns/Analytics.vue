<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link as InertiaLink, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    campaign: {
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

const currentDays = computed(() => props.filters.days ?? 7);

const changeDays = (days) => {
    router.get(
        route('campaigns.analytics.show', {
            campaign: props.campaign.id,
            days,
        }),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const hasClicks = computed(() => (props.stats?.totalClicks ?? 0) > 0);

// Helpers pour affichage devices / browsers
const devicesList = computed(() => {
    const raw = props.stats?.devices ?? {};
    return Object.entries(raw).map(([device, total]) => ({
        device: device || 'Unknown',
        total,
    }));
});

const browsersList = computed(() => {
    const raw = props.stats?.browsers ?? {};
    return Object.entries(raw).map(([browser, total]) => ({
        browser: browser || 'Unknown',
        total,
    }));
});

const clicksPerDay = computed(() => props.stats?.clicksPerDay ?? []);
</script>

<template>
    <Head :title="`Analytics – ${campaign.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Analytics – Campagne
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ campaign.name }}
                    </p>
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">
                        Période :
                    </span>

                    <button
                        type="button"
                        class="px-3 py-1 rounded-full border text-xs"
                        :class="currentDays === 7
                            ? 'bg-indigo-600 text-white border-indigo-600'
                            : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60'"
                        @click="changeDays(7)"
                    >
                        7 jours
                    </button>

                    <button
                        type="button"
                        class="px-3 py-1 rounded-full border text-xs"
                        :class="currentDays === 30
                            ? 'bg-indigo-600 text-white border-indigo-600'
                            : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60'"
                        @click="changeDays(30)"
                    >
                        30 jours
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Résumé -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total clics
                            </div>
                            <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ stats.totalClicks ?? 0 }}
                            </div>
                            <div class="mt-1 text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                Sur les {{ stats.period?.days ?? currentDays }} derniers jours
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Visiteurs uniques
                            </div>
                            <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ stats.uniqueVisitors ?? 0 }}
                            </div>
                            <div class="mt-1 text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                Basé sur le hash visiteur
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Période analysée
                            </div>
                            <div class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                Depuis le {{ stats.period?.since ?? '—' }}
                            </div>
                            <div class="mt-1 text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                Paramètre "days" = {{ currentDays }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Détails -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Clics par jour -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-4">
                            Clics par jour
                        </h3>

                        <div v-if="!hasClicks" class="text-sm text-gray-500 dark:text-gray-400">
                            Aucun clic enregistré pour cette campagne sur la période sélectionnée.
                        </div>

                        <div v-else class="space-y-2 text-sm">
                            <div
                                v-for="day in clicksPerDay"
                                :key="day.date"
                                class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-1 last:border-b-0"
                            >
                                <span class="text-gray-700 dark:text-gray-200">
                                    {{ day.date }}
                                </span>
                                <span class="font-semibold text-gray-900 dark:text-gray-50">
                                    {{ day.total }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Devices & browsers -->
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">
                                Devices
                            </h3>

                            <div v-if="!devicesList.length" class="text-sm text-gray-500 dark:text-gray-400">
                                Aucune donnée device.
                            </div>

                            <ul v-else class="space-y-2 text-sm">
                                <li
                                    v-for="item in devicesList"
                                    :key="item.device"
                                    class="flex items-center justify-between"
                                >
                                    <span class="text-gray-700 dark:text-gray-200">
                                        {{ item.device }}
                                    </span>
                                    <span class="font-medium text-gray-900 dark:text-gray-50">
                                        {{ item.total }}
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">
                                Navigateurs
                            </h3>

                            <div v-if="!browsersList.length" class="text-sm text-gray-500 dark:text-gray-400">
                                Aucune donnée navigateur.
                            </div>

                            <ul v-else class="space-y-2 text-sm">
                                <li
                                    v-for="item in browsersList"
                                    :key="item.browser"
                                    class="flex items-center justify-between"
                                >
                                    <span class="text-gray-700 dark:text-gray-200">
                                        {{ item.browser }}
                                    </span>
                                    <span class="font-medium text-gray-900 dark:text-gray-50">
                                        {{ item.total }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-start">
                    <InertiaLink
                        :href="route('campaigns.index')"
                        class="inline-flex items-center text-xs text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        ← Retour aux campagnes
                    </InertiaLink>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
