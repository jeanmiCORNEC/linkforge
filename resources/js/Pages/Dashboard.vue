<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});

// Formulaire de création
const form = useForm({
    title: '',
    destination_url: '',
});

const submit = () => {
    form.post(route('links.store'), {
        onSuccess: () => {
            form.reset('title', 'destination_url');
        },
    });
};

// ---- UI : filtres ----
const filters = ref({
    status: 'all', // all | active | inactive
});

const filteredLinks = computed(() => {
    return props.links.filter((link) => {
        if (filters.value.status === 'active') {
            return link.is_active;
        }
        if (filters.value.status === 'inactive') {
            return !link.is_active;
        }
        return true;
    });
});

// ---- Edition de lien ----
const editingId = ref(null);

const editForm = useForm({
    id: null,
    title: '',
    destination_url: '',
    is_active: true,
});

const startEdit = (link) => {
    editingId.value = link.id;
    editForm.id = link.id;
    editForm.title = link.title;
    editForm.destination_url = link.destination_url;
    editForm.is_active = link.is_active;
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const submitEdit = () => {
    if (!editForm.id) return;

    editForm.put(route('links.update', editForm.id), {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
};

// ---- Suppression ----
const deleteForm = useForm({});

const deleteLink = (link) => {
    if (!confirm(`Supprimer le lien "${link.title}" ?`)) {
        return;
    }

    deleteForm.delete(route('links.destroy', link.id), {
        preserveScroll: true,
    });
};

// ---- Génération de l’URL trackée ----
const trackingUrl = (trackedLink) => {
    if (!trackedLink) return '';

    return route('links.redirect', { tracking_key: trackedLink.tracking_key });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Création de lien -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">
                            Créer un lien tracké
                        </h3>

                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Titre
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                           dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                           focus:border-indigo-500 sm:text-sm"
                                    placeholder="Lien setup vidéo TikTok"
                                />
                                <div v-if="form.errors.title" class="text-sm text-red-500 mt-1">
                                    {{ form.errors.title }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    URL de destination
                                </label>
                                <input
                                    v-model="form.destination_url"
                                    type="url"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                           dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500
                                           focus:border-indigo-500 sm:text-sm"
                                    placeholder="https://www.amazon.fr/..."
                                />
                                <div v-if="form.errors.destination_url" class="text-sm text-red-500 mt-1">
                                    {{ form.errors.destination_url }}
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent
                                           rounded-md font-semibold text-xs text-white uppercase tracking-widest
                                           hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-700
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                           dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                                    :disabled="form.processing"
                                >
                                    Créer le lien
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des liens -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold">
                                Vos liens
                            </h3>

                            <!-- Filtres -->
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Filtrer :</span>
                                <select
                                    v-model="filters.status"
                                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900
                                           dark:text-gray-100 text-sm"
                                >
                                    <option value="all">Tous</option>
                                    <option value="active">Actifs</option>
                                    <option value="inactive">Inactifs</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="!filteredLinks.length" class="text-sm text-gray-500 dark:text-gray-400">
                            Aucun lien pour le moment. Créez-en un au-dessus 👆
                        </div>

                        <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                            <li
                                v-for="link in filteredLinks"
                                :key="link.id"
                                class="py-4 space-y-2"
                            >
                                <!-- Si on édite cette ligne -->
                                <div v-if="editingId === link.id" class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs uppercase text-gray-500 dark:text-gray-400">
                                            Édition du lien
                                        </span>
                                    </div>

                                    <div class="space-y-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                                                Titre
                                            </label>
                                            <input
                                                v-model="editForm.title"
                                                type="text"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                                       dark:bg-gray-900 dark:text-gray-100 text-sm"
                                            />
                                            <div v-if="editForm.errors.title" class="text-xs text-red-500 mt-1">
                                                {{ editForm.errors.title }}
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">
                                                URL de destination
                                            </label>
                                            <input
                                                v-model="editForm.destination_url"
                                                type="url"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700
                                                       dark:bg-gray-900 dark:text-gray-100 text-sm"
                                            />
                                            <div v-if="editForm.errors.destination_url" class="text-xs text-red-500 mt-1">
                                                {{ editForm.errors.destination_url }}
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 mt-1">
                                            <input
                                                id="is_active"
                                                v-model="editForm.is_active"
                                                type="checkbox"
                                                class="rounded border-gray-300 dark:border-gray-700
                                                       text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            />
                                            <label for="is_active" class="text-xs text-gray-500 dark:text-gray-400">
                                                Lien actif
                                            </label>
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-2 mt-3">
                                        <button
                                            type="button"
                                            class="px-3 py-1 text-xs rounded-md border border-gray-500 text-gray-500
                                                   hover:bg-gray-100 dark:hover:bg-gray-900"
                                            @click="cancelEdit"
                                        >
                                            Annuler
                                        </button>
                                        <button
                                            type="button"
                                            class="px-3 py-1 text-xs rounded-md bg-indigo-600 text-white
                                                   hover:bg-indigo-500"
                                            :disabled="editForm.processing"
                                            @click="submitEdit"
                                        >
                                            Enregistrer
                                        </button>
                                    </div>
                                </div>

                                <!-- Affichage normal -->
                                <div v-else class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-medium flex items-center gap-2">
                                                {{ link.title }}
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px]
                                                           font-medium"
                                                    :class="link.is_active
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                        : 'bg-gray-200 text-gray-700 dark:bg-gray-900 dark:text-gray-300'"
                                                >
                                                    {{ link.is_active ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xl">
                                                {{ link.destination_url }}
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">
                                                {{ link.clicks_count ?? 0 }} clics
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <button
                                                type="button"
                                                class="px-2 py-1 text-xs rounded-md border border-gray-500
                                                       text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-900"
                                                @click="startEdit(link)"
                                            >
                                                Éditer
                                            </button>
                                            <button
                                                type="button"
                                                class="px-2 py-1 text-xs rounded-md border border-red-600
                                                       text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30"
                                                @click="deleteLink(link)"
                                            >
                                                Supprimer
                                            </button>
                                        </div>
                                    </div>

                                    <div
                                        v-if="link.tracked_links && link.tracked_links.length"
                                        class="mt-2"
                                    >
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            Lien tracké
                                        </label>
                                        <input
                                            type="text"
                                            :value="trackingUrl(link.tracked_links[0])"
                                            readonly
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-700
                                                   dark:bg-gray-900 dark:text-gray-100 text-xs"
                                            @focus="$event.target.select()"
                                        />
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
