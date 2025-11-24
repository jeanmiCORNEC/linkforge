<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    referralCode: String,
    referralsCount: Number,
    referralLink: String,
});

const copied = ref(false);

const generate = () => {
    router.post(route('affiliation.generate'));
};

const copyLink = () => {
    navigator.clipboard.writeText(props.referralLink);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
};
</script>

<template>
    <Head title="Affiliation" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Affiliation & Parrainage</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Hero Section -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 md:p-10 text-center">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        Invitez des amis, gagnez LinkForge Pro
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 max-w-2xl mx-auto mb-8">
                        Pour chaque ami qui s'inscrit et paie son premier mois, vous recevez <strong>30 jours de LinkForge Pro offerts</strong>.
                        <br>Si vous êtes déjà abonné, nous créditons 9,90€ sur votre compte pour payer votre prochaine facture.
                    </p>

                    <div v-if="!referralCode" class="mt-6">
                        <button @click="generate" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Générer mon lien unique
                        </button>
                    </div>

                    <div v-else class="mt-6 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl inline-block border border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider font-bold">Votre lien de parrainage</p>
                        <div class="flex items-center gap-2">
                            <code class="bg-white dark:bg-gray-800 px-4 py-2 rounded border border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 font-mono text-lg">
                                {{ referralLink }}
                            </code>
                            <button @click="copyLink" class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition" title="Copier">
                                <span v-if="copied" class="text-green-500 font-bold text-sm">Copié !</span>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-500">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider">Filleuls convertis</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ referralsCount }}</div>
                        <div class="mt-1 text-xs text-gray-500">Ayant pris un abonnement</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider">Mois offerts</div>
                        <div class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ referralsCount }}</div>
                        <div class="mt-1 text-xs text-gray-500">Cumulés grâce à vos amis</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider">Économies</div>
                        <div class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ (referralsCount * 9.90).toFixed(2) }} €</div>
                        <div class="mt-1 text-xs text-gray-500">Valeur totale offerte</div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
