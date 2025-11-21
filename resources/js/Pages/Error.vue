<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    status: Number,
});

const title = computed(() => {
    return {
        503: '503: Service Indisponible',
        500: '500: Erreur Serveur',
        404: '404: Page Non Trouvée',
        403: '403: Accès Interdit',
    }[props.status];
});

const description = computed(() => {
    return {
        503: 'Désolé, nous effectuons une maintenance. Veuillez réessayer plus tard.',
        500: 'Oups, quelque chose s\'est mal passé sur nos serveurs.',
        404: 'Désolé, la page que vous cherchez est introuvable.',
        403: 'Désolé, vous n\'êtes pas autorisé à accéder à cette page.',
    }[props.status];
});
</script>

<template>
    <Head :title="title" />

    <GuestLayout>
        <div class="text-center py-12">
            <h1 class="text-6xl font-bold text-indigo-600 dark:text-indigo-400 mb-4">{{ status }}</h1>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-white mb-4">{{ title }}</h2>
            <p class="text-slate-600 dark:text-slate-400 mb-8">{{ description }}</p>
            
            <Link
                :href="route('welcome')"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                Retour à l'accueil
            </Link>
        </div>
    </GuestLayout>
</template>
