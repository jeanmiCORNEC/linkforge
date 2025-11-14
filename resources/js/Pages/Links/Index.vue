<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

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
    router.patch(
        route('links.toggle', link.id),
        {},
        {
            preserveScroll: true,
        },
    );
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

// --- URL courte affichée dans la table ---
const shortUrl = (link) => {
    const tracked = link.tracked_links && link.tracked_links.length
        ? link.tracked_links[0]
        : null;

    if (!tracked) return 'Aucun tracking key';

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

</script>

<template>

    <Head title="Liens" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Liens
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Bloc création de lien -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">
                                Créer un lien tracké
                            </h3>
                        </div>

                        <form @submit.prevent="createLink" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Titre
                                </label>
                                <input v-model="createForm.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                           dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                           focus:border-indigo-500 sm:text-sm" placeholder="Lien setup vidéo TikTok" />
                                <div v-if="createForm.errors.title" class="text-sm text-red-500 mt-1">
                                    {{ createForm.errors.title }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    URL de destination
                                </label>
                                <input v-model="createForm.destination_url" type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                           dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                           focus:border-indigo-500 sm:text-sm"
                                    placeholder="https://www.amazon.fr/..." />
                                <div v-if="createForm.errors.destination_url" class="text-sm text-red-500 mt-1">
                                    {{ createForm.errors.destination_url }}
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent
                                           rounded-md font-semibold text-xs text-white uppercase tracking-widest
                                           hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-700
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                           dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                                    :disabled="createForm.processing">
                                    Créer le lien
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bloc liste des liens -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">
                                Vos liens
                            </h3>

                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Filtrer :</span>
                                <select class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900
                                           dark:text-gray-100 text-sm" :value="filters.status || 'all'"
                                    @change="applyFilter($event.target.value)">
                                    <option value="all">Tous</option>
                                    <option value="active">Actifs</option>
                                    <option value="inactive">Inactifs</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="!links.data.length" class="text-sm text-gray-500 dark:text-gray-400">
                            Aucun lien pour le moment. Créez-en un au-dessus 👆
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-gray-900/40 text-left text-xs font-medium
                                               text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        <th class="px-4 py-3">Titre</th>
                                        <th class="px-4 py-3">Lien court</th>
                                        <th class="px-4 py-3">URL de destination</th>
                                        <th class="px-4 py-3">Clics</th>
                                        <th class="px-4 py-3">Créé le</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="link in links.data" :key="link.id" class="bg-white dark:bg-gray-800">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium">
                                                    {{ link.title }}
                                                </span>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium"
                                                    :class="link.is_active
                                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                                        : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200'">
                                                    {{ link.is_active ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3">
                                            <input type="text" :value="shortUrl(link)" readonly class="w-full rounded-md border-gray-300 dark:border-gray-700
                                                       dark:bg-gray-900 dark:text-gray-100 text-xs"
                                                @focus="$event.target.select()" />
                                        </td>

                                        <td class="px-4 py-3">
                                            <a :href="link.destination_url" target="_blank"
                                                class="text-indigo-500 hover:underline break-all">
                                                {{ link.destination_url }}
                                            </a>
                                        </td>

                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ link.clicks_count ?? 0 }} clics
                                        </td>

                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ new Date(link.created_at).toLocaleString() }}
                                        </td>

                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <!-- Toggle actif / inactif -->
                                            <button type="button"
                                                class="px-2 py-1 text-xs rounded-md border mr-2 transition"
                                                :class="link.is_active
                                                    ? 'border-yellow-500 text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/20'
                                                    : 'border-green-500 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20'" @click="toggleLink(link)">
                                                {{ link.is_active ? 'Désactiver' : 'Activer' }}
                                            </button>

                                            <!-- Bouton Éditer -->
                                            <button type="button" class="px-2 py-1 text-xs rounded-md border border-gray-500
               text-gray-700 dark:text-gray-200 dark:border-gray-400
               hover:bg-gray-50 dark:hover:bg-gray-700/60 mr-2" @click="openEditModal(link)">
                                                Éditer
                                            </button>

                                            <!-- Supprimer -->
                                            <button type="button" class="px-2 py-1 text-xs rounded-md border border-red-600
               text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30" @click="deleteLink(link)"
                                                :disabled="deleteForm.processing">
                                                Supprimer
                                            </button>
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
        </div><!-- Modale d'édition -->
        <div v-if="isEditOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Éditer le lien
                </h3>

                <form @submit.prevent="updateLink" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Titre
                        </label>
                        <input v-model="editForm.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                           dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                           focus:border-indigo-500 sm:text-sm" />
                        <div v-if="editForm.errors.title" class="text-sm text-red-500 mt-1">
                            {{ editForm.errors.title }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            URL de destination
                        </label>
                        <input v-model="editForm.destination_url" type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                           dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                           focus:border-indigo-500 sm:text-sm" />
                        <div v-if="editForm.errors.destination_url" class="text-sm text-red-500 mt-1">
                            {{ editForm.errors.destination_url }}
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button type="button" class="px-3 py-2 text-xs rounded-md border border-gray-300
                           text-gray-700 dark:text-gray-200 dark:border-gray-600
                           hover:bg-gray-50 dark:hover:bg-gray-700/60" @click="closeEditModal">
                            Annuler
                        </button>

                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent
                           rounded-md font-semibold text-xs text-white uppercase tracking-widest
                           hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-700
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                           dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                            :disabled="editForm.processing">
                            Sauvegarder
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
