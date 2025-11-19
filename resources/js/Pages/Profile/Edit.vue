<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    plan: {
        type: String,
        default: 'free',
    },
    integrations: {
        type: Array,
        default: () => [],
    },
    platforms: {
        type: Array,
        default: () => [],
    },
    canManageIntegrations: {
        type: Boolean,
        default: false,
    },
});

const availablePlatforms = computed(() => props.platforms ?? []);

const integrationsForm = useForm({
    platform: availablePlatforms.value[0]?.id ?? '',
    label: '',
    credentials: {},
});

const selectedPlatform = computed(() => {
    return availablePlatforms.value.find((platform) => platform.id === integrationsForm.platform)
        ?? availablePlatforms.value[0]
        ?? null;
});

const platformFields = computed(() => selectedPlatform.value?.fields ?? []);

const resetCredentials = () => {
    const fields = platformFields.value;
    const credentials = {};
    fields.forEach((field) => {
        credentials[field.key] = '';
    });
    integrationsForm.credentials = credentials;
};

watch(
    () => integrationsForm.platform,
    () => resetCredentials(),
    { immediate: true },
);

const submitIntegration = () => {
    integrationsForm.post(route('integrations.affiliate.store'), {
        preserveScroll: true,
        onSuccess: () => {
            integrationsForm.reset('label');
            resetCredentials();
        },
    });
};

const deleteForm = useForm({});
const removeIntegration = (id) => {
    deleteForm.delete(route('integrations.affiliate.destroy', id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div id="integrations" class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Connecteurs affiliés
                            </h3>
                            <p class="text-sm text-gray-500">
                                Centralise tes identifiants (Impact, Awin, Hotmart…) pour que LinkForge récupère automatiquement les conversions.
                            </p>
                        </div>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Plan : {{ (props.plan ?? 'free').toUpperCase() }}
                        </span>
                    </div>

                    <div
                        v-if="!props.canManageIntegrations"
                        class="mt-6 rounded-lg border-2 border-dashed border-gray-200 p-5 text-sm text-gray-600"
                    >
                        <p>
                            Les connecteurs affiliés sont réservés aux plans <strong>Pro</strong> et <strong>Scale</strong>.
                            Passe sur une offre supérieure pour activer la collecte automatique.
                        </p>
                        <Link
                            href="/#pricing"
                            class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-indigo-500"
                        >
                            Découvrir les offres
                        </Link>
                    </div>

                    <div
                        v-else
                        class="mt-6 space-y-8"
                    >
                        <div>
                            <p class="text-sm font-semibold text-gray-700">
                                Connexions actives
                            </p>

                            <div
                                v-if="!props.integrations.length"
                                class="mt-3 rounded-lg border border-dashed border-gray-200 p-4 text-sm text-gray-500"
                            >
                                Aucune plateforme connectée pour l’instant. Ajoute ta première connexion ci-dessous.
                            </div>

                            <ul
                                v-else
                                class="mt-4 space-y-3"
                            >
                                <li
                                    v-for="integration in props.integrations"
                                    :key="integration.id"
                                    class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 px-4 py-3"
                                >
                                    <div>
                                        <p class="font-semibold text-gray-900">
                                            {{ integration.label }}
                                            <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs uppercase text-gray-600">
                                                {{ integration.platform_label }}
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Statut : {{ integration.statusLabel }}
                                            <template v-if="integration.last_synced_at">
                                                • Sync {{ integration.last_synced_at }}
                                            </template>
                                        </p>
                                        <p
                                            v-if="integration.last_error"
                                            class="text-xs text-rose-600"
                                        >
                                            Erreur : {{ integration.last_error }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        class="text-sm font-semibold text-rose-600 hover:text-rose-500"
                                        @click="removeIntegration(integration.id)"
                                    >
                                        Supprimer
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-gray-700">
                                Ajouter une connexion
                            </p>

                            <form
                                class="mt-4 space-y-4"
                                @submit.prevent="submitIntegration"
                            >
                                <div class="grid gap-4 md:grid-cols-2">
                                    <label class="text-sm font-medium text-gray-700">
                                        Plateforme
                                        <select
                                            v-model="integrationsForm.platform"
                                            class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option
                                                v-for="platform in availablePlatforms"
                                                :key="platform.id"
                                                :value="platform.id"
                                            >
                                                {{ platform.name }}
                                            </option>
                                        </select>
                                    </label>

                                    <label class="text-sm font-medium text-gray-700">
                                        Nom interne
                                        <input
                                            v-model="integrationsForm.label"
                                            type="text"
                                            class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Ex : Impact - Ebook 2025"
                                        >
                                    </label>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div
                                        v-for="field in platformFields"
                                        :key="field.key"
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        {{ field.label }}
                                        <input
                                            v-model="integrationsForm.credentials[field.key]"
                                            type="text"
                                            class="mt-1 w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :placeholder="field.label"
                                        >
                                    </div>
                                </div>

                                <div class="flex items-center justify-end">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700"
                                        :disabled="integrationsForm.processing"
                                    >
                                        Connecter la plateforme
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
