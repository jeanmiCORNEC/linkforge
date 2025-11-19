<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    conversions: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    availableStatuses: {
        type: Array,
        default: () => [],
    },
    canAccess: {
        type: Boolean,
        default: false,
    },
});

const selectedStatus = ref(props.filters.status ?? '');
const selectedDays = ref(props.filters.days ?? 30);
const updatingId = ref(null);

const statusLabels = {
    pending: 'En attente',
    approved: 'Approuvée',
    rejected: 'Rejetée',
    void: 'Annulée',
};

const statusClasses = {
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-emerald-100 text-emerald-800',
    rejected: 'bg-rose-100 text-rose-800',
    void: 'bg-slate-100 text-slate-700',
};

const applyFilters = () => {
    router.get('/conversions', {
        status: selectedStatus.value || undefined,
        days: selectedDays.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const updateStatus = (id, status) => {
    updatingId.value = id;
    router.patch(`/conversions/${id}/status`, { status }, {
        preserveScroll: true,
        onFinish: () => {
            updatingId.value = null;
        },
    });
};

const conversionRows = computed(() => props.conversions ?? []);
const hasConversions = computed(() => (conversionRows.value.length ?? 0) > 0);
</script>

<template>
    <Head title="Conversions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Conversions
                </h2>
                <Link
                    href="/profile#integrations"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-500"
                >
                    Connecter vos plateformes →
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white p-6 shadow">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-lg font-semibold text-gray-900">
                                Suivi des conversions
                            </p>
                            <p class="text-sm text-gray-500">
                                Consulte les ventes remontées par tes plateformes partenaires.
                            </p>
                        </div>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-600">
                    {{ canAccess ? 'Actif' : 'Verrouillé' }}
                </span>
            </div>

            <div
                v-if="!canAccess"
                class="mt-6 rounded-xl border-2 border-dashed border-gray-200 p-5 text-sm text-gray-600"
            >
                <p>
                    Cette section est réservée aux plans <strong>Pro</strong> et <strong>Scale</strong>.
                    Connecte tes comptes affiliés pour récupérer automatiquement les ventes.
                </p>
                <Link
                    href="/profile#integrations"
                    class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-indigo-500"
                >
                    Gérer les connexions
                </Link>
            </div>

            <div v-else class="mt-6 space-y-6">
                        <form
                            class="grid gap-4 md:grid-cols-3"
                            @submit.prevent="applyFilters"
                        >
                            <label class="flex flex-col text-sm font-medium text-gray-700">
                                Statut
                                <select
                                    v-model="selectedStatus"
                                    class="mt-1 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Tous</option>
                                    <option
                                        v-for="status in availableStatuses"
                                        :key="status"
                                        :value="status"
                                    >
                                        {{ statusLabels[status] ?? status }}
                                    </option>
                                </select>
                            </label>

                            <label class="flex flex-col text-sm font-medium text-gray-700">
                                Fenêtre (jours)
                                <select
                                    v-model.number="selectedDays"
                                    class="mt-1 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option :value="7">7 jours</option>
                                    <option :value="30">30 jours</option>
                                    <option :value="60">60 jours</option>
                                    <option :value="90">90 jours</option>
                                </select>
                            </label>

                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700"
                                >
                                    Actualiser
                                </button>
                            </div>
                        </form>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3">Order ID</th>
                                        <th class="px-4 py-3">Lien / Source</th>
                                        <th class="px-4 py-3">Statut</th>
                                        <th class="px-4 py-3 text-right">Revenu</th>
                                        <th class="px-4 py-3 text-right">Commission</th>
                                        <th class="px-4 py-3">Date</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-if="!hasConversions">
                                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                            Aucune conversion sur cette période. Branche ton webhook ou élargis le filtre.
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="conversion in conversionRows"
                                        :key="conversion.id"
                                        class="align-middle"
                                    >
                                        <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                            {{ conversion.order_id ?? `#${conversion.id}` }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="font-semibold text-gray-900">
                                                {{ conversion.link_title ?? conversion.tracking_key }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ conversion.source_name ?? 'Sans source' }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                :class="[
                                                    'rounded-full px-2.5 py-1 text-xs font-semibold',
                                                    statusClasses[conversion.status] ?? 'bg-gray-100 text-gray-700',
                                                ]"
                                            >
                                                {{ statusLabels[conversion.status] ?? conversion.status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                            {{ conversion.revenue.toFixed(2) }} {{ conversion.currency }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-600">
                                            {{ conversion.commission.toFixed(2) }} {{ conversion.currency }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ conversion.recorded_at }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button
                                                    v-if="conversion.status !== 'approved'"
                                                    type="button"
                                                    class="rounded-md border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50"
                                                    :disabled="updatingId === conversion.id"
                                                    @click="updateStatus(conversion.id, 'approved')"
                                                >
                                                    Approuver
                                                </button>
                                                <button
                                                    v-if="conversion.status !== 'rejected'"
                                                    type="button"
                                                    class="rounded-md border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-50"
                                                    :disabled="updatingId === conversion.id"
                                                    @click="updateStatus(conversion.id, 'rejected')"
                                                >
                                                    Rejeter
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
