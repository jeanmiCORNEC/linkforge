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

// Styles DA LinkForge
const cardClass =
    'rounded-xl border border-slate-800 bg-slate-900/70 shadow-md shadow-slate-950/40';
const clickableCardClass =
    cardClass +
    ' cursor-pointer hover:border-indigo-500/70 hover:bg-slate-900/90 hover:shadow-indigo-900/40 transition';

const primaryLinkCardClass = 'block ' + clickableCardClass;
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <!-- H1 typographique (36px) -->
            <h2 class="text-4xl font-bold text-slate-50 tracking-tight">
                Tableau de bord
            </h2>
        </template>

        <div class="py-8">
            <div class="w-[95%] mx-auto space-y-8">
                <!-- Cartes stats principales -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Liens -->
                    <div :class="cardClass" class="p-5">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Liens
                        </div>
                        <div class="mt-2 text-3xl font-semibold text-slate-50">
                            {{ stats.links_count }}
                        </div>
                        <p class="mt-2 text-xs text-slate-400">
                            Liens trackés créés
                        </p>
                    </div>

                    <!-- Clics + uniques -->
                    <div :class="cardClass" class="p-5">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Clics
                        </div>
                        <div class="mt-2 text-3xl font-semibold text-slate-50">
                            {{ stats.clicks_count }}
                        </div>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ stats.unique_visitors }} visiteurs uniques
                        </p>
                    </div>

                    <!-- Campagnes -->
                    <div :class="cardClass" class="p-5">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Campagnes
                        </div>
                        <div class="mt-2 text-3xl font-semibold text-slate-50">
                            {{ stats.campaigns_count }}
                        </div>
                        <p class="mt-2 text-xs text-slate-400">
                            Campagnes actives ou archivées
                        </p>
                    </div>

                    <!-- Sources -->
                    <div :class="cardClass" class="p-5">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            Sources
                        </div>
                        <div class="mt-2 text-3xl font-semibold text-slate-50">
                            {{ stats.sources_count }}
                        </div>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ stats.countries_count }} pays touchés
                        </p>
                    </div>
                </div>

                <!-- Graphique : clics sur 7 jours -->
                <div :class="cardClass" class="p-6">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-sm font-semibold text-slate-50">
                            Clics sur les 7 derniers jours
                        </h3>
                        <!-- Placeholder plus tard pour un sélecteur de période à la Stripe -->
                        <!-- <button class="text-xs text-slate-400 hover:text-slate-100">7 jours ▾</button> -->
                    </div>
                    <p class="text-xs text-slate-400 mb-4">
                        Toutes sources et toutes campagnes confondues.
                    </p>

                    <div class="flex items-end gap-3 h-40">
                        <div
                            v-for="day in dailyClicks"
                            :key="day.date"
                            class="flex-1 flex flex-col items-center justify-end gap-1"
                        >
                            <div
                                class="w-full rounded-t-md bg-indigo-500 transition-all"
                                :style="{ height: `${day.count === 0 ? 4 : day.count * 8}px` }"
                            />
                            <div class="text-[10px] text-slate-400">
                                {{ day.label }}
                            </div>
                            <div class="text-[10px] text-slate-200">
                                {{ day.count }}
                            </div>
                        </div>

                        <div
                            v-if="dailyClicks.length === 0"
                            class="text-xs text-slate-500"
                        >
                            Pas encore de clics sur les 7 derniers jours.
                        </div>
                    </div>
                </div>

                <!-- Résumé devices + navigateurs fusionnés (layout type Mailtrap/Stripe) -->
                <div :class="cardClass" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10">
                        <!-- Appareils -->
                        <section>
                            <h3 class="text-sm font-semibold text-slate-50 mb-2">
                                Appareils
                            </h3>
                            <ul class="space-y-1 text-xs text-slate-200">
                                <li
                                    v-for="(count, key) in stats.devices_breakdown"
                                    :key="key"
                                    class="flex justify-between"
                                >
                                    <span>{{ deviceLabel(key) }}</span>
                                    <span class="font-semibold">{{ count }}</span>
                                </li>
                                <li
                                    v-if="!Object.keys(stats.devices_breakdown || {}).length"
                                    class="text-slate-500"
                                >
                                    Pas encore de données.
                                </li>
                            </ul>
                        </section>

                        <!-- Navigateurs -->
                        <section class="md:border-l md:border-slate-800 md:pl-6">
                            <h3 class="text-sm font-semibold text-slate-50 mb-2">
                                Navigateurs
                            </h3>
                            <ul class="space-y-1 text-xs text-slate-200">
                                <li
                                    v-for="(count, key) in stats.browsers_breakdown"
                                    :key="key"
                                    class="flex justify-between"
                                >
                                    <span>{{ browserLabel(key) }}</span>
                                    <span class="font-semibold">{{ count }}</span>
                                </li>
                                <li
                                    v-if="!Object.keys(stats.browsers_breakdown || {}).length"
                                    class="text-slate-500"
                                >
                                    Pas encore de données.
                                </li>
                            </ul>
                        </section>
                    </div>
                </div>

                <!-- Raccourcis -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <InertiaLink
                        :href="route('links.index')"
                        :class="primaryLinkCardClass"
                    >
                        <div class="p-5">
                            <div class="text-sm font-semibold text-slate-50 mb-1">
                                Gérer vos liens
                            </div>
                            <p class="text-xs text-slate-400">
                                Créez, activez/désactivez et supprimez vos liens trackés.
                            </p>
                        </div>
                    </InertiaLink>

                    <InertiaLink
                        :href="route('campaigns.index')"
                        :class="primaryLinkCardClass"
                    >
                        <div class="p-5">
                            <div class="text-sm font-semibold text-slate-50 mb-1">
                                Gérer vos campagnes
                            </div>
                            <p class="text-xs text-slate-400">
                                Organisez vos promos par campagne et archivez ce qui est terminé.
                            </p>
                        </div>
                    </InertiaLink>

                    <InertiaLink
                        :href="route('sources.index')"
                        :class="primaryLinkCardClass"
                    >
                        <div class="p-5">
                            <div class="text-sm font-semibold text-slate-50 mb-1">
                                Gérer vos sources
                            </div>
                            <p class="text-xs text-slate-400">
                                Reliez vos liens à TikTok, Instagram, newsletter, etc.
                            </p>
                        </div>
                    </InertiaLink>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
