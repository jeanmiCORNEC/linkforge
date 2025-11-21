<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link as InertiaLink, router } from '@inertiajs/vue3';

const props = defineProps({
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
    features: {
        type: Object,
        required: true,
    },
});

// ---- Période sélectionnée (7 / 30 / perso) ----
const currentDays = computed(() => Number(props.filters.days || 7));
const customDays = ref(currentDays.value);

const changePeriod = (days) => {
    customDays.value = days;

    router.get(
        route('sources.analytics.show', props.source.id),
        { days },
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

// ---- Helpers de stats ----
const totalClicks = computed(() => props.stats.total_clicks ?? 0);
const uniqueVisitors = computed(() => props.stats.unique_visitors ?? 0);

const clicksPerDay = computed(() => props.stats.clicks_per_day || []);

const hasClicks = computed(() => totalClicks.value > 0);

const topLinks = computed(() => props.stats.top_links ?? []);
const topDays = computed(() => props.stats.top_days ?? []);
const hourlyHeatmap = computed(() => props.stats.hourly_heatmap ?? []);
const formatPercentage = (value) => `${value ?? 0}%`;

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
        return 'bg-slate-100 dark:bg-slate-900/40 text-slate-400 dark:text-slate-500';
    }
    const ratio = value / max;
    if (ratio < 0.33) return 'bg-indigo-200 dark:bg-indigo-900 text-indigo-800 dark:text-slate-200';
    if (ratio < 0.66) return 'bg-indigo-400 dark:bg-indigo-700 text-white';
    return 'bg-indigo-600 dark:bg-indigo-500 text-white';
};

const exportAnalytics = () => {
    window.location = route('sources.analytics.export', {
        source: props.source.id,
        days: currentDays.value,
    });
};

const exportRawAnalytics = () => {
    window.location = route('sources.analytics.export-raw', {
        source: props.source.id,
        days: currentDays.value,
    });
};

const canExport = computed(() => props.features?.exports ?? false);
const showHeatmap = computed(() => props.features?.heatmap ?? false);
const showTopLists = computed(() => props.features?.topLists ?? false);
const showDeltas = computed(() => props.features?.deltas ?? false);
const canExportRaw = computed(() => props.features?.rawLog ?? false);

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
    if (!exportMonth.value) {
        return;
    }

    window.location = route('exports.traffic.monthly', {
        month: exportMonth.value,
        type: 'source',
        id: props.source.id,
    });
};

// ---- Helpers d’affichage ----
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
    if (!key || key === 'unknown') return 'Inconnu';
    return key;
};

/* ---------- Styles DA LinkForge (identiques aux autres pages analytics) ---------- */

const shellCardClass =
    'relative rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-6 py-5 shadow-sm dark:shadow-black/20';

const cardClass =
    'rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm dark:shadow-black/20';

const bigCardClass =
    'rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm dark:shadow-black/20';

const primaryButtonClass =
    'inline-flex items-center rounded-md bg-indigo-600 dark:bg-indigo-500 px-3 py-1 text-xs md:text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 dark:hover:bg-indigo-400 disabled:opacity-50 transition';

const periodPillGroupClass =
    'inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-1';

const periodPillBaseClass =
    'px-3 py-1 rounded-full text-xs md:text-sm font-medium transition';

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

const totalDelta = computed(() => props.stats.delta?.total_clicks ?? 0);
const uniqueDelta = computed(() => props.stats.delta?.unique_visitors ?? 0);
</script>

