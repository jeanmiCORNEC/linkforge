<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link as InertiaLink } from '@inertiajs/vue3';
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
    editForm.starts_at = campaign.starts_at ? campaign.starts_at.substring(0, 10) : '';
    editForm.ends_at = campaign.ends_at ? campaign.ends_at.substring(0, 10) : '';

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
    router.patch(route('campaigns.archive', campaign.id), {}, { preserveScroll: true });
};

// ---------- Suppression (soft delete) ----------
const deleteForm = useForm({});

const deleteCampaign = (campaign) => {
    if (!confirm(`Supprimer la campagne "${campaign.name}" ?`)) return;

    deleteForm.delete(route('campaigns.destroy', campaign.id), {
        preserveScroll: true,
    });
};

/* ---------- Styles DA LinkForge (alignés sur links + analytics) ---------- */

const shellCardClass =
    'relative rounded-3xl border border-slate-800 bg-slate-950/80 px-6 py-5 shadow-xl shadow-indigo-900/30';

const bigCardClass =
    'rounded-xl border border-slate-800 bg-slate-950/70 p-6 shadow-xl shadow-indigo-900/30';

const primaryButtonClass =
    'inline-flex items-center rounded-md bg-indigo-500 px-4 py-2 text-xs font-semibold text-white ' +
    'shadow-sm shadow-indigo-900/40 hover:bg-indigo-400 disabled:opacity-50 transition';

const secondaryButtonClass =
    'px-2 py-1 text-xs rounded-md border border-slate-600 text-slate-200 hover:bg-slate-800 transition';

const warningButtonClass =
    'px-2 py-1 text-xs rounded-md border border-amber-500 text-amber-300 hover:bg-amber-900/20 transition';

const dangerButtonClass =
    'px-2 py-1 text-xs rounded-md border border-red-500 text-red-400 hover:bg-red-900/30 transition';

const successButtonClass =
    'px-2 py-1 text-xs rounded-md border border-emerald-500 text-emerald-300 hover:bg-emerald-900/30 transition';

const ghostBadgeClass =
    'px-2 py-0.5 rounded-full text-[10px] font-medium border';
</script>

