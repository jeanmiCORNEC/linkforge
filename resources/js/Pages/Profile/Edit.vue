<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import { useTheme } from '@/Composables/useTheme';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    plan: {
        type: String,
        default: 'free',
    },
});

const { theme, setTheme } = useTheme();
</script>

<template>
    <Head title="Profil" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl md:text-2xl font-semibold leading-tight text-slate-800 dark:text-slate-100"
            >
                Profil
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                <div v-if="$page.props.flash.error" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Erreur : </strong>
                    <span class="block sm:inline">{{ $page.props.flash.error }}</span>
                </div>
                <div v-if="$page.props.flash.success" class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Succès : </strong>
                    <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                </div>
                <div v-if="$page.props.flash.status" class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Info : </strong>
                    <span class="block sm:inline">{{ $page.props.flash.status }}</span>
                </div>
                <!-- Subscription Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm dark:shadow-black/20 p-4 sm:p-6 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                            Plan actuel
                        </p>
                        <p class="text-lg font-semibold text-slate-900 dark:text-white">
                            {{ props.plan === 'free' ? 'Free' : 'Pro' }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            {{ props.plan === 'free' ? 'Accédez aux heatmaps, tops et exports illimités avec le plan Pro.' : 'Merci de soutenir LinkForge !' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2" v-if="props.plan === 'free'">
                        <a
                            :href="route('subscription.checkout', { interval: 'monthly' })"
                            class="rounded-md bg-indigo-600 dark:bg-indigo-500 px-4 py-2 text-xs font-semibold text-white shadow-lg hover:bg-indigo-500 dark:hover:bg-indigo-400 transition"
                        >
                            Pro Mensuel (9,90€)
                        </a>
                        <a
                            :href="route('subscription.checkout', { interval: 'yearly' })"
                            class="rounded-md border border-indigo-600 dark:border-indigo-500 px-4 py-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition"
                        >
                            Pro Annuel (99€)
                        </a>
                    </div>
                    <div class="flex items-center gap-4" v-else>
                        <a
                            :href="route('subscription.portal')"
                            class="rounded-md border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-indigo-600 dark:hover:text-white transition"
                        >
                            Gérer mon abonnement
                        </a>
                    </div>
                </div>

                <!-- Theme Selector -->
                <div class="bg-white dark:bg-slate-900 p-4 shadow sm:rounded-lg sm:p-8 border border-slate-200 dark:border-slate-800">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">Apparence</h2>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Choisissez le thème de l'interface.
                            </p>
                        </header>

                        <div class="mt-6 flex items-center gap-4">
                            <button
                                @click="setTheme('light')"
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium border transition',
                                    theme === 'light'
                                        ? 'bg-indigo-50 dark:bg-indigo-500/10 border-indigo-500 text-indigo-700 dark:text-indigo-300'
                                        : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'
                                ]"
                            >
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                                    </svg>
                                    Clair
                                </div>
                            </button>

                            <button
                                @click="setTheme('dark')"
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium border transition',
                                    theme === 'dark'
                                        ? 'bg-indigo-50 dark:bg-indigo-500/10 border-indigo-500 text-indigo-700 dark:text-indigo-300'
                                        : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'
                                ]"
                            >
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                                    </svg>
                                    Sombre
                                </div>
                            </button>

                            <button
                                @click="setTheme('system')"
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium border transition',
                                    theme === 'system'
                                        ? 'bg-indigo-50 dark:bg-indigo-500/10 border-indigo-500 text-indigo-700 dark:text-indigo-300'
                                        : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'
                                ]"
                            >
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                    </svg>
                                    Système
                                </div>
                            </button>
                        </div>
                    </section>
                </div>

                <div
                    class="bg-white dark:bg-slate-900 p-4 shadow sm:rounded-lg sm:p-8 border border-slate-200 dark:border-slate-800"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="bg-white dark:bg-slate-900 p-4 shadow sm:rounded-lg sm:p-8 border border-slate-200 dark:border-slate-800"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div
                    class="bg-white dark:bg-slate-900 p-4 shadow sm:rounded-lg sm:p-8 border border-slate-200 dark:border-slate-800"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
