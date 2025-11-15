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

// Pré-sélectionner la première campagne dans le select
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
    router.get(
        route('sources.analytics.show', source.id),
        {},
        {
            preserveScroll: true,
        },
    );
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

    trackedLinkForm.post(
        route('sources.tracked-links.store', source.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                // Optionnel : reset la valeur
                // selectedLinks.value[source.id] = '';
            },
        },
    );
};
</script>

<template>
    <Head title="Sources" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Sources
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Bloc création de source -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 space-y-6">
                        <h3 class="text-lg font-semibold">
                            Ajouter une source à une campagne
                        </h3>

                        <form
                            @submit.prevent="createSource"
                            class="grid grid-cols-1 md:grid-cols-4 gap-4"
                        >
                            <!-- Campagne -->
                            <div class="md:col-span-1">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                >
                                    Campagne
                                </label>
                                <select
                                    v-model="createForm.campaign_id"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-700
                                           dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                           focus:border-indigo-500 text-sm"
                                >
                                    <option
                                        v-for="campaign in campaigns.data"
                                        :key="campaign.id"
                                        :value="campaign.id"
                                    >
                                        {{ campaign.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="createForm.errors.campaign_id"
                                    class="text-sm text-red-500 mt-1"
                                >
                                    {{ createForm.errors.campaign_id }}
                                </div>
                            </div>

                            <!-- Nom de la source -->
                            <div class="md:col-span-1">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                >
                                    Nom de la source
                                </label>
                                <input
                                    v-model="createForm.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                           dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                           focus:border-indigo-500 text-sm"
                                    placeholder="TikTok bio, YouTube description..."
                                />
                                <div
                                    v-if="createForm.errors.name"
                                    class="text-sm text-red-500 mt-1"
                                >
                                    {{ createForm.errors.name }}
                                </div>
                            </div>

                            <!-- Plateforme -->
                            <div class="md:col-span-1">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                >
                                    Plateforme (optionnel)
                                </label>
                                <input
                                    v-model="createForm.platform"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                           dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                           focus:border-indigo-500 text-sm"
                                    placeholder="TikTok, YouTube, Newsletter..."
                                />
                                <div
                                    v-if="createForm.errors.platform"
                                    class="text-sm text-red-500 mt-1"
                                >
                                    {{ createForm.errors.platform }}
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-1">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                >
                                    Notes (optionnel)
                                </label>
                                <textarea
                                    v-model="createForm.notes"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                           dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                           focus:border-indigo-500 text-sm"
                                    placeholder="Ex : lien dans la bio TikTok avec CTA spécial..."
                                />
                                <div
                                    v-if="createForm.errors.notes"
                                    class="text-sm text-red-500 mt-1"
                                >
                                    {{ createForm.errors.notes }}
                                </div>
                            </div>

                            <div class="md:col-span-4 flex justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent
                                           rounded-md font-semibold text-xs text-white uppercase tracking-widest
                                           hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-700
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                           dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                                    :disabled="createForm.processing"
                                >
                                    Ajouter la source
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bloc campagnes + sources -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">
                            Vos campagnes
                        </h3>

                        <div
                            v-if="!campaigns.data.length"
                            class="text-sm text-gray-500 dark:text-gray-400"
                        >
                            Aucune campagne pour le moment. Créez-en d'abord une dans l’onglet Campagnes.
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="campaign in campaigns.data"
                                :key="campaign.id"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-900/10 dark:bg-gray-900/40"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <div class="font-semibold">
                                            {{ campaign.name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ campaign.sources.length }} source(s)
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="!campaign.sources.length"
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    Aucune source pour cette campagne.
                                </div>

                                <div v-else class="mt-2 space-y-2">
                                    <div
                                        v-for="source in campaign.sources"
                                        :key="source.id"
                                        class="flex items-start justify-between text-sm bg-gray-800/60 rounded-md px-3 py-2"
                                    >
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium">
                                                    {{ source.name }}
                                                </span>
                                                <span
                                                    v-if="source.platform"
                                                    class="px-2 py-0.5 rounded-full text-[10px] font-medium
                                                           bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-200"
                                                >
                                                    {{ source.platform }}
                                                </span>
                                            </div>
                                            <div
                                                v-if="source.notes"
                                                class="text-xs text-gray-400 mt-1"
                                            >
                                                {{ source.notes }}
                                            </div>

                                            <!-- Liste des tracked links éventuels -->
                                            <div
                                                v-if="source.tracked_links && source.tracked_links.length"
                                                class="mt-2 space-y-1"
                                            >
                                                <div
                                                    v-for="tracked in source.tracked_links"
                                                    :key="tracked.id"
                                                    class="flex items-center justify-between text-xs bg-gray-900/60 rounded px-2 py-1"
                                                >
                                                    <div class="truncate">
                                                        <span class="font-medium">
                                                            {{ tracked.link?.title || 'Lien' }}
                                                        </span>
                                                        <span class="text-gray-400 ml-1">
                                                            ({{ tracked.tracking_key }})
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Ajouter un lien tracké à cette source -->
                                            <div class="mt-3 space-y-1 text-xs">
                                                <div class="font-medium text-gray-400">
                                                    Ajouter un lien tracké à cette source
                                                </div>

                                                <div class="flex flex-col sm:flex-row gap-2">
                                                    <select
                                                        v-model="selectedLinks[source.id]"
                                                        class="flex-1 rounded-md border-gray-300 dark:border-gray-700
                                                               dark:bg-gray-900 dark:text-gray-100 text-xs"
                                                    >
                                                        <option value="">
                                                            Choisir un lien…
                                                        </option>
                                                        <option
                                                            v-for="link in links"
                                                            :key="link.id"
                                                            :value="link.id"
                                                        >
                                                            {{ link.title }} – {{ link.destination_url }}
                                                        </option>
                                                    </select>

                                                    <button
                                                        type="button"
                                                        class="px-3 py-1 rounded-md bg-indigo-600 text-white text-xs font-semibold
                                                               hover:bg-indigo-500 disabled:opacity-50"
                                                        :disabled="trackedLinkForm.processing"
                                                        @click="attachLinkToSource(source)"
                                                    >
                                                        Connecter
                                                    </button>
                                                </div>

                                                <div
                                                    v-if="trackedLinkForm.errors.link_id"
                                                    class="text-red-500 mt-1"
                                                >
                                                    {{ trackedLinkForm.errors.link_id }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex flex-col items-end gap-2 ml-4">
                                            <!-- Bouton stats -->
                                            <button
                                                type="button"
                                                class="px-2 py-1 text-xs rounded-md border border-indigo-500
                                                       text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30"
                                                @click="goToAnalytics(source)"
                                            >
                                                Stats
                                            </button>

                                            <!-- Éditer / Supprimer -->
                                            <div class="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded-md border border-gray-500
                                                           text-gray-700 dark:text-gray-200 dark:border-gray-400
                                                           hover:bg-gray-50 dark:hover:bg-gray-700/60"
                                                    @click="openEditModal(source)"
                                                >
                                                    Éditer
                                                </button>

                                                <button
                                                    type="button"
                                                    class="px-2 py-1 text-xs rounded-md border border-red-600
                                                           text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30"
                                                    @click="deleteSource(source)"
                                                    :disabled="deleteForm.processing"
                                                >
                                                    Supprimer
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <Pagination :links="campaigns.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale d'édition -->
        <div
            v-if="isEditOpen && editingSource"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-lg">
                <div
                    class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center"
                >
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Éditer la source
                    </h3>
                    <button
                        type="button"
                        class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                        @click="closeEditModal"
                    >
                        ✕
                    </button>
                </div>

                <div class="px-6 py-4 space-y-4 text-sm">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Nom de la source
                        </label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                   dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                   focus:border-indigo-500 text-sm"
                        />
                        <div
                            v-if="editForm.errors.name"
                            class="text-sm text-red-500 mt-1"
                        >
                            {{ editForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Plateforme (optionnel)
                        </label>
                        <input
                            v-model="editForm.platform"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                   dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                   focus:border-indigo-500 text-sm"
                        />
                        <div
                            v-if="editForm.errors.platform"
                            class="text-sm text-red-500 mt-1"
                        >
                            {{ editForm.errors.platform }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                        >
                            Notes (optionnel)
                        </label>
                        <textarea
                            v-model="editForm.notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                   dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                   focus:border-indigo-500 text-sm"
                        />
                        <div
                            v-if="editForm.errors.notes"
                            class="text-sm text-red-500 mt-1"
                        >
                            {{ editForm.errors.notes }}
                        </div>
                    </div>
                </div>

                <div
                    class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2"
                >
                    <button
                        type="button"
                        class="px-4 py-2 text-xs rounded-md border border-gray-300 dark:border-gray-600
                               text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60"
                        @click="closeEditModal"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 text-xs rounded-md bg-indigo-600 text-white font-semibold
                               hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-700
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                               dark:focus:ring-offset-gray-800"
                        @click="updateSource"
                        :disabled="editForm.processing"
                    >
                        Sauvegarder
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
