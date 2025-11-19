<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { ref, onMounted } from 'vue';

const props = defineProps({
    campaigns: {
        type: Object, // paginator
        required: true,
    },
    links: {
        type: Array,
        required: true,
    },
});

// --- Formulaire de création de source ---
const createForm = useForm({
    campaign_id: '',
    name: '',
    platform: '',
    notes: '',
});

// Pré-sélectionner la première campagne
onMounted(() => {
    if (!createForm.campaign_id && props.campaigns.data.length) {
        createForm.campaign_id = props.campaigns.data[0].id;
    }
});

const createSource = () => {
    createForm.post(route('sources.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset('name', 'platform', 'notes');
        },
    });
};

// --- Suppression de source ---
const deleteForm = useForm({});

const deleteSource = (source) => {
    if (!confirm(`Supprimer la source "${source.name}" ?`)) return;

    deleteForm.delete(route('sources.destroy', source.id), {
        preserveScroll: true,
    });
};

// --- Édition (modale) ---
const isEditOpen = ref(false);
const editingSource = ref(null);

const editForm = useForm({
    name: '',
    platform: '',
    notes: '',
});

const openEditModal = (source) => {
    editingSource.value = source;

    editForm.reset();
    editForm.name = source.name;
    editForm.platform = source.platform || '';
    editForm.notes = source.notes || '';

    isEditOpen.value = true;
};

const closeEditModal = () => {
    isEditOpen.value = false;
    editingSource.value = null;
};

const updateSource = () => {
    if (!editingSource.value) return;

    editForm.put(route('sources.update', editingSource.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
    });
};

// --- Aller vers les stats d'une source ---
const goToAnalytics = (source) => {
    router.get(route('sources.analytics.show', source.id), {}, { preserveScroll: true });
};

// --- Lier un lien à une source (TrackedLink) ---
const selectedLinks = ref({}); // { [sourceId]: linkId }

const trackedLinkForm = useForm({
    link_id: '',
});

const attachLinkToSource = (source) => {
    const linkId = selectedLinks.value[source.id];

    if (!linkId) {
        alert('Choisis un lien à connecter à cette source.');
        return;
    }

    trackedLinkForm.link_id = linkId;

    trackedLinkForm.post(route('sources.tracked-links.store', source.id), {
        preserveScroll: true,
    });
};

// --- Helpers : copie de tracking links ---
const trackingUrl = (trackedLink) =>
    route('links.redirect', { tracking_key: trackedLink.tracking_key });

const showToast = ref(false);
const toastMessage = ref('');

const triggerToast = (message = 'Lien copié dans le presse-papier') => {
    toastMessage.value = message;
    showToast.value = true;

    setTimeout(() => {
        showToast.value = false;
    }, 2000);
};

const copyTrackedLink = async (trackedLink) => {
    if (!trackedLink) return;

    try {
        await navigator.clipboard.writeText(trackingUrl(trackedLink));
        triggerToast();
    } catch (error) {
        triggerToast("Impossible de copier l'URL");
    }
};

/* ---------- Styles DA LinkForge (alignés sur liens / campagnes / analytics) ---------- */

const shellCardClass =
    'relative rounded-3xl border border-slate-800 bg-slate-950/80 px-6 py-5 shadow-xl shadow-indigo-900/30';

const bigCardClass =
    'rounded-xl border border-slate-800 bg-slate-950/70 p-6 shadow-xl shadow-indigo-900/30';

const primaryButtonClass =
    'inline-flex items-center rounded-md bg-indigo-500 px-4 py-2 text-xs font-semibold text-white shadow-sm shadow-indigo-900/40 hover:bg-indigo-400 disabled:opacity-50 transition';

const tinyPrimaryButtonClass =
    'px-3 py-1 text-[11px] rounded-md bg-indigo-500 text-white font-semibold shadow-sm shadow-indigo-900/40 hover:bg-indigo-400 disabled:opacity-50 transition';

const secondaryButtonClass =
    'px-2 py-1 text-xs rounded-md border border-slate-600 text-slate-200 hover:bg-slate-800 transition';

const dangerButtonClass =
    'px-2 py-1 text-xs rounded-md border border-red-500 text-red-400 hover:bg-red-900/30 transition';

const statsButtonClass =
    'px-2 py-1 text-[11px] rounded-md border border-indigo-500 text-indigo-300 hover:bg-indigo-900/30 transition';

const badgePlatformClass =
    'px-2 py-0.5 rounded-full text-[10px] font-medium bg-indigo-900/60 text-indigo-200 border border-indigo-500/40';
</script>

