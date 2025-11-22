<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link as InertiaLink } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    dailyClicks: {
        type: Array,
        required: true,
    },
    hourlyHeatmap: {
        type: Array,
        required: true,
    },
    topCampaigns: {
        type: Array,
        required: true,
    },
    topSources: {
        type: Array,
        required: true,
    },
    topLinks: {
        type: Array,
        required: true,
    },
    globalDelta: {
        type: Object,
        required: true,
    },
    features: {
        type: Object,
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

const deltaLabel = (value) => {
    if (value === 0) return 'Stable vs période précédente';
    return `${value > 0 ? '+' : ''}${value}% vs période précédente`;
};

const deltaClass = (value) => {
    if (value > 0) {
        return 'text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-500/30';
    }
    if (value < 0) {
        return 'text-rose-700 dark:text-rose-300 bg-rose-100 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-500/30';
    }
    return 'text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700';
};

/* ---------- Styles DA LinkForge ---------- */
// Card de base : même DA que les pages analytics (glow indigo)
// Card de base : Matte Pro (Linear/Stripe style)
const cardClass =
    'rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm dark:shadow-black/20';

// Variante "grande" avec padding
const bigCardClass = cardClass + ' p-6';

// Cards cliquables (raccourcis)
const clickableCardClass =
    cardClass +
    ' cursor-pointer hover:border-indigo-300 dark:hover:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 hover:shadow-md transition';

const primaryLinkCardClass = 'block ' + clickableCardClass;

const formatPercentage = (value) => `${value ?? 0}%`;

const analyticsButtonClass =
    'inline-flex items-center gap-1 rounded-md border border-indigo-200 dark:border-indigo-500 px-3 py-1 text-[11px] font-semibold text-indigo-700 dark:text-indigo-200 bg-indigo-50 dark:bg-transparent hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition';

const heatmapHours = Array.from({ length: 24 }, (_, index) => index);
const weekdayLabels = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
const weekdayOrder = [1, 2, 3, 4, 5, 6, 0];

const heatmapMatrix = computed(() => {
    const data = props.hourlyHeatmap || [];
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
        return 'bg-slate-100 dark:bg-slate-900/40 text-slate-400 dark:text-slate-500';
    }
    const ratio = value / max;
    if (ratio < 0.33) return 'bg-indigo-200 dark:bg-indigo-900 text-indigo-900 dark:text-slate-200';
    if (ratio < 0.66) return 'bg-indigo-400 dark:bg-indigo-700 text-white';
    return 'bg-indigo-600 dark:bg-indigo-500 text-white';
};

const deviceSegments = computed(() => {
    const raw = props.stats.devices_breakdown || {};
    const total = Object.values(raw).reduce((sum, value) => sum + Number(value || 0), 0);
    const order = ['mobile', 'desktop', 'tablet', 'unknown'];

    return order.map((key) => {
        const count = Number(raw[key] ?? 0);
        const percentage = total > 0 ? Math.round((count / total) * 100) : 0;

        return {
            key,
            label: deviceLabel(key),
            count,
            percentage,
        };
    });
});

const topCountries = computed(() => props.stats.top_countries ?? []);

const exportMonth = ref(new Date().toISOString().slice(0, 7));
const exportMonthlyTraffic = () => {
    if (! exportMonth.value) {
        return;
    }

    window.location = route('exports.traffic.monthly', {
        month: exportMonth.value,
    });
    window.location = route('exports.traffic.monthly', {
        month: exportMonth.value,
    });
};

const isZeroData = computed(() => {
    return props.stats.links_count === 0 && props.stats.campaigns_count === 0;
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-4xl font-bold text-slate-900 dark:text-slate-50 tracking-tight">
                Tableau de bord
            </h2>
        </template>

        <div class="py-8">
            <main class="w-[95%] mx-auto pt-2 pb-12 space-y-8">
                <!-- ONBOARDING CHECKLIST (ZERO DATA) -->
                <section v-if="isZeroData" class="max-w-2xl mx-auto">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-indigo-100 dark:border-indigo-900/30 p-8 text-center space-y-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white">Bienvenue sur LinkForge ! 🚀</h3>
                        <p class="text-slate-600 dark:text-slate-300">
                            Votre tableau de bord est vide pour le moment. Voici les étapes pour démarrer :
                        </p>
                        
                        <div class="text-left space-y-3 max-w-md mx-auto bg-slate-50 dark:bg-slate-950/50 p-6 rounded-xl border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 border-slate-300 dark:border-slate-600 flex items-center justify-center">
                                    <span class="w-2 h-2 rounded-full bg-transparent"></span>
                                </div>
                                <span class="text-slate-700 dark:text-slate-300">1. Créez votre première <strong>Campagne</strong> (ex: "Lancement Ebook")</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 border-slate-300 dark:border-slate-600 flex items-center justify-center">
                                    <span class="w-2 h-2 rounded-full bg-transparent"></span>
                                </div>
                                <span class="text-slate-700 dark:text-slate-300">2. Ajoutez un <strong>Lien</strong> dans cette campagne</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 border-slate-300 dark:border-slate-600 flex items-center justify-center">
                                    <span class="w-2 h-2 rounded-full bg-transparent"></span>
                                </div>
                                <span class="text-slate-700 dark:text-slate-300">3. Partagez le lien court sur vos réseaux</span>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-center gap-4">
                            <InertiaLink :href="route('campaigns.index')" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/30 transition">
                                Créer une Campagne
                            </InertiaLink>
                            <InertiaLink :href="route('links.index')" class="px-6 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-semibold rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                Créer un Lien
                            </InertiaLink>
                        </div>
                    </div>
                </section>

                <!-- Cartes stats principales -->
                <section v-else class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Liens -->
                    <div :class="cardClass + ' p-5'">
                        <div class="text-xs font-medium uppercase tracking-[0.15em] text-slate-500 dark:text-slate-400">
                            Liens
                        </div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-50">
                            {{ stats.links_count }}
                        </div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            Liens trackés créés
                        </p>
                    </div>

                    <!-- Clics + uniques -->
                    <div :class="cardClass + ' p-5'">
                        <div class="text-xs font-medium uppercase tracking-[0.15em] text-slate-500 dark:text-slate-400">
                            Clics
                        </div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-50">
                            {{ stats.clicks_count }}
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ stats.unique_visitors }} visiteurs uniques
                        </p>
                        <p
                            v-if="props.features.deltas"
                            class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-[11px] font-medium"
                            :class="deltaClass(props.globalDelta.clicks || 0)"
                        >
                            {{ deltaLabel(props.globalDelta.clicks || 0) }}
                        </p>
                    </div>

                    <!-- Campagnes -->
                    <div :class="cardClass + ' p-5'">
                        <div class="text-xs font-medium uppercase tracking-[0.15em] text-slate-500 dark:text-slate-400">
                            Campagnes
                        </div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-50">
                            {{ stats.campaigns_count }}
                        </div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            Campagnes actives ou archivées
                        </p>
                        <p
                            v-if="props.features.deltas"
                            class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-[11px] font-medium"
                            :class="deltaClass(props.globalDelta.clicks || 0)"
                        >
                            {{ deltaLabel(props.globalDelta.clicks || 0) }}
                        </p>
                    </div>

                    <!-- Sources -->
                    <div :class="cardClass + ' p-5'">
                        <div class="text-xs font-medium uppercase tracking-[0.15em] text-slate-500 dark:text-slate-400">
                            Sources
                        </div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-50">
                            {{ stats.sources_count }}
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ stats.countries_count }} pays touchés
                        </p>
                        <p
                            v-if="props.features.deltas"
                            class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-[11px] font-medium"
                            :class="deltaClass(props.globalDelta.unique_visitors || 0)"
                        >
                            {{ deltaLabel(props.globalDelta.unique_visitors || 0) }}
                        </p>
                    </div>
                </section>


                
                <!-- Graphiques (Masqués si Zero Data) -->
                <template v-if="!isZeroData">
                    <!-- Graphique : clics sur 7 jours -->
                    <section v-if="props.features.heatmap" :class="bigCardClass">
                        <!-- ... content ... -->
                         <div class="flex items-center justify-between mb-1">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                Clics sur les 7 derniers jours
                            </h3>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                            Toutes sources et toutes campagnes confondues.
                        </p>

                        <div class="flex items-end gap-3 h-40">
                            <template v-if="dailyClicks.length">
                                <div
                                    v-for="day in dailyClicks"
                                    :key="day.date"
                                    class="flex-1 flex flex-col items-center justify-end gap-1"
                                >
                                    <div
                                        class="w-full rounded-t-md bg-indigo-600 dark:bg-indigo-500/80 transition-all"
                                        :style="{ height: `${day.count === 0 ? 4 : Math.min(day.count * 8, 120)}px` }"
                                    />
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                        {{ day.label }}
                                    </div>
                                    <div class="text-[10px] text-slate-700 dark:text-slate-200">
                                        {{ day.count }}
                                    </div>
                                </div>
                            </template>

                            <div v-else class="text-xs text-slate-500">
                                Pas encore de clics sur les 7 derniers jours.
                            </div>
                        </div>
                    </section>

                    <!-- Heatmap horaire -->
                    <section v-if="props.features.topLists" :class="bigCardClass">
                        <!-- ... content ... -->
                         <div class="flex items-center justify-between mb-1">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                Heatmap horaire
                            </h3>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                            Visualisez les jours et heures où vos clics sont les plus élevés.
                        </p>

                        <div v-if="heatmapMatrix.max" class="overflow-x-auto">
                            <div class="space-y-2 min-w-[600px]">
                                <div
                                    v-for="row in heatmapMatrix.rows"
                                    :key="row.weekday"
                                    class="flex items-center gap-2 text-[11px]"
                                >
                                    <span class="w-10 text-right text-slate-500 dark:text-slate-400">
                                        {{ row.label }}
                                    </span>
                                    <div
                                        class="flex-1 grid gap-1"
                                        :style="{ gridTemplateColumns: 'repeat(24, minmax(0, 1fr))' }"
                                    >
                                        <div
                                            v-for="(value, hour) in row.values"
                                            :key="hour"
                                            class="h-5 rounded"
                                            :class="heatmapCellClass(value)"
                                            :title="`${value} clics à ${hour}h`"
                                        >
                                            <span class="sr-only">
                                                {{ value }} clics à {{ hour }}h
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
                            Pas encore assez de clics pour générer la heatmap.
                        </p>
                    </section>

                    <!-- Top campagnes -->
                    <section :class="bigCardClass">
                        <!-- ... content ... -->
                         <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50 mb-2">
                            Campagnes les plus performantes
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                            Classement des campagnes (via leurs sources) sur les 7 derniers jours.
                        </p>
                        <ul class="space-y-3">
                            <li
                                v-for="campaign in topCampaigns"
                                :key="campaign.id"
                                class="rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 p-3 text-xs md:text-sm text-slate-900 dark:text-slate-100 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                            >
                                <div class="min-w-0">
                                    <p class="font-semibold truncate">
                                        {{ campaign.name }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ campaign.status === 'archived' ? 'Archivée' : 'Active' }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xl font-bold">
                                        {{ campaign.total }}
                                    </span>
                                    <InertiaLink
                                        :href="route('campaigns.analytics.show', campaign.id)"
                                        :class="analyticsButtonClass"
                                    >
                                        Voir les analytics
                                    </InertiaLink>
                                </div>
                            </li>
                            <li v-if="!topCampaigns.length" class="text-xs text-slate-500">
                                Aucune campagne n’a encore généré de clics mesurés.
                            </li>
                        </ul>
                    </section>

                    <!-- Top sources / liens -->
                    <section v-if="props.features.topLists" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- ... content ... -->
                        <div :class="bigCardClass">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50 mb-2">
                                Top sources globales
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                                Vos emplacements (bio, newsletter…) qui performent le plus toutes campagnes confondues.
                            </p>
                            <ul class="space-y-3">
                                <li
                                    v-for="source in topSources"
                                    :key="source.id"
                                    class="rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 p-3 text-xs md:text-sm text-slate-900 dark:text-slate-100 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                                >
                                    <div class="min-w-0">
                                        <p class="font-semibold truncate">
                                            {{ source.name }}
                                        </p>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                            {{ formatPercentage(source.percentage) }} des clics
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xl font-bold">
                                            {{ source.total }}
                                        </span>
                                        <InertiaLink
                                            :href="route('sources.analytics.show', source.id)"
                                            :class="analyticsButtonClass"
                                        >
                                            Voir les analytics
                                        </InertiaLink>
                                    </div>
                                </li>
                                <li v-if="!topSources.length" class="text-xs text-slate-500">
                                    Attachez vos liens aux sources pour visualiser ce classement.
                                </li>
                            </ul>
                        </div>

                        <div :class="bigCardClass">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50 mb-2">
                                Top liens trackés
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                                Les liens courts qui génèrent le plus de trafic sur 7 jours.
                            </p>
                            <ul class="space-y-3">
                                <li
                                    v-for="link in topLinks"
                                    :key="link.id"
                                    class="rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 p-3 text-xs md:text-sm text-slate-900 dark:text-slate-100 flex flex-col gap-3"
                                >
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="font-semibold truncate">
                                                {{ link.title }}
                                            </p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                                {{ formatPercentage(link.percentage) }} • {{ link.destination }}
                                            </p>
                                        </div>
                                        <span class="text-xl font-bold">
                                            {{ link.total }}
                                        </span>
                                    </div>
                                    <div class="flex justify-end">
                                        <InertiaLink
                                            :href="route('links.analytics.show', link.id)"
                                            :class="analyticsButtonClass"
                                        >
                                            Voir les analytics
                                        </InertiaLink>
                                    </div>
                                </li>
                                <li v-if="!topLinks.length" class="text-xs text-slate-500">
                                    Aucune donnée disponible pour le moment.
                                </li>
                            </ul>
                        </div>
                    </section>

                    <!-- Résumé devices + pays -->
                    <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- ... content ... -->
                        <div :class="bigCardClass">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                Mobile vs Desktop
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                                Visualisez comment se répartissent vos clics selon le type d’appareil.
                            </p>
                            <div class="space-y-4">
                                <div
                                    v-for="segment in deviceSegments"
                                    :key="segment.key"
                                    class="space-y-1"
                                >
                                    <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-300">
                                        <span>{{ segment.label }}</span>
                                        <span class="font-semibold text-slate-900 dark:text-slate-100">
                                            {{ segment.percentage }}% • {{ segment.count }}
                                        </span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                        <div
                                            class="h-full rounded-full bg-indigo-600 dark:bg-indigo-500 transition-all duration-300"
                                            :style="{ width: `${segment.percentage}%` }"
                                        ></div>
                                    </div>
                                </div>
                                <p
                                    v-if="!Object.values(props.stats.devices_breakdown || {}).some((value) => Number(value) > 0)"
                                    class="text-xs text-slate-500"
                                >
                                    Pas encore assez de clics pour segmenter les appareils.
                                </p>
                            </div>
                        </div>

                        <div :class="bigCardClass">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                Top pays
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                                Les zones géographiques qui génèrent le plus de trafic sur 7 jours.
                            </p>
                            <ul class="space-y-2 text-xs text-slate-700 dark:text-slate-200">
                                <li
                                    v-for="country in topCountries"
                                    :key="country.country"
                                    class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 px-3 py-2"
                                >
                                    <span class="font-medium">
                                        {{ country.country || 'Inconnu' }}
                                    </span>
                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                        {{ country.percentage }}% • {{ country.total }}
                                    </span>
                                </li>
                                <li v-if="!topCountries.length" class="text-slate-500">
                                    Pas encore de données pays.
                                </li>
                            </ul>
                        </div>
                    </section>
                </template>

                <!-- Raccourcis -->
                <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <InertiaLink
                        :href="route('links.index')"
                        :class="primaryLinkCardClass"
                    >
                        <div class="p-5">
                            <div class="text-sm font-semibold text-slate-900 dark:text-slate-50 mb-1">
                                Gérer vos liens
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Créez, activez/désactivez et supprimez vos liens trackés.
                            </p>
                        </div>
                    </InertiaLink>

                    <InertiaLink
                        :href="route('campaigns.index')"
                        :class="primaryLinkCardClass"
                    >
                        <div class="p-5">
                            <div class="text-sm font-semibold text-slate-900 dark:text-slate-50 mb-1">
                                Gérer vos campagnes
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Organisez vos promos par campagne et archivez ce qui est terminé.
                            </p>
                        </div>
                    </InertiaLink>

                    <InertiaLink
                        :href="route('sources.index')"
                        :class="primaryLinkCardClass"
                    >
                        <div class="p-5">
                            <div class="text-sm font-semibold text-slate-900 dark:text-slate-50 mb-1">
                                Gérer vos sources
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Reliez vos liens à TikTok, Instagram, newsletter, etc.
                            </p>
                        </div>
                    </InertiaLink>
                </section>

                <!-- Export mensuel -->
                <section :class="bigCardClass">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                Export mensuel CSV
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Téléchargez un rapport consolidé (clics, devices, pays, referers) pour le mois choisi.
                            </p>
                        </div>
                        <div class="flex flex-col md:flex-row items-start md:items-center gap-3">
                            <input
                                v-model="exportMonth"
                                type="month"
                                class="rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900/80 px-3 py-2 text-xs text-slate-900 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md bg-indigo-600 dark:bg-indigo-500 px-4 py-2 text-xs font-semibold text-white shadow-xl shadow-indigo-500/30 dark:shadow-indigo-900/30 hover:bg-indigo-500 dark:hover:bg-indigo-400 transition"
                                @click="exportMonthlyTraffic"
                                :disabled="!exportMonth"
                            >
                                Exporter le mois
                            </button>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
