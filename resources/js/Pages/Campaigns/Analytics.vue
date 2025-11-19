<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link as InertiaLink, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

// --- Période sélectionnée ---
const currentDays = computed(() => Number(props.filters.days ?? 7));
const customDays = ref(currentDays.value);

// Helpers formatage
const formatDateFr = (value) => {
    if (!value) return '';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};

const formatDayLabel = (value) => {
    if (!value) return '';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
    });
};

const deviceLabel = (key) => {
    if (key === 'mobile') return 'Mobile';
    if (key === 'desktop') return 'Desktop';
    if (key === 'tablet') return 'Tablette';
    if (!key) return 'Inconnu';
    return key;
};

const browserLabel = (key) => key || 'Inconnu';

const clicksPerDay = computed(() => props.stats?.clicksPerDay ?? []);
const topSources = computed(() => props.stats?.topSources ?? []);
const topLinks = computed(() => props.stats?.topLinks ?? []);
const topDays = computed(() => props.stats?.topDays ?? []);
const formatPercentage = (value) => `${value ?? 0}%`;

const hourlyHeatmap = computed(() => props.stats?.hourlyHeatmap ?? []);
const heatmapHours = Array.from({ length: 24 }, (_, index) => index);
const weekdayLabels = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
const weekdayOrder = [1, 2, 3, 4, 5, 6, 0];

const heatmapMatrix = computed(() => {
    const data = hourlyHeatmap.value;
    const map = {};
    let max = 0;

    data.forEach((entry) => {
        const weekday = entry.weekday ?? new Date(entry.date).getDay();
        if (!map[weekday]) {
            map[weekday] = {
                label: entry.weekdayLabel || weekdayLabels[weekday] || '',
                values: {},
            };
        }
        map[weekday].values[entry.hour] = entry.total;
        if (entry.total > max) {
            max = entry.total;
        }
    });

    const rows = weekdayOrder.map((day) => ({
        weekday: day,
        label: map[day]?.label ?? weekdayLabels[day],
        values: heatmapHours.map((hour) => map[day]?.values?.[hour] ?? 0),
    }));

    return { rows, max };
});

const heatmapCellClass = (value) => {
    const max = heatmapMatrix.value.max;
    if (!max || value === 0) {
        return 'bg-slate-900/40 text-slate-500';
    }
    const ratio = value / max;
    if (ratio < 0.33) return 'bg-indigo-900 text-slate-200';
    if (ratio < 0.66) return 'bg-indigo-700 text-white';
    return 'bg-indigo-500 text-white';
};

