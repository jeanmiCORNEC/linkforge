<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    title: '',
    destination_url: '',
    // plus tard on pourra ajouter source_id
});

const submit = () => {
    form.post(route('links.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const trackingUrl = (trackedLink) => {
    if (!trackedLink) return '';

    // Utilise Ziggy pour générer l’URL complète
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
                <!-- Bloc création de lien -->
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

                <!-- Bloc liste des liens -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">
                            Vos derniers liens
                        </h3>

                        <div v-if="!links.length" class="text-sm text-gray-500 dark:text-gray-400">
                            Aucun lien pour le moment. Créez-en un au-dessus 👆
                        </div>

                        <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                            <li
                                v-for="link in links"
                                :key="link.id"
                                class="py-4 space-y-2"
                            >
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-medium">
                                            {{ link.title }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xl">
                                            {{ link.destination_url }}
                                        </div>
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
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
