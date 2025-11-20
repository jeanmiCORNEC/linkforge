<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    plan: {
        type: String,
        default: 'free',
    },
});
</script>

<template>
    <Head title="Profil" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl md:text-2xl font-semibold leading-tight text-slate-100"
            >
                Profil
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-slate-950 border border-slate-800 shadow-xl shadow-indigo-900/30 p-4 sm:p-6 rounded-2xl flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">
                            Plan actuel
                        </p>
                        <p class="text-lg font-semibold text-white">
                            {{ props.plan === 'free' ? 'Free' : 'Pro' }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">
                            Accédez aux heatmaps, tops et exports illimités avec le plan Pro.
                        </p>
                    </div>
                    <div v-if="props.plan === 'free'" class="flex items-center">
                        <a
                            href="/#pricing"
                            class="rounded-md bg-indigo-500 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-indigo-900/30 hover:bg-indigo-400 transition"
                        >
                            Passer en Pro
                        </a>
                    </div>
                </div>

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

                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
