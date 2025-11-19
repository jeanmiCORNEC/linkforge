<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link as InertiaLink } from '@inertiajs/vue3';
import { computed } from 'vue';

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

/* ---------- Styles DA LinkForge ---------- */
// Card de base : même DA que les pages analytics (glow indigo)
const cardClass =
    'rounded-xl border border-slate-800 bg-slate-950/70 shadow-xl shadow-indigo-900/30';

// Variante "grande" avec padding
const bigCardClass = cardClass + ' p-6';

// Cards cliquables (raccourcis)
const clickableCardClass =
    cardClass +
    ' cursor-pointer hover:border-indigo-500/70 hover:bg-slate-900/90 hover:shadow-indigo-900/40 transition';

const primaryLinkCardClass = 'block ' + clickableCardClass;

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
        return 'bg-slate-900/40 text-slate-500';
    }
    const ratio = value / max;
    if (ratio < 0.33) return 'bg-indigo-900 text-slate-200';
    if (ratio < 0.66) return 'bg-indigo-700 text-white';
    return 'bg-indigo-500 text-white';
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-4xl font-bold text-slate-50 tracking-tight">
                Tableau de bord
            </h2>
        </template>

        <div class="py-8">
            <main class="w-[95%] mx-auto pt-2 pb-12 space-y-8">
                <!-- Cartes stats principales -->
                <section class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Liens -->
                    <div :class="cardClass + ' p-5'">
                        <div class="text-xs font-medium uppercase tracking-[0.15em] text-slate-400">
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
                    <div :class="cardClass + ' p-5'">
                        <div class="text-xs font-medium uppercase tracking-[0.15em] text-slate-400">
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
                    <div :class="cardClass + ' p-5'">
                        <div class="text-xs font-medium uppercase tracking-[0.15em] text-slate-400">
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
                    <div :class="cardClass + ' p-5'">
                        <div class="text-xs font-medium uppercase tracking-[0.15em] text-slate-400">
                            Sources
                        </div>
                        <div class="mt-2 text-3xl font-semibold text-slate-50">
                            {{ stats.sources_count }}
                        </div>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ stats.countries_count }} pays touchés
                        </p>
                    </div>
                </section>

                <!-- Graphique : clics sur 7 jours -->
                <section :class="bigCardClass">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-sm font-semibold text-slate-50">
                            Clics sur les 7 derniers jours
                        </h3>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">
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
                                    class="w-full rounded-t-md bg-indigo-500/80 transition-all"
                                    :style="{ height: `${day.count === 0 ? 4 : Math.min(day.count * 8, 120)}px` }"
                                />
                                <div class="text-[10px] text-slate-400">
                                    {{ day.label }}
                                </div>
                                <div class="text-[10px] text-slate-200">
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
                <section :class="bigCardClass">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-sm font-semibold text-slate-50">
                            Heatmap horaire
                        </h3>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">
                        Visualisez les jours et heures où vos clics sont les plus élevés.
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
                    <h3 class="text-sm font-semibold text-slate-50 mb-2">
                        Campagnes les plus performantes
                    </h3>
                    <p class="text-xs text-slate-400 mb-4">
                        Classement des campagnes (via leurs sources) sur les 7 derniers jours.
                    </p>
                    <ul class="space-y-3">
                        <li
                            v-for="campaign in topCampaigns"
                            :key="campaign.id"
                            class="rounded-lg border border-slate-800 bg-slate-900/60 p-3 text-xs md:text-sm text-slate-100 flex items-center justify-between gap-3"
                        >
                            <div class="min-w-0">
                                <p class="font-semibold truncate">
                                    {{ campaign.name }}
                                </p>
                                <p class="text-[11px] text-slate-400">
                                    {{ campaign.status === 'archived' ? 'Archivée' : 'Active' }}
                                </p>
                            </div>
                            <span class="text-xl font-bold">
                                {{ campaign.total }}
                            </span>
                        </li>
                        <li v-if="!topCampaigns.length" class="text-xs text-slate-500">
                            Aucune campagne n’a encore généré de clics mesurés.
                        </li>
                    </ul>
                </section>

                <!-- Résumé devices + navigateurs -->
                <section :class="bigCardClass">
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
                </section>

                <!-- Raccourcis -->
                <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                </section>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
