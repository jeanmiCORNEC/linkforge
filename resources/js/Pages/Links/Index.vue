<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link as InertiaLink } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps({
    links: {
        type: Object,
        required: true,
    },
});

const hasLinks = computed(() => props.links.data.length > 0);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Vos liens" />

        <div class="py-8 max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold text-gray-100">
                    Vos liens trackés
                </h1>

                <!-- Bouton vers le dashboard pour créer un lien -->
                <InertiaLink
                    :href="route('dashboard')"
                    class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium bg-indigo-600 hover:bg-indigo-500 text-white transition"
                >
                    + Créer un nouveau lien
                </InertiaLink>
            </div>

            <!-- Tableau -->
            <div class="bg-slate-900/70 border border-slate-800 rounded-xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-slate-800">
                    <thead class="bg-slate-900/80">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">
                                Titre
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">
                                Lien court
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">
                                URL de destination
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-400 uppercase tracking-wider">
                                Créé le
                            </th>
                        </tr>
                    </thead>

                    <tbody class="bg-slate-900 divide-y divide-slate-800">
                        <tr v-if="!hasLinks">
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-400">
                                Aucun lien pour le moment. Crée ton premier lien depuis le
                                <InertiaLink
                                    :href="route('dashboard')"
                                    class="text-indigo-400 hover:text-indigo-300 underline"
                                >
                                    dashboard
                                </InertiaLink>.
                            </td>
                        </tr>

                        <tr v-for="link in links.data" :key="link.id">
                            <td class="px-6 py-4 text-sm text-slate-100">
                                {{ link.title }}
                            </td>

                            <td class="px-6 py-4 text-sm">
                                <div v-if="link.tracking_key" class="flex items-center gap-2">
                                    <a
                                        :href="route('links.redirect', link.tracking_key)"
                                        class="text-indigo-400 hover:text-indigo-300 underline break-all"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        {{ route('links.redirect', link.tracking_key) }}
                                    </a>
                                </div>
                                <span v-else class="text-slate-500 text-xs italic">
                                    Aucun tracking key (sera géré plus tard)
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm">
                                <a
                                    :href="link.destination_url"
                                    class="text-slate-300 hover:text-slate-100 break-all"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {{ link.destination_url }}
                                </a>
                            </td>

                            <td class="px-6 py-4 text-sm text-right text-slate-400">
                                {{ link.created_at }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination simple (on pourra la styliser plus tard) -->
            <div
                v-if="links.links && links.links.length > 1"
                class="mt-4 flex justify-end gap-2 text-sm text-slate-300"
            >
                <InertiaLink
                    v-for="page in links.links"
                    :key="page.url || page.label"
                    :href="page.url || '#'"
                    v-html="page.label"
                    :class="[
                        'px-3 py-1 rounded-md border border-slate-700',
                        page.active
                            ? 'bg-indigo-600 text-white'
                            : 'bg-slate-900 hover:bg-slate-800',
                        !page.url ? 'opacity-50 cursor-default pointer-events-none' : '',
                    ]"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