// --- Changement de période (7 / 30 / perso) ---
const changePeriod = (days) => {
    customDays.value = days;

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

const applyCustomPeriod = () => {
    const val = Number(customDays.value) || 7;
    changePeriod(val);
};

const exportAnalytics = () => {
    window.location = route('campaigns.analytics.export', {
        campaign: props.campaign.id,
        days: currentDays.value,
    });
};

/* ---------- Styles DA LinkForge (mêmes que link analytics) ---------- */

const shellCardClass =
    'relative rounded-3xl border border-slate-800 bg-slate-950/80 px-6 py-5 shadow-xl shadow-indigo-900/30';

const cardClass =
    'rounded-xl border border-slate-800 bg-slate-950/70 p-5 shadow-xl shadow-indigo-900/30';

const bigCardClass =
    'rounded-xl border border-slate-800 bg-slate-950/70 p-6 shadow-xl shadow-indigo-900/30';

const primaryButtonClass =
    'inline-flex items-center rounded-md bg-indigo-500 px-3 py-1 text-xs md:text-sm font-semibold text-white shadow-xl shadow-indigo-900/30 hover:bg-indigo-400 disabled:opacity-50 transition';

const periodPillGroupClass =
    'inline-flex items-center rounded-full bg-slate-900/80 border border-slate-700 p-1';

const periodPillBaseClass =
    'px-3 py-1 rounded-full text-xs md:text-sm font-medium transition';

const deltaLabel = (value) => {
    if (value === 0) return 'Stable vs période précédente';
    return `${value > 0 ? '+' : ''}${value}% vs période précédente`;
};

const deltaClass = (value) => {
    if (value > 0) {
        return 'text-emerald-300 bg-emerald-900/30 border border-emerald-500/30';
    }
    if (value < 0) {
        return 'text-rose-300 bg-rose-900/30 border border-rose-500/30';
    }
    return 'text-slate-300 bg-slate-800/60 border border-slate-700';
};

const totalDelta = computed(() => props.stats.delta?.totalClicks ?? 0);
const uniqueDelta = computed(() => props.stats.delta?.uniqueVisitors ?? 0);
</script>

<template>
    <Head :title="`Stats campagne - ${campaign.name}`" />

    <AuthenticatedLayout>
        <!-- HEADER ANALYTICS -->
        <section class="border-b border-slate-800 bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900">
            <!-- même largeur que la page liens -->
            <div class="w-[95%] mx-auto pt-8 pb-10">
                <div :class="shellCardClass + ' flex flex-col md:flex-row md:items-center md:justify-between gap-6'">
                    <!-- Bloc gauche : titre -->
                    <div class="space-y-3">
                        <p
                            class="inline-flex items-center rounded-full border border-indigo-500/40 bg-indigo-500/10 px-3 py-1 text-xs md:text-sm font-medium text-indigo-200 uppercase tracking-[0.15em]"
                        >
                            Analytics – Campagne
                        </p>

                        <div>
                            <h1 class="text-xl md:text-2xl font-semibold tracking-tight">
                                {{ campaign.name }}
                            </h1>
                        </div>

                        <InertiaLink
                            :href="route('campaigns.index')"
                            class="inline-flex items-center gap-1 text-xs md:text-sm text-slate-400 hover:text-indigo-300 transition"
                        >
                            <span>←</span>
                            <span>Retour aux campagnes</span>
                        </InertiaLink>
                    </div>

                    <!-- Bloc droite : période -->
                    <div class="flex flex-col items-start md:items-end gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="uppercase tracking-[0.15em] text-slate-400 text-[10px]">
                                Période
                            </span>

                            <div :class="periodPillGroupClass">
                                <button
                                    type="button"
                                    :class="[
                                        periodPillBaseClass,
                                        currentDays === 7
                                            ? 'bg-indigo-500 text-white shadow shadow-indigo-500/40'
                                            : 'text-slate-300 hover:text-white',
                                    ]"
                                    @click="changePeriod(7)"
                                >
                                    7 jours
                                </button>
                                <button
                                    type="button"
                                    :class="[
                                        periodPillBaseClass,
                                        currentDays === 30
                                            ? 'bg-indigo-500 text-white shadow shadow-indigo-500/40'
                                            : 'text-slate-300 hover:text-white',
                                    ]"
                                    @click="changePeriod(30)"
                                >
                                    30 jours
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-xs md:text-sm">
                            <span class="text-slate-400">Perso (jours) :</span>
                            <input
                                v-model="customDays"
                                type="number"
                                min="1"
                                class="w-16 rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs md:text-sm text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            />
                            <button
                                type="button"
                                :class="primaryButtonClass"
                                @click="applyCustomPeriod"
                            >
                                Appliquer
                            </button>
                        </div>

                        <p class="text-xs md:text-sm text-slate-400">
                            Analyse des {{ stats.period?.days ?? currentDays }} derniers jours
                            • Depuis le {{ formatDateFr(stats.period?.since) }}
                        </p>

                        <button
                            type="button"
                            :class="primaryButtonClass"
                            @click="exportAnalytics"
                        >
                            Exporter CSV
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENU ANALYTICS -->
        <main class="w-[95%] mx-auto pt-8 pb-12 space-y-8">
            <!-- KPIs : même layout que liens -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div :class="cardClass">
                    <p class="text-xs md:text-sm uppercase tracking-[0.15em] text-slate-400">
                        Total clics
                    </p>
                    <p class="mt-2 text-3xl font-bold">
                        {{ stats.totalClicks ?? 0 }}
                    </p>
                    <p class="mt-1 text-xs md:text-sm text-slate-400">
                        Sur les {{ stats.period?.days ?? currentDays }} derniers jours
                    </p>
                    <p
                        class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-[11px] font-medium"
                        :class="deltaClass(totalDelta)"
                    >
                        {{ deltaLabel(totalDelta) }}
                    </p>
                </div>

                <div :class="cardClass">
                    <p class="text-xs md:text-sm uppercase tracking-[0.15em] text-slate-400">
                        Visiteurs uniques
                    </p>
                    <p class="mt-2 text-3xl font-bold">
                        {{ stats.uniqueVisitors ?? 0 }}
                    </p>
                    <p class="mt-1 text-xs md:text-sm text-slate-400">
                        Basé sur le hash visiteur (IP + user-agent)
                    </p>
                    <p
                        class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-[11px] font-medium"
                        :class="deltaClass(uniqueDelta)"
                    >
                        {{ deltaLabel(uniqueDelta) }}
                    </p>
                </div>

                <div :class="cardClass">
                    <p class="text-xs md:text-sm uppercase tracking-[0.15em] text-slate-400">
                        Période analysée
                    </p>
                    <p class="mt-2 text-lg font-semibold">
                        Derniers {{ stats.period?.days ?? currentDays }} jours
                    </p>
                    <p class="mt-1 text-xs md:text-sm text-slate-400">
                        Depuis le {{ formatDateFr(stats.period?.since) }}
                    </p>
                </div>
            </section>

            <!-- Tops : sources / liens / meilleurs jours -->
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div :class="bigCardClass">
                    <h3 class="text-sm font-semibold">
                        Top sources
                    </h3>
                    <p class="text-xs text-slate-400 mb-4">
                        Les emplacements de cette campagne qui génèrent le plus de clics.
                    </p>
                    <ul class="space-y-3">
                        <li
                            v-for="source in topSources"
                            :key="source.id"
                            class="rounded-lg border border-slate-800 bg-slate-900/60 p-3 text-xs md:text-sm text-slate-100"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold truncate">
                                        {{ source.name }}
                                    </p>
                                    <p class="text-[11px] text-slate-400">
                                        {{ formatPercentage(source.percentage) }} des clics
                                    </p>
                                </div>
                                <span class="text-xl font-bold">
                                    {{ source.total }}
                                </span>
                            </div>
                        </li>
                        <li v-if="!topSources.length" class="text-xs text-slate-500">
                            Pas encore de sources actives sur cette période.
                        </li>
                    </ul>
                </div>

                <div :class="bigCardClass">
                    <h3 class="text-sm font-semibold">
                        Top liens trackés
                    </h3>
                    <p class="text-xs text-slate-400 mb-4">
                        Copiez ces liens courts dans vos placements pour garder le tracking précis.
                    </p>
                    <ul class="space-y-3">
                        <li
                            v-for="link in topLinks"
                            :key="link.id"
                            class="rounded-lg border border-slate-800 bg-slate-900/60 p-3 text-xs md:text-sm text-slate-100"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold truncate">
                                        {{ link.title }}
                                    </p>
                                    <p class="text-[11px] text-slate-400">
                                        {{ formatPercentage(link.percentage) }} des clics
                                    </p>
                                </div>
                                <span class="text-xl font-bold">
                                    {{ link.total }}
                                </span>
                            </div>
                        </li>
                        <li v-if="!topLinks.length" class="text-xs text-slate-500">
                            Aucun lien tracké n'a encore généré de clics.
                        </li>
                    </ul>
                </div>

                <div :class="bigCardClass">
                    <h3 class="text-sm font-semibold">
                        Jours les plus performants
                    </h3>
                    <p class="text-xs text-slate-400 mb-4">
                        Identifiez les dates à fort engagement pour caler vos prochains contenus.
                    </p>
                    <ul class="space-y-3">
                        <li
                            v-for="day in topDays"
                            :key="day.date"
                            class="rounded-lg border border-slate-800 bg-slate-900/60 p-3 text-xs md:text-sm text-slate-100 flex items-center justify-between gap-3"
                        >
                            <div>
                                <p class="font-semibold">
                                    {{ formatDateFr(day.date) }}
                                </p>
                                <p class="text-[11px] text-slate-400">
                                    {{ formatPercentage(day.percentage) }} des clics
                                </p>
                            </div>
                            <span class="text-xl font-bold">
                                {{ day.total }}
                            </span>
                        </li>
                        <li v-if="!topDays.length" class="text-xs text-slate-500">
                            Pas encore assez de clics pour dégager une tendance.
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Graph clics / jour (full width) -->
            <section :class="bigCardClass">
                <h3 class="text-sm font-semibold mb-1">
                    Clics par jour
                </h3>
                <p class="text-xs md:text-sm text-slate-400 mb-4">
                    Évolution des clics pour cette campagne sur la période sélectionnée.
                </p>

                <div class="flex items-end gap-3 h-40">
                    <template v-if="clicksPerDay.length">
                        <div
                            v-for="day in clicksPerDay"
                            :key="day.date"
                            class="flex-1 flex flex-col items-center justify-end gap-1"
                        >
                            <div
                                class="w-full rounded-t-md bg-indigo-500/80 transition-all"
                                :style="{ height: `${day.total === 0 ? 4 : Math.min(day.total * 12, 120)}px` }"
                            />
                            <div class="text-[10px] text-slate-400">
                                {{ formatDayLabel(day.date) }}
                            </div>
                            <div class="text-[10px] text-slate-200">
                                {{ day.total }}
                            </div>
                        </div>
                    </template>

                    <div v-else class="text-xs text-slate-400">
                        Aucun clic enregistré pour cette campagne sur la période sélectionnée.
                    </div>
                </div>
            </section>

            <!-- Devices & navigateurs : même largeur, 2 colonnes -->
            <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div :class="bigCardClass">
                    <h3 class="text-sm font-semibold mb-2">
                        Appareils
                    </h3>
                    <ul class="space-y-1 text-xs text-slate-200">
                        <li
                            v-for="(count, key) in stats.devices"
                            :key="key"
                            class="flex justify-between"
                        >
                            <span class="text-slate-300">
                                {{ deviceLabel(key) }}
                            </span>
                            <span class="font-semibold">
                                {{ count }}
                            </span>
                        </li>
                        <li
                            v-if="!Object.keys(stats.devices || {}).length"
                            class="text-slate-500"
                        >
                            Pas encore de données.
                        </li>
                    </ul>
                </div>

                <div :class="bigCardClass">
                    <h3 class="text-sm font-semibold mb-2">
                        Navigateurs
                    </h3>
                    <ul class="space-y-1 text-xs text-slate-200">
                        <li
                            v-for="(count, key) in stats.browsers"
                            :key="key"
                            class="flex justify-between"
                        >
                            <span class="text-slate-300">
                                {{ browserLabel(key) }}
                            </span>
                            <span class="font-semibold">
                                {{ count }}
                            </span>
                        </li>
                        <li
                            v-if="!Object.keys(stats.browsers || {}).length"
                            class="text-slate-500"
                        >
                            Pas encore de données.
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Heatmap -->
            <section :class="bigCardClass">
                <h3 class="text-sm font-semibold">
                    Heatmap horaire
                </h3>
                <p class="text-xs text-slate-400 mb-4">
                    Constatez les créneaux (jour × heure) où la campagne est la plus performante.
                </p>

                <div v-if="heatmapMatrix.max" class="overflow-x-auto">
                    <div class="space-y-2 min-w-[600px]">
                        <div
                            v-for="row in heatmapMatrix.rows"
                            :key="row.weekday"
                            class="flex items-center gap-2 text-[11px]"
                        >
                            <span class="w-10 text-right text-slate-400">
                                {{ row.label }}
                            </span>
                            <div
                                class="flex-1 grid gap-1"
                                :style="{ gridTemplateColumns: 'repeat(24, minmax(0, 1fr))' }"
                            >
                                <div
                                    v-for="(value, index) in row.values"
                                    :key="index"
                                    class="h-5 rounded"
                                    :class="heatmapCellClass(value)"
                                    :title="`${value} clics à ${index}h`"
                                >
                                    <span class="sr-only">
                                        {{ value }} clics à {{ index }}h
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-4">
                        Plus la couleur est lumineuse, plus le créneau est performant.
                    </p>
                </div>

                <p v-else class="text-xs text-slate-500">
                    Pas encore assez de clics pour générer cette heatmap.
                </p>
            </section>

        </main>
    </AuthenticatedLayout>
</template>
