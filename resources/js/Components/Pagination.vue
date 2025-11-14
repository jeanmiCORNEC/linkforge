<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    // Tableau `links` de la réponse paginator Laravel
    links: {
        type: Array,
        required: true,
    },
    preserveState: {
        type: Boolean,
        default: true,
    },
    preserveScroll: {
        type: Boolean,
        default: true,
    },
});

// Label FR propre
const label = (link) => {
    if (link.label.includes('&laquo;')) {
        return '« Précédent';
    }
    if (link.label.includes('&raquo;')) {
        return 'Suivant »';
    }

    return link.label;
};

const goTo = (link) => {
    if (!link.url) return;

    router.get(
        link.url,
        {},
        {
            preserveState: props.preserveState,
            preserveScroll: props.preserveScroll,
        },
    );
};
</script>

<template>
    <div
        v-if="links && links.length > 1"
        class="mt-6 flex justify-center gap-2 text-xs"
    >
        <button
            v-for="link in links"
            :key="link.label + String(link.active)"
            type="button"
            class="px-3 py-1 rounded-md border text-xs transition"
            :class="[
                link.active
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800',
                !link.url ? 'opacity-40 cursor-default' : 'cursor-pointer',
            ]"
            :disabled="!link.url"
            @click="goTo(link)"
        >
            {{ label(link) }}
        </button>
    </div>
</template>