<template>

    <Head title="Sources" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-950 text-slate-100">
            <!-- HEADER -->
            <section class="border-b border-slate-800 bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900">
                <div class="w-[95%] mx-auto pt-8 pb-10">
                    <div
                        :class="shellCardClass + ' flex flex-col md:flex-row md:items-center md:justify-between gap-4'">
                        <!-- Bloc gauche -->
                        <div class="space-y-2">
                            <p
                                class="inline-flex items-center rounded-full border border-indigo-500/40 bg-indigo-500/10 px-3 py-1 text-xs md:text-sm font-medium text-indigo-200 uppercase tracking-[0.15em]">
                                Espace – Sources
                            </p>
                            <div>
                                <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">
                                    Sources
                                </h1>
                                <p class="mt-1 text-xs md:text-sm text-slate-400">
                                    Connectez vos campagnes et vos liens à des emplacements précis (bio, description,
                                    newsletter…).
                                </p>
                            </div>
                        </div>

                        <!-- Bloc droit : mini résumé -->
                        <div class="flex flex-col items-start md:items-end gap-2 text-xs">
                            <p class="text-slate-400">
                                Campagnes actives :
                                <span class="font-semibold text-slate-100">
                                    {{ campaigns.total }}
                                </span>
                            </p>
                            <p class="text-[11px] text-slate-500">
                                Ajoutez des sources par campagne, puis liez vos liens trackés pour suivre chaque
                                emplacement.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CONTENU -->
            <main class="w-[95%] mx-auto pt-8 pb-12 space-y-8">
                <!-- Bloc création de source -->
                <section :class="bigCardClass">
                    <div class="text-slate-50 space-y-6 text-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold">
                                    Ajouter une source à une campagne
                                </h3>
                                <p class="mt-1 text-xs text-slate-400">
                                    Exemple : « Bio TikTok », « Description YouTube », « Newsletter du dimanche », etc.
                                </p>
                            </div>
                        </div>

                        <form @submit.prevent="createSource" class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                            <!-- Campagne -->
                            <div class="md:col-span-1">
                                <label class="block text-xs font-medium text-slate-300 mb-1">
                                    Campagne
                                </label>
                                <select v-model="createForm.campaign_id"
                                    class="block w-full rounded-md border border-slate-700 bg-slate-950 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option v-for="campaign in campaigns.data" :key="campaign.id" :value="campaign.id">
                                        {{ campaign.name }}
                                    </option>
                                </select>
                                <div v-if="createForm.errors.campaign_id" class="text-xs text-red-400 mt-1">
                                    {{ createForm.errors.campaign_id }}
                                </div>
                            </div>

                            <!-- Nom de la source -->
                            <div class="md:col-span-1">
                                <label class="block text-xs font-medium text-slate-300 mb-1">
                                    Nom de la source
                                </label>
                                <input v-model="createForm.name" type="text"
                                    class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-950 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="TikTok bio, YouTube description..." />
                                <div v-if="createForm.errors.name" class="text-xs text-red-400 mt-1">
                                    {{ createForm.errors.name }}
                                </div>
                            </div>

                            <!-- Plateforme -->
                            <div class="md:col-span-1">
                                <label class="block text-xs font-medium text-slate-300 mb-1">
                                    Plateforme (optionnel)
                                </label>
                                <input v-model="createForm.platform" type="text"
                                    class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-950 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="TikTok, YouTube, Newsletter..." />
                                <div v-if="createForm.errors.platform" class="text-xs text-red-400 mt-1">
                                    {{ createForm.errors.platform }}
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-1">
                                <label class="block text-xs font-medium text-slate-300 mb-1">
                                    Notes (optionnel)
                                </label>
                                <textarea v-model="createForm.notes" rows="2"
                                    class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-950 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Ex : lien dans la bio TikTok avec CTA spécial..." />
                                <div v-if="createForm.errors.notes" class="text-xs text-red-400 mt-1">
                                    {{ createForm.errors.notes }}
                                </div>
                            </div>

                            <div class="md:col-span-4 flex justify-end">
                                <button type="submit" :class="primaryButtonClass" :disabled="createForm.processing">
                                    Ajouter la source
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- Bloc campagnes + sources -->
                <section :class="bigCardClass">
                    <div class="text-slate-50 text-sm">
                        <h3 class="text-sm font-semibold mb-4">
                            Vos campagnes & sources
                        </h3>

                        <div v-if="!campaigns.data.length" class="text-xs text-slate-400">
                            Aucune campagne pour le moment. Créez-en d'abord une dans l’onglet Campagnes.
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="campaign in campaigns.data" :key="campaign.id"
                                class="border border-slate-800 rounded-lg p-4 bg-slate-950/50">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <div class="font-semibold text-slate-50">
                                            {{ campaign.name }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ campaign.sources.length }} source(s)
                                        </div>
                                    </div>
                                </div>

                                <div v-if="!campaign.sources.length" class="text-xs text-slate-400">
                                    Aucune source pour cette campagne.
                                </div>

                                <div v-else class="mt-2 space-y-2">
                                    <div v-for="source in campaign.sources" :key="source.id"
                                        class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 text-xs bg-slate-950/70 rounded-md px-3 py-3">
                                        <!-- Infos source + tracked links + attach -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-slate-50">
                                                    {{ source.name }}
                                                </span>
                                                <span v-if="source.platform" :class="badgePlatformClass">
                                                    {{ source.platform }}
                                                </span>
                                            </div>

                                            <div v-if="source.notes" class="text-xs text-slate-400 mt-1">
                                                {{ source.notes }}
                                            </div>

                                            <!-- Liste des tracked links -->
                                            <div
                                                v-if="source.tracked_links && source.tracked_links.length"
                                                class="mt-2 space-y-2"
                                            >
                                                <div
                                                    v-for="tracked in source.tracked_links"
                                                    :key="tracked.id"
                                                    class="rounded-xl border border-slate-800 bg-slate-950/70 px-3 py-3 text-xs text-slate-100 space-y-2 w-full sm:max-w-2xl"
                                                >
                                                    <div class="flex flex-col gap-1">
                                                        <p class="text-[11px] font-semibold text-slate-300 uppercase tracking-[0.2em]">
                                                            Lien à coller pour {{ source.name }}
                                                        </p>
                                                        <p class="text-xs font-medium text-slate-200">
                                                            {{ tracked.link?.title ?? 'Lien sans titre' }}
                                                        </p>
                                                        <p class="font-mono text-slate-50 text-sm truncate">
                                                            {{ trackingUrl(tracked) }}
                                                        </p>
                                                        <p class="text-[11px] text-slate-400">
                                                            Utilise exactement ce lien pour suivre les clics de cette source.
                                                        </p>
                                                    </div>
                                                    <div class="flex justify-end">
                                                        <button
                                                            type="button"
                                                            class="inline-flex items-center gap-2 rounded-md border border-indigo-500 px-3 py-1 text-[11px] font-semibold text-indigo-200 hover:bg-indigo-900/30 transition"
                                                            @click="copyTrackedLink(tracked)"
                                                        >
                                                            Copier le lien
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Ajouter un lien tracké -->
                                            <div class="mt-3 space-y-1 text-[11px]">
                                                <div class="font-medium text-slate-400">
                                                    Ajouter un lien tracké à cette source
                                                </div>

                                                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                                    <select v-model="selectedLinks[source.id]"
                                                        class="flex-1 max-w-xs sm:max-w-sm lg:max-w-md rounded-md border border-slate-700 bg-slate-950 text-xs text-slate-100">
                                                        <option value="">
                                                            Choisir un lien…
                                                        </option>
                                                        <option v-for="link in links" :key="link.id" :value="link.id">
                                                            {{ link.title }} – {{ link.destination_url }}
                                                        </option>
                                                    </select>

                                                    <button type="button" :class="tinyPrimaryButtonClass"
                                                        :disabled="trackedLinkForm.processing"
                                                        @click="attachLinkToSource(source)">
                                                        Connecter
                                                    </button>
                                                </div>

                                                <div v-if="trackedLinkForm.errors.link_id"
                                                    class="text-xs text-red-400 mt-1">
                                                    {{ trackedLinkForm.errors.link_id }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex flex-row items-center gap-2 shrink-0">
                                            <button type="button" :class="secondaryButtonClass"
                                                @click="openEditModal(source)">
                                                Éditer
                                            </button>

                                            <button type="button" :class="dangerButtonClass"
                                                @click="deleteSource(source)" :disabled="deleteForm.processing">
                                                Supprimer
                                            </button>

                                            <button type="button" :class="statsButtonClass"
                                                @click="goToAnalytics(source)">
                                                Stats
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <Pagination :links="campaigns.links" />
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <!-- Toast copie -->
        <transition name="fade">
            <div
                v-if="showToast"
                class="fixed bottom-4 right-4 z-50 px-4 py-2 rounded-md shadow-lg bg-slate-950 border border-slate-700 text-white text-xs flex items-center gap-2"
            >
                <span>{{ toastMessage }}</span>
            </div>
        </transition>

        <!-- Modale d'édition -->
        <div v-if="isEditOpen && editingSource" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-slate-950 border border-slate-800 rounded-xl shadow-xl shadow-slate-950/60 w-full max-w-lg">
                <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-slate-50">
                        Éditer la source
                    </h3>
                    <button type="button" class="text-slate-400 hover:text-slate-200" @click="closeEditModal">
                        ✕
                    </button>
                </div>

                <div class="px-6 py-4 space-y-4 text-sm text-slate-50">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">
                            Nom de la source
                        </label>
                        <input v-model="editForm.name" type="text"
                            class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-900 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                        <div v-if="editForm.errors.name" class="text-xs text-red-400 mt-1">
                            {{ editForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">
                            Plateforme (optionnel)
                        </label>
                        <input v-model="editForm.platform" type="text"
                            class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-900 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                        <div v-if="editForm.errors.platform" class="text-xs text-red-400 mt-1">
                            {{ editForm.errors.platform }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">
                            Notes (optionnel)
                        </label>
                        <textarea v-model="editForm.notes" rows="3"
                            class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-900 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                        <div v-if="editForm.errors.notes" class="text-xs text-red-400 mt-1">
                            {{ editForm.errors.notes }}
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-800 flex justify-end gap-2">
                    <button type="button" :class="secondaryButtonClass" @click="closeEditModal">
                        Annuler
                    </button>
                    <button type="button" :class="primaryButtonClass" @click="updateSource"
                        :disabled="editForm.processing">
                        Sauvegarder
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease-out, transform 0.2s ease-out;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(4px);
}
</style>
