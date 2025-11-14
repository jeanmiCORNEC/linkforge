<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

const props = defineProps({
    campaigns: {
        type: Object, // paginator
        required: true,
    },
});

// ---------- Création ----------
const createForm = useForm({
    name: '',
    notes: '',
});

const createCampaign = () => {
    createForm.post(route('campaigns.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset('name', 'notes');
        },
    });
};

// ---------- Edition (modale) ----------
const showEditModal = ref(false);
const editingCampaign = ref(null);

const editForm = useForm({
    name: '',
    notes: '',
    status: 'active',
    starts_at: '',
    ends_at: '',
});

const openEditModal = (campaign) => {
    editingCampaign.value = campaign;

    editForm.name = campaign.name ?? '';
    editForm.notes = campaign.notes ?? '';
    editForm.status = campaign.status ?? 'active';
    editForm.starts_at = campaign.starts_at
        ? campaign.starts_at.substring(0, 10)
        : '';
    editForm.ends_at = campaign.ends_at
        ? campaign.ends_at.substring(0, 10)
        : '';

    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingCampaign.value = null;
    editForm.reset();
};

const updateCampaign = () => {
    if (!editingCampaign.value) return;

    editForm.put(route('campaigns.update', editingCampaign.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
    });
};

// ---------- Archive toggle ----------
const toggleArchive = (campaign) => {
    router.patch(
        route('campaigns.archive', campaign.id),
        {},
        {
            preserveScroll: true,
        },
    );
};

// ---------- Suppression (soft delete) ----------
const deleteForm = useForm({});

const deleteCampaign = (campaign) => {
    if (!confirm(`Supprimer la campagne "${campaign.name}" ?`)) return;

    deleteForm.delete(route('campaigns.destroy', campaign.id), {
        preserveScroll: true,
    });
};

</script>

<template>
    <Head title="Campagnes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Campagnes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Création de campagne -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">
                            Créer une campagne
                        </h3>

                        <form @submit.prevent="createCampaign" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nom de la campagne
                                </label>
                                <input
                                    v-model="createForm.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                           dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                           focus:border-indigo-500 sm:text-sm"
                                    placeholder="Lancement ebook, Pack presets TikTok..."
                                />
                                <div v-if="createForm.errors.name" class="text-sm text-red-500 mt-1">
                                    {{ createForm.errors.name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Notes (optionnel)
                                </label>
                                <textarea
                                    v-model="createForm.notes"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                           dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                           focus:border-indigo-500 sm:text-sm"
                                    placeholder="Ex : Objectif, public cible, promo en cours..."
                                />
                                <div v-if="createForm.errors.notes" class="text-sm text-red-500 mt-1">
                                    {{ createForm.errors.notes }}
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent
                                           rounded-md font-semibold text-xs text-white uppercase tracking-widest
                                           hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-700
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                           dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                                    :disabled="createForm.processing"
                                >
                                    Créer la campagne
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des campagnes -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">
                            Vos campagnes
                        </h3>

                        <div v-if="!campaigns.data.length" class="text-sm text-gray-500 dark:text-gray-400">
                            Aucune campagne pour le moment. Créez-en une au-dessus 👆
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="campaign in campaigns.data"
                                :key="campaign.id"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4
                                       bg-gray-50 dark:bg-gray-900/40 flex flex-col sm:flex-row
                                       sm:items-center sm:justify-between gap-4"
                            >
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-semibold">
                                            {{ campaign.name }}
                                        </h4>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-medium"
                                            :class="campaign.status === 'active'
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                                : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-200'"
                                        >
                                            {{ campaign.status === 'active' ? 'Active' : 'Archivée' }}
                                        </span>
                                    </div>

                                    <p v-if="campaign.notes" class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ campaign.notes }}
                                    </p>

                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ campaign.sources_count }} source(s)
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 justify-end">
                                    <!-- Archive / Réactive -->
                                    <button
                                        type="button"
                                        class="px-2 py-1 text-xs rounded-md border
                                               border-yellow-500 text-yellow-700
                                               hover:bg-yellow-50 dark:hover:bg-yellow-900/20"
                                        @click="toggleArchive(campaign)"
                                    >
                                        {{ campaign.status === 'active' ? 'Archiver' : 'Réactiver' }}
                                    </button>

                                    <!-- Edit -->
                                    <button
                                        type="button"
                                        class="px-2 py-1 text-xs rounded-md border border-gray-500
                                               text-gray-700 dark:text-gray-200 dark:border-gray-400
                                               hover:bg-gray-50 dark:hover:bg-gray-700/60"
                                        @click="openEditModal(campaign)"
                                    >
                                        Éditer
                                    </button>

                                    <!-- Delete -->
                                    <button
                                        type="button"
                                        class="px-2 py-1 text-xs rounded-md border border-red-600
                                               text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30"
                                        @click="deleteCampaign(campaign)"
                                        :disabled="deleteForm.processing"
                                    >
                                        Supprimer
                                    </button>
                                </div>
                            </div>

                            <!-- Pagination -->
                           <Pagination :links="campaigns.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale d'édition -->
        <div
            v-if="showEditModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
                    Éditer la campagne
                </h3>

                <form @submit.prevent="updateCampaign" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nom de la campagne
                        </label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                   dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                   focus:border-indigo-500 sm:text-sm"
                        />
                        <div v-if="editForm.errors.name" class="text-sm text-red-500 mt-1">
                            {{ editForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Notes
                        </label>
                        <textarea
                            v-model="editForm.notes"
                            rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                   dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                   focus:border-indigo-500 sm:text-sm"
                        />
                        <div v-if="editForm.errors.notes" class="text-sm text-red-500 mt-1">
                            {{ editForm.errors.notes }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Statut
                            </label>
                            <select
                                v-model="editForm.status"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                       dark:bg-gray-900 dark:text-gray-100 text-sm"
                            >
                                <option value="active">Active</option>
                                <option value="archived">Archivée</option>
                            </select>
                            <div v-if="editForm.errors.status" class="text-sm text-red-500 mt-1">
                                {{ editForm.errors.status }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Début (optionnel)
                            </label>
                            <input
                                v-model="editForm.starts_at"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                       dark:bg-gray-900 dark:text-gray-100 text-sm"
                            />
                            <div v-if="editForm.errors.starts_at" class="text-sm text-red-500 mt-1">
                                {{ editForm.errors.starts_at }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Fin (optionnel)
                            </label>
                            <input
                                v-model="editForm.ends_at"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                       dark:bg-gray-900 dark:text-gray-100 text-sm"
                            />
                            <div v-if="editForm.errors.ends_at" class="text-sm text-red-500 mt-1">
                                {{ editForm.errors.ends_at }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button
                            type="button"
                            class="px-3 py-2 text-xs rounded-md border border-gray-300
                                   text-gray-700 dark:text-gray-200 dark:border-gray-600
                                   hover:bg-gray-50 dark:hover:bg-gray-700/60"
                            @click="closeEditModal"
                        >
                            Annuler
                        </button>

                        <button
                            type="submit"
                            class="px-4 py-2 text-xs rounded-md bg-indigo-600 text-white
                                   font-semibold hover:bg-indigo-500 disabled:opacity-50"
                            :disabled="editForm.processing"
                        >
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
