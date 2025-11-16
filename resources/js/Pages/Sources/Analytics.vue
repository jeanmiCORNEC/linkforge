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

const devices = computed(() => props.stats.devices_breakdown || {});
const browsers = computed(() => props.stats.browsers_breakdown || {});
const clicksPerDay = computed(() => props.stats.clicks_per_day || []);

const hasClicks = computed(() => totalClicks.value > 0);

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

const browserLabel = (key) => (key && key !== 'unknown' ? key : 'Inconnu');

/* ---------- Styles DA LinkForge (identiques aux autres pages analytics) ---------- */

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
</script>

<template>
    <Head :title="`Stats source - ${source.name}`" />

    <AuthenticatedLayout>
        <!-- HEADER ANALYTICS -->
        <section class="border-b border-slate-800 bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900">
            <!-- même largeur que liens / campagnes -->
            <div class="w-[95%] mx-auto pt-8 pb-10">
                <div :class="shellCardClass + ' flex flex-col md:flex-row md:items-center md:justify-between gap-6'">
                    <!-- Bloc gauche : titre source -->
                    <div class="space-y-3">
                        <p
                            class="inline-flex items-center rounded-full border border-indigo-500/40 bg-indigo-500/10 px-3 py-1 text-xs md:text-sm font-medium text-indigo-200 uppercase tracking-[0.15em]"
                        >
                            Analytics – Source
                        </p>

                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h1 class="text-xl md:text-2xl font-semibold tracking-tight">
                                    {{ source.name }}
                                </h1>
                                <span
                                    v-if="source.platform"
                                    class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-indigo-900/60 text-indigo-200 border border-indigo-500/40"
                                >
                                    {{ source.platform }}
                                </span>
                            </div>

                            <p class="text-xs md:text-sm text-slate-400">
                                Source liée à vos campagnes et liens trackés.
                            </p>
                        </div>

                        <InertiaLink
                            :href="route('sources.index')"
                            class="inline-flex items-center gap-1 text-xs md:text-sm text-slate-400 hover:text-indigo-300 transition"
                        >
                            <span>←</span>
                            <span>Retour aux sources</span>
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
                            Analyse des {{ props.stats.period?.days ?? currentDays }} derniers jours
                            • Depuis le {{ formatDateFr(props.stats.period?.since) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENU ANALYTICS -->
        <main class="w-[95%] mx-auto pt-8 pb-12 space-y-8">
            <!-- KPIs -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div :class="cardClass">
                    <p class="text-xs md:text-sm uppercase tracking-[0.15em] text-slate-400">
                        Total clics
                    </p>
                    <p class="mt-2 text-3xl font-bold">
                        {{ totalClicks }}
                    </p>
                    <p class="mt-1 text-xs md:text-sm text-slate-400">
                        Sur les {{ props.stats.period?.days ?? currentDays }} derniers jours
                    </p>
                </div>

                <div :class="cardClass">
                    <p class="text-xs md:text-sm uppercase tracking-[0.15em] text-slate-400">
                        Visiteurs uniques
                    </p>
                    <p class="mt-2 text-3xl font-bold">
                        {{ uniqueVisitors }}
                    </p>
                    <p class="mt-1 text-xs md:text-sm text-slate-400">
                        Basé sur le hash visiteur (IP + user-agent)
                    </p>
                </div>

                <div :class="cardClass">
                    <p class="text-xs md:text-sm uppercase tracking-[0.15em] text-slate-400">
                        Période analysée
                    </p>
                    <p class="mt-2 text-lg font-semibold">
                        Derniers {{ props.stats.period?.days ?? currentDays }} jours
                    </p>
                    <p class="mt-1 text-xs md:text-sm text-slate-400">
                        Depuis le {{ formatDateFr(props.stats.period?.since) }}
                    </p>
                </div>
            </section>

            <!-- Graph clics / jour (full width) -->
            <section :class="bigCardClass">
                <h3 class="text-sm font-semibold mb-1">
                    Clics par jour
                </h3>
                <p class="text-xs md:text-sm text-slate-400 mb-4">
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
                        Pas encore de clics pour cette source sur la période sélectionnée.
                    </div>
                </div>
            </section>

            <!-- Devices & navigateurs -->
            <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div :class="bigCardClass">
                    <h3 class="text-sm font-semibold mb-2">
                        Appareils
                    </h3>
                    <ul class="space-y-1 text-xs text-slate-200">
                        <li
                            v-for="(count, key) in devices"
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
                            v-if="!Object.keys(devices || {}).length"
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
                            v-for="(count, key) in browsers"
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
                            v-if="!Object.keys(browsers || {}).length"
                            class="text-slate-500"
                        >
                            Pas encore de données.
                        </li>
                    </ul>
                </div>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