<template>
    <Head :title="`Stats source - ${source.name}`" />

    <AuthenticatedLayout>
        <!-- HEADER ANALYTICS -->
        <section class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-gradient-to-b dark:from-slate-950 dark:via-slate-950 dark:to-slate-900">
            <!-- même largeur que liens / campagnes -->
            <div class="w-[95%] mx-auto pt-8 pb-10">
                <div :class="shellCardClass + ' flex flex-col gap-6'">
                    <!-- Bloc gauche : titre source -->
                    <div class="space-y-3">
                        <p
                            class="inline-flex items-center rounded-full border border-indigo-200 dark:border-indigo-500/40 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1 text-xs md:text-sm font-medium text-indigo-600 dark:text-indigo-200 uppercase tracking-[0.15em]"
                        >
                            Analytics – Source
                        </p>

                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h1 class="text-xl md:text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">
                                    {{ source.name }}
                                </h1>
                                <span
                                    v-if="source.platform"
                                    class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-500/40"
                                >
                                    {{ source.platform }}
                                </span>
                            </div>

                            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
                                Source liée à vos campagnes et liens trackés.
                            </p>
                        </div>

                        <InertiaLink
                            :href="route('sources.index')"
                            class="inline-flex items-center gap-1 text-xs md:text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-300 transition"
                        >
                            <span>←</span>
                            <span>Retour aux sources</span>
                        </InertiaLink>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-4 text-xs md:text-sm text-slate-700 dark:text-slate-200">
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
                                                    ? 'bg-white dark:bg-indigo-500 text-indigo-600 dark:text-white shadow-sm dark:shadow shadow-slate-200'
                                                    : 'text-slate-500 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white',
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
                                                    ? 'bg-white dark:bg-indigo-500 text-indigo-600 dark:text-white shadow-sm dark:shadow shadow-slate-200'
                                                    : 'text-slate-500 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white',
                                            ]"
                                            @click="changePeriod(30)"
                                        >
                                            30 jours
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-500 dark:text-slate-400">Perso :</span>
                                        <input
                                            v-model="customDays"
                                            type="number"
                                            min="1"
                                            class="w-16 rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
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
                            <p class="text-slate-500 dark:text-slate-400">
                                Analyse des {{ props.stats.period?.days ?? currentDays }} derniers jours •
                                Depuis le {{ formatDateFr(props.stats.period?.since) }}
                            </p>
                        </div>

                        <div class="space-y-3 text-xs md:text-sm text-slate-700 dark:text-slate-200">
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
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-[220px] leading-snug">
                                        Log brut clic par clic (horodatage, device, pays) pour audit ou BI.
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col md:flex-row items-start md:items-center gap-2 text-slate-600 dark:text-slate-300">
                                <label class="flex items-center gap-2">
                                    Mois
                                    <input
                                        v-model="exportMonth"
                                        type="month"
                                        class="rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1 text-xs text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
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
            <!-- KPIs -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div :class="cardClass">
                    <p class="text-xs md:text-sm uppercase tracking-[0.15em] text-slate-500 dark:text-slate-400">
                        Total clics
                    </p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                        {{ totalClicks }}
                    </p>
                    <p class="mt-1 text-xs md:text-sm text-slate-500 dark:text-slate-400">
                        Sur les {{ props.stats.period?.days ?? currentDays }} derniers jours
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
                    <p class="text-xs md:text-sm uppercase tracking-[0.15em] text-slate-500 dark:text-slate-400">
                        Visiteurs uniques
                    </p>
                    <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">
                        {{ uniqueVisitors }}
                    </p>
                    <p class="mt-1 text-xs md:text-sm text-slate-500 dark:text-slate-400">
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
                    <p class="text-xs md:text-sm uppercase tracking-[0.15em] text-slate-500 dark:text-slate-400">
                        Période analysée
                    </p>
                    <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">
                        Derniers {{ props.stats.period?.days ?? currentDays }} jours
                    </p>
                    <p class="mt-1 text-xs md:text-sm text-slate-500 dark:text-slate-400">
                        Depuis le {{ formatDateFr(props.stats.period?.since) }}
                    </p>
                </div>
            </section>

            <!-- Tops : liens et jours -->
            <section v-if="showTopLists" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div :class="bigCardClass">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                        Liens trackés les plus performants
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Utilisez ces liens courts dans cette source pour garder un suivi précis.
                    </p>
                    <ul class="space-y-3">
                        <li
                            v-for="link in topLinks"
                            :key="link.id"
                            class="rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-3 text-xs md:text-sm text-slate-900 dark:text-slate-100"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold truncate">
                                        {{ link.title }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ formatPercentage(link.percentage) }} des clics
                                    </p>
                                </div>
                                <span class="text-xl font-bold text-slate-900 dark:text-white">
                                    {{ link.total }}
                                </span>
                            </div>
                        </li>
                        <li v-if="!topLinks.length" class="text-xs text-slate-500">
                            Connectez vos liens trackés pour suivre leurs performances ici.
                        </li>
                    </ul>
                </div>

                <div :class="bigCardClass">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                        Jours les plus performants
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Les meilleurs jours pour cette source sur la période.
                    </p>
                    <ul class="space-y-3">
                        <li
                            v-for="day in topDays"
                            :key="day.date"
                            class="rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-3 text-xs md:text-sm text-slate-900 dark:text-slate-100 flex items-center justify-between gap-3"
                        >
                            <div>
                                <p class="font-semibold">
                                    {{ formatDateFr(day.date) }}
                                </p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ formatPercentage(day.percentage) }} des clics
                                </p>
                            </div>
                            <span class="text-xl font-bold text-slate-900 dark:text-white">
                                {{ day.total }}
                            </span>
                        </li>
                        <li v-if="!topDays.length" class="text-xs text-slate-500">
                            Pas encore assez de clics pour établir un top.
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Graph clics / jour (full width) -->
            <section v-if="showHeatmap" :class="bigCardClass">
                <h3 class="text-sm font-semibold mb-1 text-slate-900 dark:text-slate-50">
                    Clics par jour
                </h3>
                <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mb-4">
                    Évolution des clics pour cette source sur la période sélectionnée.
                </p>

                <div class="flex items-end gap-3 h-40">
                    <template v-if="hasClicks && clicksPerDay.length">
                        <div
                            v-for="day in clicksPerDay"
                            :key="day.date"
                            class="flex-1 flex flex-col items-center justify-end gap-1"
                        >
                            <div
                                class="w-full rounded-t-md bg-indigo-600 dark:bg-indigo-500/80 transition-all"
                                :style="{ height: `${day.total === 0 ? 4 : Math.min(day.total * 12, 120)}px` }"
                            />
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                {{ formatDayLabel(day.date) }}
                            </div>
                            <div class="text-[10px] text-slate-700 dark:text-slate-200">
                                {{ day.total }}
                            </div>
                        </div>
                    </template>

                    <div v-else class="text-xs text-slate-500 dark:text-slate-400">
                        Pas encore de clics pour cette source sur la période sélectionnée.
                    </div>
                </div>
            </section>

            <!-- Devices & top pays -->
            <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div :class="bigCardClass">
                    <h3 class="text-sm font-semibold mb-2 text-slate-900 dark:text-slate-50">
                        Mobile vs Desktop
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Visualisez comment cette source performe selon l’appareil utilisé.
                    </p>
                    <div class="space-y-4">
                        <div
                            v-for="segment in deviceSegments"
                            :key="segment.key"
                            class="space-y-1"
                        >
                            <div class="flex items-center justify-between text-xs text-slate-700 dark:text-slate-300">
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
                            Pas assez de clics segmentés sur cette période.
                        </p>
                    </div>
                </div>

                <div :class="bigCardClass">
                    <h3 class="text-sm font-semibold mb-2 text-slate-900 dark:text-slate-50">
                        Top pays
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Les zones géographiques qui cliquent le plus sur cette source.
                    </p>
                    <ul class="space-y-2 text-xs text-slate-700 dark:text-slate-200">
                        <li
                            v-for="country in topCountries"
                            :key="country.country"
                            class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 px-3 py-2"
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

            <!-- Heatmap horaire -->
            <section :class="bigCardClass">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50">
                    Heatmap horaire
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                    Visualisez les heures et jours où cette source performe le plus.
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
                        Plus la couleur est lumineuse, plus le créneau reçoit de clics.
                    </p>
                </div>
                <p v-else class="text-xs text-slate-500">
                    Pas encore assez de clics pour générer la heatmap.
                </p>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
