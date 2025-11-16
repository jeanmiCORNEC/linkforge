<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';

const { links, filters } = defineProps({
    links: {
        type: Object, // paginator
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// --- Formulaire de création de lien ---
const createForm = useForm({
    title: '',
    destination_url: '',
});

const createLink = () => {
    createForm.post(route('links.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset('title', 'destination_url');
        },
    });
};

// --- Suppression de lien ---
const deleteForm = useForm({});

const deleteLink = (link) => {
    if (!confirm(`Supprimer le lien "${link.title}" ?`)) return;

    deleteForm.delete(route('links.destroy', link.id), {
        preserveScroll: true,
    });
};

// --- Toggle actif / inactif ---
const toggleLink = (link) => {
    router.patch(route('links.toggle', link.id), {}, { preserveScroll: true });
};

// --- Édition (modale) ---
const isEditOpen = ref(false);
const editingLink = ref(null);

const editForm = useForm({
    title: '',
    destination_url: '',
});

const openEditModal = (link) => {
    editingLink.value = link;
    editForm.clearErrors();
    editForm.title = link.title;
    editForm.destination_url = link.destination_url;
    isEditOpen.value = true;
};

const closeEditModal = () => {
    isEditOpen.value = false;
};

const updateLink = () => {
    if (!editingLink.value) return;

    editForm.put(route('links.update', editingLink.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditOpen.value = false;
        },
    });
};

// --- Helpers : tracked link par défaut + URL courte ---
const getDefaultTrackedLink = (link) => {
    if (!link.tracked_links || !link.tracked_links.length) {
        return null;
    }
    const withoutSource = link.tracked_links.find((t) => t.source_id === null);
    return withoutSource ?? link.tracked_links[0];
};

const getShortUrlForLink = (link) => {
    const tracked = getDefaultTrackedLink(link);
    if (!tracked) return '';
    return route('links.redirect', { tracking_key: tracked.tracking_key });
};

// --- Filtre statut (Tous / Actifs / Inactifs) ---
const applyFilter = (status) => {
    router.get(
        route('links.index'),
        { status },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

// --- Copy to clipboard + toast ---
const showToast = ref(false);
const toastMessage = ref('');

const showCopyToast = (message = 'Lien copié dans le presse-papier') => {
    toastMessage.value = message;
    showToast.value = true;

    setTimeout(() => {
        showToast.value = false;
    }, 2000);
};

const copyToClipboard = async (text) => {
    if (!text) return;

    try {
        await navigator.clipboard.writeText(text);
        showCopyToast();
    } catch (e) {
        console.error('Erreur copy clipboard', e);
        showCopyToast('Impossible de copier le lien');
    }
};

/* ---------- Styles DA LinkForge ---------- */

const cardClass =
    'rounded-xl border border-slate-800 bg-slate-900/70 shadow-md shadow-slate-950/40';

const primaryButtonClass =
    'inline-flex items-center rounded-md bg-indigo-500 px-4 py-2 text-xs font-semibold ' +
    'text-white shadow-sm shadow-indigo-900/40 hover:bg-indigo-400 disabled:opacity-50 transition';

const secondaryButtonClass =
    'px-2 py-1 text-xs rounded-md border border-slate-600 text-slate-200 ' +
    'hover:bg-slate-800 transition';

const dangerButtonClass =
    'px-2 py-1 text-xs rounded-md border border-red-500 text-red-400 ' +
    'hover:bg-red-900/30 transition';

const warningButtonClass =
    'px-2 py-1 text-xs rounded-md border border-amber-500 text-amber-300 ' +
    'hover:bg-amber-900/20 transition';

// Pills filtre (Tous / actifs / inactifs)
const pillBase =
    'inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition';

const pillClasses = (value) => {
    const active = (filters.status || 'all') === value;
    return active
        ? `${pillBase} bg-indigo-500 text-white shadow-sm shadow-indigo-900/40`
        : `${pillBase} bg-slate-900/70 text-slate-300 border border-slate-700 hover:border-indigo-500 hover:text-slate-50`;
};
const successButtonClass =
    'px-2 py-1 text-xs rounded-md border border-emerald-500 text-emerald-300 hover:bg-emerald-900/30 transition';

const statsButtonClass =
    'px-2 py-1 text-[11px] rounded-md border border-indigo-500 text-indigo-300 hover:bg-indigo-900/30 transition';

</script>

<template>

    <Head title="Liens" />

    <AuthenticatedLayout>
        <template #header>
            <!-- même gabarit que le dashboard -->
            <h2 class="text-4xl font-bold text-slate-50 tracking-tight">
                Liens
            </h2>
        </template>

        <div class="py-8">
            <div class="w-[95%] mx-auto space-y-8">
                <!-- Bloc création de lien -->
                <div :class="cardClass">
                    <div class="p-6 text-slate-50">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-semibold">
                                    Créer un lien tracké
                                </h3>
                                <p class="mt-1 text-xs text-slate-400">
                                    Raccourcissez et trackez vos URLs en quelques secondes.
                                </p>
                            </div>
                        </div>

                        <form @submit.prevent="createLink" class="space-y-4 text-sm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-300">
                                        Titre
                                    </label>
                                    <input v-model="createForm.title" type="text"
                                        class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-950 text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                        placeholder="Lien setup vidéo TikTok" />
                                    <div v-if="createForm.errors.title" class="text-xs text-red-400 mt-1">
                                        {{ createForm.errors.title }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-300">
                                        URL de destination
                                    </label>
                                    <input v-model="createForm.destination_url" type="url"
                                        class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-950 text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                        placeholder="https://www.amazon.fr/..." />
                                    <div v-if="createForm.errors.destination_url" class="text-xs text-red-400 mt-1">
                                        {{ createForm.errors.destination_url }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <button type="submit" :class="primaryButtonClass" :disabled="createForm.processing">
                                    Créer le lien
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bloc liste des liens -->
                <div :class="cardClass">
                    <div class="p-6 text-slate-50">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                            <h3 class="text-sm font-semibold">
                                Vos liens
                            </h3>

                            <!-- Filtre sous forme de pills -->
                            <div class="flex items-center gap-3 text-xs">
                                <span class="text-slate-400">Filtrer :</span>
                                <div class="flex items-center gap-2">
                                    <button type="button" :class="pillClasses('all')" @click="applyFilter('all')">
                                        Tous
                                    </button>
                                    <button type="button" :class="pillClasses('active')" @click="applyFilter('active')">
                                        Actifs
                                    </button>
                                    <button type="button" :class="pillClasses('inactive')"
                                        @click="applyFilter('inactive')">
                                        Inactifs
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="!links.data.length" class="text-xs text-slate-400">
                            Aucun lien pour le moment. Créez-en un au-dessus 👆
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead>
                                    <tr
                                        class="bg-slate-900/80 text-left text-[11px] font-medium text-slate-400 uppercase tracking-wider">
                                        <th class="px-4 py-3">Titre</th>
                                        <th class="px-4 py-3">Lien court</th>
                                        <th class="px-4 py-3">URL de destination</th>
                                        <th class="px-4 py-3">Clics</th>
                                        <th class="px-4 py-3">Créé le</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    <tr v-for="link in links.data" :key="link.id"
                                        class="odd:bg-slate-950/60 even:bg-slate-950/30">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium">
                                                    {{ link.title }}
                                                </span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium border"
                                                    :class="link.is_active
                                                        ? 'bg-emerald-900/40 text-emerald-300 border-emerald-500/40'
                                                        : 'bg-slate-800 text-slate-200 border-slate-600'
                                                        ">
                                                    {{ link.is_active ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Lien court + bouton Copier -->
                                        <td class="px-4 py-3 text-xs text-slate-200">
                                            <div class="flex items-center gap-2">
                                                <span class="truncate max-w-[220px]">
                                                    {{ getShortUrlForLink(link) || 'Aucun tracking key' }}
                                                </span>

                                                <button v-if="getShortUrlForLink(link)" type="button"
                                                    class="inline-flex items-center px-2 py-1 text-[11px] rounded-md border border-indigo-500 text-indigo-300 hover:bg-indigo-900/30 transition"
                                                    @click="copyToClipboard(getShortUrlForLink(link))">
                                                    Copier
                                                </button>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3">
                                            <a :href="link.destination_url" target="_blank"
                                                class="text-indigo-300 hover:text-indigo-200 hover:underline break-all">
                                                {{ link.destination_url }}
                                            </a>
                                        </td>

                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ link.clicks_count ?? 0 }} clics
                                        </td>

                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ new Date(link.created_at).toLocaleString() }}
                                        </td>

                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-right space-x-2 flex items-center justify-end">

                                            <!-- Toggle actif / inactif -->
                                            <button type="button"
                                                :class="link.is_active ? warningButtonClass : successButtonClass"
                                                @click="toggleLink(link)">
                                                {{ link.is_active ? 'Désactiver' : 'Activer' }}
                                            </button>

                                            <!-- Bouton Éditer -->
                                            <button type="button" :class="secondaryButtonClass"
                                                @click="openEditModal(link)">
                                                Éditer
                                            </button>

                                            <!-- Supprimer -->
                                            <button type="button" :class="dangerButtonClass" @click="deleteLink(link)"
                                                :disabled="deleteForm.processing">
                                                Supprimer
                                            </button>

                                            <!-- Voir les stats -->
                                            <Link :href="route('links.analytics.show', link.id)"
                                                :class="statsButtonClass">
                                            Stats
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <Pagination :links="links.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modale d'édition -->
        <div v-if="isEditOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div
                class="bg-slate-950 border border-slate-800 rounded-xl shadow-xl shadow-slate-950/60 w-full max-w-lg p-6">
                <h3 class="text-sm font-semibold text-slate-50 mb-4">
                    Éditer le lien
                </h3>

                <form @submit.prevent="updateLink" class="space-y-4 text-sm text-slate-50">
                    <div>
                        <label class="block text-xs font-medium text-slate-300">
                            Titre
                        </label>
                        <input v-model="editForm.title" type="text"
                            class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-900 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                        <div v-if="editForm.errors.title" class="text-xs text-red-400 mt-1">
                            {{ editForm.errors.title }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300">
                            URL de destination
                        </label>
                        <input v-model="editForm.destination_url" type="url"
                            class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-900 text-sm text-slate-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                        <div v-if="editForm.errors.destination_url" class="text-xs text-red-400 mt-1">
                            {{ editForm.errors.destination_url }}
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button type="button" :class="secondaryButtonClass" @click="closeEditModal">
                            Annuler
                        </button>

                        <button type="submit" :class="primaryButtonClass" :disabled="editForm.processing">
                            Sauvegarder
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Toast copie -->
        <transition name="fade">
            <div v-if="showToast"
                class="fixed bottom-4 right-4 z-50 px-4 py-2 rounded-md shadow-lg bg-slate-950 border border-slate-700 text-white text-xs flex items-center gap-2">
                <span>{{ toastMessage }}</span>
            </div>
        </transition>
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
