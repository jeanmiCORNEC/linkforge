<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link as InertiaLink, router } from '@inertiajs/vue3';

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

const currentDays = computed(() => Number(props.filters.days || 7));
const customDays = ref(currentDays.value);

// Helpers d’affichage
const deviceLabel = (key) => {
    if (key === 'mobile') return 'Mobile';
    if (key === 'desktop') return 'Desktop';
    if (key === 'tablet') return 'Tablette';
    if (!key) return 'Inconnu';
    return key;
};

const browserLabel = (key) => key || 'Inconnu';

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

// Changement de période
const changePeriod = (days) => {
    customDays.value = days;
    router.get(
        route('links.analytics.show', props.link.id),
        { days },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const applyCustomPeriod = () => {
    const val = Number(customDays.value) || 7;
    changePeriod(val);
};
</script>

<template>

    <Head :title="`Stats du lien - ${link.title}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-950 text-slate-100">
            <!-- HEADER ANALYTICS -->
            <section class="border-b border-slate-800 bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900">
                <div class="max-w-6xl mx-auto px-4 pt-8 pb-10">
                    <div
                        class="relative rounded-3xl border border-slate-800 bg-slate-950/80 px-6 py-5 shadow-xl shadow-indigo-900/30 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <!-- Bloc gauche : titre lien -->
                        <div class="space-y-3">
                            <p
                                class="inline-flex items-center rounded-full border border-indigo-500/40 bg-indigo-500/10 px-3 py-1 text-xs md:text-sm font-medium text-indigo-200 uppercase tracking-[0.15em]">
                                Analytics – Lien
                            </p>

                            <div>
                                <h1 class="text-xl md:text-2xl font-semibold tracking-tight">
                                    {{ link.title }}
                                </h1>
                                
                            </div>

                            <InertiaLink :href="route('links.index')"
                                class="inline-flex items-center gap-1 text-xs md:text-sm text-slate-400 hover:text-indigo-300 transition">
                                <span>←</span>
                                <span>Retour aux liens</span>
                            </InertiaLink>
                        </div>

                        <!-- Bloc droite : période -->
                        <div class="flex flex-col items-start md:items-end gap-3 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="uppercase tracking-[0.15em] text-slate-400 text-[10px]">
                                    Période
                                </span>

                                <div
                                    class="inline-flex items-center rounded-full bg-slate-900/80 border border-slate-700 p-1">
                                    <button type="button"
                                        class="px-3 py-1 rounded-full text-xs md:text-sm font-medium transition" :class="currentDays === 7
                                            ? 'bg-indigo-500 text-white shadow shadow-indigo-500/40'
                                            : 'text-slate-300 hover:text-white'" @click="changePeriod(7)">
                                        7 jours
                                    </button>
                                    <button type="button"
                                        class="px-3 py-1 rounded-full text-xs md:text-sm font-medium transition" :class="currentDays === 30
                                            ? 'bg-indigo-500 text-white shadow shadow-indigo-500/40'
                                            : 'text-slate-300 hover:text-white'" @click="changePeriod(30)">
                                        30 jours
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 text-xs md:text-sm">
                                <span class="text-slate-400">Perso (jours) :</span>
                                <input v-model="customDays" type="number" min="1"
                                    class="w-16 rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs md:text-sm text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                <button type="button"
                                    class="rounded-md bg-slate-800 px-3 py-1 text-xs md:text-sm font-medium text-slate-100 hover:bg-indigo-500 hover:text-white transition"
                                    @click="applyCustomPeriod">
                                    Appliquer
                                </button>
                            </div>

                            <p class="text-xs md:text-sm text-slate-400">
                                Analyse des {{ stats.period.days }} derniers jours
                                • Depuis le {{ formatDateFr(stats.period.since) }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CONTENU ANALYTICS -->
            <main class="max-w-6xl mx-auto px-4 py-8 space-y-6">
                <!-- KPIs -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5 shadow-md shadow-slate-950/40">
                        <p class="text-xs md:text-sm uppercase tracking-[0.15em] text-slate-400">
                            Total clics
                        </p>
                        <p class="mt-2 text-3xl font-bold">
                            {{ stats.totalClicks }}
                        </p>
                        <p class="mt-1 text-xs md:text-sm text-slate-400">
                            Sur les {{ stats.period.days }} derniers jours
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5 shadow-md shadow-slate-950/40">
                        <p class="text-xs md:text-sm uppercase tracking-[0.15em] text-slate-400">
                            Visiteurs uniques
                        </p>
                        <p class="mt-2 text-3xl font-bold">
                            {{ stats.uniqueVisitors }}
                        </p>
                        <p class="mt-1 text-xs md:text-sm text-slate-400">
                            Basé sur le hash visiteur (IP + user-agent)
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-5 shadow-md shadow-slate-950/40">
                        <p class="text-xs md:text-sm uppercase tracking-[0.15em] text-slate-400">
                            Période analysée
                        </p>
                        <p class="mt-2 text-lg font-semibold">
                            Derniers {{ stats.period.days }} jours
                        </p>
                        <p class="mt-1 text-xs md:text-sm text-slate-400">
                            Depuis le {{ formatDateFr(stats.period.since) }}
                        </p>
                    </div>
                </div>

                <!-- Graph clics / jour -->
                <section class="rounded-2xl border border-slate-800 bg-slate-950/70 p-6 shadow-md shadow-slate-950/40">
                    <h3 class="text-sm font-semibold mb-1">
                        Clics par jour
                    </h3>
                    <p class="text-xs md:text-sm text-slate-400 mb-4">
                        Évolution des clics pour ce lien sur la période sélectionnée.
                    </p>

                    <div class="flex items-end gap-3 h-40">
                        <div v-if="stats.clicksPerDay.length" v-for="day in stats.clicksPerDay" :key="day.date"
                            class="flex-1 flex flex-col items-center justify-end gap-1">
                            <div class="w-full rounded-t-md bg-indigo-500/80 transition-all"
                                :style="{ height: `${day.total === 0 ? 4 : Math.min(day.total * 12, 120)}px` }"></div>
                            <div class="text-[10px] text-slate-400">
                                {{ formatDayLabel(day.date) }}
                            </div>
                            <div class="text-[10px] text-slate-200">
                                {{ day.total }}
                            </div>
                        </div>

                        <div v-if="!stats.clicksPerDay.length" class="text-xs text-slate-400">
                            Pas encore de clics enregistrés sur la période.
                        </div>
                    </div>
                </section>

                <!-- Devices & navigateurs -->
                <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-6 shadow-md shadow-slate-950/40">
                        <h3 class="text-sm font-semibold mb-2">
                            Appareils
                        </h3>
                        <ul class="space-y-1 text-xs text-slate-200">
                            <li v-for="(count, key) in stats.devices" :key="key" class="flex justify-between">
                                <span class="text-slate-300">
                                    {{ deviceLabel(key) }}
                                </span>
                                <span class="font-semibold">
                                    {{ count }}
                                </span>
                            </li>
                            <li v-if="!Object.keys(stats.devices || {}).length" class="text-slate-500">
                                Pas encore de données.
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-slate-800 bg-slate-950/70 p-6 shadow-md shadow-slate-950/40">
                        <h3 class="text-sm font-semibold mb-2">
                            Navigateurs
                        </h3>
                        <ul class="space-y-1 text-xs text-slate-200">
                            <li v-for="(count, key) in stats.browsers" :key="key" class="flex justify-between">
                                <span class="text-slate-300">
                                    {{ browserLabel(key) }}
                                </span>
                                <span class="font-semibold">
                                    {{ count }}
                                </span>
                            </li>
                            <li v-if="!Object.keys(stats.browsers || {}).length" class="text-slate-500">
                                Pas encore de données.
                            </li>
                        </ul>
                    </div>
                </section>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