<template>
    <Head title="Campagnes" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-950 text-slate-100">
            <!-- HEADER -->
            <section class="border-b border-slate-800 bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900">
                <div class="w-[95%] mx-auto pt-8 pb-10">
                    <div
                        :class="shellCardClass + ' flex flex-col md:flex-row md:items-center md:justify-between gap-4'"
                    >
                        <!-- Bloc gauche -->
                        <div class="space-y-2">
                            <p
                                class="inline-flex items-center rounded-full border border-indigo-500/40 bg-indigo-500/10 px-3 py-1 text-xs md:text-sm font-medium text-indigo-200 uppercase tracking-[0.15em]"
                            >
                                Espace – Campagnes
                            </p>
                            <div>
                                <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">
                                    Campagnes
                                </h1>
                                <p class="mt-1 text-xs md:text-sm text-slate-400">
                                    Regroupez vos liens par promo ou lancement pour suivre leurs performances.
                                </p>
                            </div>
                        </div>

                        <!-- Bloc droit : petit résumé -->
                        <div class="flex flex-col items-start md:items-end gap-2 text-xs">
                            <p class="text-slate-400">
                                Vous avez
                                <span class="font-semibold text-slate-100">
                                    {{ campaigns.total }}
                                </span>
                                campagne(s) au total.
                            </p>
                            <p class="text-[11px] text-slate-500">
                                Gérez le statut (active / archivée), les dates et accédez aux analytics en un clic.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CONTENU -->
            <main class="w-[95%] mx-auto pt-8 pb-12 space-y-8">
                <!-- Création de campagne -->
                <section :class="bigCardClass">
                    <div class="text-slate-50">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-semibold">
                                    Créer une campagne
                                </h3>
                                <p class="mt-1 text-xs text-slate-400">
                                    Regroupez vos liens par promo ou lancement pour suivre leurs performances.
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="createForm.errors.campaign"
                            class="rounded-lg border border-amber-500/60 bg-amber-500/10 px-3 py-2 text-xs text-amber-100"
                        >
                            {{ createForm.errors.campaign }}
                        </div>

                        <form @submit.prevent="createCampaign" class="space-y-4 text-sm">
                            <!-- grille compacte : nom (1/3) + notes (2/3) -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Nom de la campagne -->
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-medium text-slate-300">
                                        Nom de la campagne
                                    </label>
                                    <input
                                        v-model="createForm.name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-950 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Lancement ebook, Pack presets TikTok..."
                                    />
                                    <div v-if="createForm.errors.name" class="text-xs text-red-400 mt-1">
                                        {{ createForm.errors.name }}
                                    </div>
                                </div>

                                <!-- Notes optionnelles -->
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-slate-300">
                                        Notes (optionnel)
                                    </label>
                                    <textarea
                                        v-model="createForm.notes"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-950 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                                        placeholder="Objectif, promo, canal principal…"
                                    />
                                    <div v-if="createForm.errors.notes" class="text-xs text-red-400 mt-1">
                                        {{ createForm.errors.notes }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button
                                    type="submit"
                                    :class="primaryButtonClass"
                                    :disabled="createForm.processing"
                                >
                                    Créer la campagne
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- Liste des campagnes -->
                <section :class="bigCardClass">
                    <div class="text-slate-50">
                        <h3 class="text-sm font-semibold mb-4">
                            Vos campagnes
                        </h3>

                        <div v-if="!campaigns.data.length" class="text-xs text-slate-400">
                            Aucune campagne pour le moment. Créez-en une au-dessus 👆
                        </div>

                        <div v-else class="space-y-4">
                            <!-- Liste “Stripe-like” -->
                            <div class="divide-y divide-slate-800 rounded-lg bg-slate-950/40 border border-slate-900">
                                <div
                                    v-for="campaign in campaigns.data"
                                    :key="campaign.id"
                                    class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <!-- Infos campagne -->
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-semibold">
                                                {{ campaign.name }}
                                            </h4>
                                            <span
                                                :class="[
                                                    ghostBadgeClass,
                                                    campaign.status === 'active'
                                                        ? 'bg-emerald-900/40 text-emerald-300 border-emerald-500/40'
                                                        : 'bg-amber-900/30 text-amber-200 border-amber-500/40',
                                                ]"
                                            >
                                                {{ campaign.status === 'active' ? 'Active' : 'Archivée' }}
                                            </span>
                                        </div>

                                        <p v-if="campaign.notes" class="text-xs text-slate-400">
                                            {{ campaign.notes }}
                                        </p>

                                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-500">
                                            <span>
                                                {{ campaign.sources_count }} source(s) liée(s)
                                            </span>
                                            <span v-if="campaign.starts_at || campaign.ends_at">
                                                ·
                                            </span>
                                            <span v-if="campaign.starts_at || campaign.ends_at">
                                                Période :
                                                <span v-if="campaign.starts_at">
                                                    du {{ campaign.starts_at.substring(0, 10) }}
                                                </span>
                                                <span v-if="campaign.starts_at && campaign.ends_at">
                                                    au
                                                </span>
                                                <span v-if="campaign.ends_at">
                                                    {{ campaign.ends_at.substring(0, 10) }}
                                                </span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <!-- Archive / Réactive -->
                                        <button
                                            type="button"
                                            :class="campaign.status === 'active' ? warningButtonClass : successButtonClass"
                                            @click="toggleArchive(campaign)"
                                        >
                                            {{ campaign.status === 'active' ? 'Archiver' : 'Réactiver' }}
                                        </button>

                                        <!-- Edit -->
                                        <button
                                            type="button"
                                            :class="secondaryButtonClass"
                                            @click="openEditModal(campaign)"
                                        >
                                            Éditer
                                        </button>

                                        <!-- Delete -->
                                        <button
                                            type="button"
                                            :class="dangerButtonClass"
                                            @click="deleteCampaign(campaign)"
                                            :disabled="deleteForm.processing"
                                        >
                                            Supprimer
                                        </button>

                                        <!-- Stats -->
                                        <InertiaLink
                                            :href="route('campaigns.analytics.show', campaign.id)"
                                            class="px-2 py-1 text-xs rounded-md border border-indigo-500 text-indigo-300 hover:bg-indigo-900/30 transition"
                                        >
                                            Stats
                                        </InertiaLink>
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

        <!-- Modale d'édition -->
        <div
            v-if="showEditModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div
                class="bg-slate-950 border border-slate-800 rounded-xl shadow-xl shadow-slate-950/60 max-w-lg w-full p-6"
            >
                <h3 class="text-sm font-semibold mb-4 text-slate-50">
                    Éditer la campagne
                </h3>

                <form @submit.prevent="updateCampaign" class="space-y-4 text-slate-50 text-sm">
                    <div>
                        <label class="block text-xs font-medium text-slate-300">
                            Nom de la campagne
                        </label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-900 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <div v-if="editForm.errors.name" class="text-xs text-red-400 mt-1">
                            {{ editForm.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300">
                            Notes
                        </label>
                        <textarea
                            v-model="editForm.notes"
                            rows="2"
                            class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-900 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        />
                        <div v-if="editForm.errors.notes" class="text-xs text-red-400 mt-1">
                            {{ editForm.errors.notes }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300">
                                Statut
                            </label>
                            <select
                                v-model="editForm.status"
                                class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-900 text-sm text-slate-100"
                            >
                                <option value="active">Active</option>
                                <option value="archived">Archivée</option>
                            </select>
                            <div v-if="editForm.errors.status" class="text-xs text-red-400 mt-1">
                                {{ editForm.errors.status }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-300">
                                Début (optionnel)
                            </label>
                            <input
                                v-model="editForm.starts_at"
                                type="date"
                                class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-900 text-sm text-slate-100"
                            />
                            <div v-if="editForm.errors.starts_at" class="text-xs text-red-400 mt-1">
                                {{ editForm.errors.starts_at }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-300">
                                Fin (optionnel)
                            </label>
                            <input
                                v-model="editForm.ends_at"
                                type="date"
                                class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-900 text-sm text-slate-100"
                            />
                            <div v-if="editForm.errors.ends_at" class="text-xs text-red-400 mt-1">
                                {{ editForm.errors.ends_at }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" :class="secondaryButtonClass" @click="closeEditModal">
                            Annuler
                        </button>

                        <button
                            type="submit"
                            :class="primaryButtonClass"
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
