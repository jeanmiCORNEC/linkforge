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

const clicksPerDay = computed(() => props.stats?.clicksPerDay ?? []);
const topSources = computed(() => props.stats?.topSources ?? []);
const topLinks = computed(() => props.stats?.topLinks ?? []);
const topDays = computed(() => props.stats?.topDays ?? []);
const formatPercentage = (value) => `${value ?? 0}%`;

const hourlyHeatmap = computed(() => props.stats?.hourlyHeatmap ?? []);
const heatmapHours = Array.from({ length: 24 }, (_, index) => index);
const weekdayLabels = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
const weekdayOrder = [1, 2, 3, 4, 5, 6, 0];
const canExport = computed(() => props.features?.exports ?? false);
const canExportRaw = computed(() => props.features?.rawLog ?? false);
const showHeatmap = computed(() => props.features?.heatmap ?? false);
const showTopLists = computed(() => props.features?.topLists ?? false);
const showDeltas = computed(() => props.features?.deltas ?? false);

const exportMonth = ref(new Date().toISOString().slice(0, 7));
const exportMonthlyTraffic = () => {
    if (!exportMonth.value) {
        return;
    }

    window.location = route('exports.traffic.monthly', {
        month: exportMonth.value,
        type: 'campaign',
        id: props.campaign.id,
    });
};

const deviceSegments = computed(() => {
    const raw = props.stats?.devices ?? {};
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

const topCountries = computed(() => props.stats?.topCountries ?? []);

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

const exportRawAnalytics = () => {
    window.location = route('campaigns.analytics.export-raw', {
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
                <div :class="shellCardClass + ' flex flex-col gap-6'">
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

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-4 text-xs md:text-sm text-slate-200">
                            <div class="space-y-2">
                                <p class="uppercase tracking-[0.15em] text-slate-500 text-[10px]">
                                    Période
                                </p>
                                <div class="flex flex-wrap items-center gap-3">
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
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-400">Perso :</span>
                                        <input
                                            v-model="customDays"
                                            type="number"
                                            min="1"
                                            class="w-16 rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        />
                                        <button
                                            type="button"
                                            :class="primaryButtonClass"
                                            @click="applyCustomPeriod"
                                        >
                                            OK
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p class="text-slate-400">
                                Analyse des {{ stats.period?.days ?? currentDays }} derniers jours • Depuis le {{ formatDateFr(stats.period?.since) }}
                            </p>
                        </div>

                        <div class="space-y-3 text-xs md:text-sm text-slate-200">
                            <div class="flex flex-wrap items-start gap-3">
                                <button
                                    v-if="canExport"
                                    type="button"
                                    :class="primaryButtonClass"
                                    @click="exportAnalytics"
                                >
                                    Exporter CSV
                                </button>

                                <div v-if="canExportRaw" class="flex flex-col gap-1">
                                    <button
                                        type="button"
                                        :class="primaryButtonClass"
                                        @click="exportRawAnalytics"
                                    >
                                        Exporter clics (raw)
                                    </button>
                                    <p class="text-[11px] text-slate-400 max-w-[220px] leading-snug">
                                        Log brut clic par clic (horodatage, device, pays) pour audit ou reporting avancé.
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col md:flex-row items-start md:items-center gap-2 text-slate-300">
                                <label class="flex items-center gap-2">
                                    Mois
                                    <input
                                        v-model="exportMonth"
                                        type="month"
                                        class="rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    />
                                </label>
                                <button
                                    type="button"
                                    :class="primaryButtonClass"
                                    @click="exportMonthlyTraffic"
                                    :disabled="!exportMonth"
                                >
                                    Exporter le mois
                                </button>
                            </div>
                        </div>
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
                        v-if="showDeltas"
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
                        v-if="showDeltas"
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
            <section v-if="showTopLists" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
            <section v-if="showHeatmap" :class="bigCardClass">
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

            <!-- Devices & top pays -->
            <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div :class="bigCardClass">
                    <h3 class="text-sm font-semibold mb-2">
                        Mobile vs Desktop
                    </h3>
                    <p class="text-xs text-slate-400 mb-4">
                        Identifiez où concentrer vos efforts créatifs selon l’appareil utilisé.
                    </p>
                    <div class="space-y-4">
                        <div
                            v-for="segment in deviceSegments"
                            :key="segment.key"
                            class="space-y-1"
                        >
                            <div class="flex items-center justify-between text-xs text-slate-300">
                                <span>{{ segment.label }}</span>
                                <span class="font-semibold text-slate-100">
                                    {{ segment.percentage }}% • {{ segment.count }}
                                </span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                                <div
                                    class="h-full rounded-full bg-indigo-500 transition-all duration-300"
                                    :style="{ width: `${segment.percentage}%` }"
                                ></div>
                            </div>
                        </div>
                        <p
                            v-if="!Object.values(props.stats?.devices || {}).some((value) => Number(value) > 0)"
                            class="text-xs text-slate-500"
                        >
                            Pas encore de données d’appareil sur cette période.
                        </p>
                    </div>
                </div>

                <div :class="bigCardClass">
                    <h3 class="text-sm font-semibold mb-2">
                        Top pays
                    </h3>
                    <p class="text-xs text-slate-400 mb-4">
                        Les pays qui alimentent le plus cette campagne.
                    </p>
                    <ul class="space-y-2 text-xs text-slate-200">
                        <li
                            v-for="country in topCountries"
                            :key="country.country"
                            class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-900/40 px-3 py-2"
                        >
                            <span class="font-medium">
                                {{ country.country || 'Inconnu' }}
                            </span>
                            <span class="text-sm font-semibold text-slate-100">
                                {{ country.percentage }}% • {{ country.total }}
                            </span>
                        </li>
                        <li v-if="!topCountries.length" class="text-slate-500">
                            Pas encore de données pays.
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
