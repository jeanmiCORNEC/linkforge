<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Footer from '@/Components/Footer.vue';

const billingInterval = ref('monthly');

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    auth: {
        type: Object,
        default: () => ({ user: null }),
    },
    plans: {
        type: Array,
        default: () => [],
    },
    appUrl: {
        type: String,
        default: '',
    },
});

const pricingCards = computed(() => {
    const user = props.auth.user;

    if (props.plans.length) {
        return props.plans.map((plan) => {
            const isPro = plan.id === 'pro';
            const isFree = plan.id === 'free';
            
            let ctaLabel = plan.cta_label ?? 'Choisir ce plan';
            let ctaUrl = plan.cta_url ?? '#pricing';
            let disabled = false;

            if (isPro) {
                if (user) {
                    if (user.plan === 'pro') {
                        ctaLabel = 'Plan Actuel';
                        ctaUrl = '#';
                        disabled = true;
                    } else {
                        ctaLabel = 'Passer en Pro';
                        ctaUrl = route('subscription.checkout', { interval: billingInterval.value });
                    }
                } else if (isFree) {
                    ctaLabel = 'Aller au Dashboard';
                    ctaUrl = route('dashboard');
                }
            } else {
                // Guest override
                if (isPro && props.canRegister) {
                     ctaUrl = route('register');
                }
            }

            // Override price for Pro plan based on interval
            let finalPrice = plan.price;
            let finalPriceLabel = plan.price_label;
            let finalFeatures = plan.features ?? [];

            if (isPro) {
                finalPrice = billingInterval.value === 'yearly' ? 99 : 9.9;
                finalPriceLabel = billingInterval.value === 'yearly' ? '99€ / an' : '9,90€ / mois';
                if (billingInterval.value === 'yearly') {
                    finalFeatures = ['Support prioritaire', '2 mois offerts'];
                }
            }

            return {
                id: plan.id,
                name: plan.name,
                price: finalPrice,
                priceLabel: finalPriceLabel,
                description: plan.description,
                featured: plan.featured ?? false,
                limits: plan.limits ?? [],
                features: finalFeatures,
                ctaLabel: ctaLabel,
                ctaUrl: ctaUrl,
                isFree: plan.price === 0,
                disabled: disabled,
            };
        });
    }

    return [
        {
            id: 'free',
            name: 'Discovery',
            price: 0,
            priceLabel: '0€ / mois',
            description: '10 liens, 2 campagnes, stats basiques sur 7 jours.',
            featured: false,
            limits: ['10 liens trackés', '2 campagnes / 4 sources', 'Statistiques 7 jours'],
            features: ['Heatmap et exports désactivés'],
            ctaLabel: user ? 'Aller au Dashboard' : 'Créer un compte',
            ctaUrl: user ? route('dashboard') : (props.canRegister ? route('register') : route('login')),
            isFree: true,
        },
        {
            id: 'pro',
            name: 'Creator',
            price: billingInterval.value === 'yearly' ? 99 : 9.9,
            priceLabel: billingInterval.value === 'yearly' ? '99€ / an' : '9,90€ / mois',
            description: 'Full options : heatmap, tops, exports CSV/raw.',
            featured: true,
            limits: [
                'Liens, campagnes et sources illimités',
                'Deltas, tops, heatmap horaire',
                'Exports CSV + raw',
            ],
            features: ['Support prioritaire', ...(billingInterval.value === 'yearly' ? ['2 mois offerts'] : [])],
            ctaLabel: user ? (user.plan === 'pro' ? 'Plan Actuel' : 'Passer en Pro') : 'Passer en Pro',
            ctaUrl: user 
                ? (user.plan === 'pro' ? '#' : route('subscription.checkout', { interval: billingInterval.value })) 
                : (props.canRegister ? route('register') : route('login')),
            isFree: false,
            disabled: user && user.plan === 'pro',
        },
    ];
});

const canonicalUrl = computed(() => {
    if (props.appUrl) {
        return props.appUrl;
    }
    if (typeof window !== 'undefined') {
        return window.location.origin;
    }
    return '';
});

const ogImageUrl = computed(() => {
    if (canonicalUrl.value) {
        return `${canonicalUrl.value}/images/og-linkforge.png`;
    }
    return '/images/og-linkforge.png';
});
</script>

<template>
    <Head>
        <title>LinkForge - Raccourcisseur de liens et analytics pour créateurs</title>
        <meta name="description" content="Créez des liens courts, organisez vos campagnes et analysez votre audience (géoloc, device, source). Essai gratuit, pas de CB." />
        <meta property="og:title" content="LinkForge - Raccourcisseur de liens et analytics" />
        <meta property="og:description" content="Ne devinez plus d'où vient votre trafic. Suivez les clics, devices et pays en un tableau de bord." />
        <meta property="og:image" :content="ogImageUrl" />
        <meta name="twitter:image" :content="ogImageUrl" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="LinkForge - Raccourcisseur de liens et analytics" />
        <meta name="twitter:description" content="Créez des liens courts, organisez vos campagnes et analysez votre audience (géoloc, device, source)." />
        <link rel="canonical" :href="canonicalUrl" />
    </Head>

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col transition-colors duration-300">
        <!-- Top bar -->
        <header class="border-b border-slate-200 dark:border-slate-800/60 bg-white/80 dark:bg-slate-950/95 backdrop-blur sticky top-0 z-50">
            <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <ApplicationLogo class="w-9 h-9 text-indigo-600 dark:text-indigo-400" />
                    <span class="font-semibold text-lg tracking-tight">LinkForge</span>
                </div>

                <nav class="hidden md:flex items-center gap-4 text-sm font-medium">
                    <a href="#hero" class="hover:text-indigo-600 dark:hover:text-indigo-300 transition">Accueil</a>
                    <a href="#proof" class="hover:text-indigo-600 dark:hover:text-indigo-300 transition">Compatibilité</a>
                    <a href="#problem" class="hover:text-indigo-600 dark:hover:text-indigo-300 transition">Problème</a>
                    <a href="#features" class="hover:text-indigo-600 dark:hover:text-indigo-300 transition">Solution</a>
                    <a href="#pricing" class="hover:text-indigo-600 dark:hover:text-indigo-300 transition">Tarifs</a>

                    <div class="h-6 w-px bg-slate-300 dark:bg-slate-700 mx-2" />

                    <Link v-if="auth.user" :href="route('dashboard')"
                        class="px-4 py-2 rounded-full bg-indigo-600 dark:bg-indigo-500 text-white font-semibold hover:bg-indigo-500 dark:hover:bg-indigo-400 shadow-lg shadow-indigo-500/30 dark:shadow-indigo-900/30 transition">
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link v-if="canLogin" :href="route('login')" class="text-sm text-slate-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-white transition">
                            Connexion
                        </Link>
                        <Link v-if="canRegister" :href="route('register')"
                            class="px-4 py-2 rounded-full bg-indigo-600 dark:bg-indigo-500 text-white font-semibold hover:bg-indigo-500 dark:hover:bg-indigo-400 shadow-lg shadow-indigo-500/30 dark:shadow-indigo-900/30 transition">
                            Commencer gratuitement
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <!-- HERO -->
            <section id="hero" class="border-b border-slate-200 dark:border-slate-800 bg-gradient-to-b from-white via-slate-50 to-slate-100 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900">
                <div class="max-w-6xl mx-auto px-4 py-14 grid md:grid-cols-2 gap-12 items-center">
                    <div class="space-y-5">
                        <p class="inline-flex items-center rounded-full border border-indigo-500/40 bg-indigo-500/10 px-3 py-1 text-[11px] font-semibold text-indigo-600 dark:text-indigo-200 uppercase tracking-[0.15em]">
                            Ne devinez plus d'où vient votre trafic
                        </p>
                        <h1 class="text-3xl md:text-4xl font-bold tracking-tight leading-tight text-slate-900 dark:text-white">
                            Le gestionnaire de liens intelligent pour créateurs et solopreneurs.
                        </h1>
                        <p class="text-sm md:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                            Créez des liens courts, organisez vos campagnes et découvrez exactement ce qui convertit. Devices, pays, sources : tout est dans un dashboard clair.
                        </p>
                        <div class="flex flex-wrap items-center gap-3">
                            <Link v-if="canRegister" :href="route('register')"
                                class="inline-flex items-center rounded-md bg-indigo-600 dark:bg-indigo-500 px-5 py-2.5 text-sm font-medium text-white shadow-lg shadow-indigo-500/30 dark:shadow-indigo-900/40 hover:bg-indigo-500 dark:hover:bg-indigo-400 transition">
                                Commencer gratuitement
                            </Link>
                            <a href="#pricing"
                                class="inline-flex items-center rounded-md border border-slate-300 dark:border-slate-700 px-5 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-white hover:border-indigo-400 transition bg-white dark:bg-transparent">
                                Voir les plans
                            </a>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pas de CB requise • Paramètres UTM conservés</p>
                        </div>
                    </div>

                    <div class="hidden md:block">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/70 dark:bg-slate-900/70 p-5 shadow-xl shadow-indigo-500/10 dark:shadow-indigo-900/30 space-y-3 backdrop-blur-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Dashboard</p>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Exemple campagne TikTok</p>
                                </div>
                                <span class="text-[10px] rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-300 px-2 py-0.5 border border-emerald-500/40">
                                    Temps réel
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 p-3">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Clics (7 j)</p>
                                    <p class="text-2xl font-bold text-slate-900 dark:text-white">1 284</p>
                                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1">+32% vs période précédente</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 p-3">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Mobile</p>
                                    <p class="text-xl font-semibold text-slate-900 dark:text-white">72%</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">iOS 48% / Android 24%</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 p-3">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Top pays</p>
                                    <p class="text-lg font-semibold text-slate-900 dark:text-white">FR • CA • BE</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Heatmap horaire incluse</p>
                                </div>
                            </div>
                            <div class="rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 p-3">
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Top sources</p>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-slate-700 dark:text-slate-300">Bio TikTok – Setup 2025</span>
                                        <span class="text-slate-900 dark:text-slate-200 font-medium">432 clics</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-700 dark:text-slate-300">YouTube – Unboxing micro</span>
                                        <span class="text-slate-900 dark:text-slate-200 font-medium">311 clics</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-700 dark:text-slate-300">Story Insta – Code -10%</span>
                                        <span class="text-slate-900 dark:text-slate-200 font-medium">187 clics</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SOCIAL PROOF -->
            <section id="proof" class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">
                <div class="max-w-6xl mx-auto px-4 py-8">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500 text-center mb-4">Compatible avec vos plateformes</p>
                    <div class="flex flex-wrap items-center justify-center gap-6 text-sm font-medium">
                        <span class="text-pink-500 font-semibold">TikTok</span>
                        <span class="text-fuchsia-500 font-semibold">Instagram</span>
                        <span class="text-red-600 font-semibold">YouTube</span>
                        <span class="text-blue-600 font-semibold">LinkedIn</span>
                        <span class="text-blue-500 font-semibold">Facebook</span>
                        <span class="text-slate-900 dark:text-white font-semibold">X (Twitter)</span>
                        <span class="text-indigo-500 font-semibold">Vinted</span>
                        <span class="text-orange-500 font-semibold">Leboncoin</span>
                    </div>
                </div>
            </section>

            <!-- PROBLÈME -->
            <section id="problem" class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/90">
                <div class="max-w-6xl mx-auto px-4 py-12 space-y-6">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Vous publiez du contenu, mais vous avancez à l'aveugle ?</h2>
                    <div class="grid md:grid-cols-3 gap-4 text-sm text-slate-600 dark:text-slate-300">
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/60 p-4 shadow-sm">
                            <p class="font-semibold text-slate-900 dark:text-white mb-2">Des liens partout</p>
                            <p>Bio, descriptions, newsletters... impossible de s’y retrouver.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/60 p-4 shadow-sm">
                            <p class="font-semibold text-slate-900 dark:text-white mb-2">Zéro visibilité</p>
                            <p>Les réseaux donnent un total de clics, mais pas le device ni le pays.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/60 p-4 shadow-sm">
                            <p class="font-semibold text-slate-900 dark:text-white mb-2">Perte de revenus</p>
                            <p>Sans savoir quelles campagnes marchent, vous gaspillez du temps sur les mauvais canaux.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SOLUTION / FEATURES -->
            <section id="features" class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">
                <div class="max-w-6xl mx-auto px-4 py-12 space-y-8">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">LinkForge est le remède</h2>
                    <div class="grid md:grid-cols-3 gap-6 text-sm text-slate-600 dark:text-slate-300">
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 p-5 space-y-2">
                            <p class="text-xs uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-300">Tracking chirurgical</p>
                            <p class="text-lg font-semibold text-slate-900 dark:text-white">Connaissez tout de vos visiteurs</p>
                            <p>Pays, ville, appareil (mobile/desktop/tablette), navigateur. La data que les réseaux cachent.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 p-5 space-y-2">
                            <p class="text-xs uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-300">Campagnes & Sources</p>
                            <p class="text-lg font-semibold text-slate-900 dark:text-white">Organisez, ne subissez plus</p>
                            <p>Groupez par campagne (Black Friday, Lancement ebook) et par source (Bio TikTok, Story). Comparez en un coup d’œil.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 p-5 space-y-2">
                            <p class="text-xs uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-300">Redirection intelligente</p>
                            <p class="text-lg font-semibold text-slate-900 dark:text-white">Ne perdez jamais un clic</p>
                            <p>Propagation automatique des paramètres (UTM, affiliation) et redirection ultra-rapide.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- USE CASES -->
            <section id="for-who" class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/90">
                <div class="max-w-6xl mx-auto px-4 py-12 grid md:grid-cols-3 gap-6 text-sm text-slate-600 dark:text-slate-300">
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/60 p-4 space-y-2 shadow-sm">
                        <p class="text-xs uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-300">Infopreneur</p>
                        <p class="text-slate-900 dark:text-white font-semibold">Comparer YouTube vs Instagram</p>
                        <p>Trackez les clics sur vos ebooks et identifiez les vidéos qui convertissent.</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/60 p-4 space-y-2 shadow-sm">
                        <p class="text-xs uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-300">Affilié</p>
                        <p class="text-slate-900 dark:text-white font-semibold">Nettoyez vos liens</p>
                        <p>Masquez les URL moches, sécurisez vos paramètres et suivez chaque clic par source.</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/60 p-4 space-y-2 shadow-sm">
                        <p class="text-xs uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-300">Marketer</p>
                        <p class="text-slate-900 dark:text-white font-semibold">Prouvez le ROI</p>
                        <p>Rapports clairs par campagne/source, exports CSV pour vos clients.</p>
                    </div>
                </div>
            </section>

            <!-- PRICING -->
            <section id="pricing" class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">
                <div class="max-w-6xl mx-auto px-4 py-12 space-y-6">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-300">Pricing simple</p>
                            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Une offre gratuite, une offre Pro : c'est tout.</h2>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Essai gratuit. Pas de carte.</p>
                    </div>

                    <!-- Toggle Mensuel / Annuel -->
                    <div class="flex justify-center">
                        <div class="relative flex items-center rounded-full bg-slate-100 dark:bg-slate-800 p-1">
                            <button
                                @click="billingInterval = 'monthly'"
                                :class="[
                                    'relative z-10 rounded-full px-4 py-1.5 text-sm font-medium transition',
                                    billingInterval === 'monthly'
                                        ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                                ]"
                            >
                                Mensuel
                            </button>
                            <button
                                @click="billingInterval = 'yearly'"
                                :class="[
                                    'relative z-10 rounded-full px-4 py-1.5 text-sm font-medium transition',
                                    billingInterval === 'yearly'
                                        ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                                ]"
                            >
                                Annuel <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold ml-1">-15%</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div
                            v-for="plan in pricingCards"
                            :key="plan.id"
                            :class="[
                                'rounded-2xl border p-6 space-y-3 bg-slate-50 dark:bg-slate-950/70',
                                plan.featured ? 'border-indigo-500 shadow-xl shadow-indigo-500/20 dark:shadow-indigo-900/30' : 'border-slate-200 dark:border-slate-800'
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ plan.name }}</p>
                                    <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ plan.priceLabel }}</p>
                                </div>
                                <span v-if="plan.featured" class="text-[11px] rounded-full bg-indigo-100 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-200 px-3 py-1 border border-indigo-200 dark:border-indigo-500/40">
                                    Le plus populaire
                                </span>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ plan.description }}</p>

                            <ul class="space-y-1 text-sm text-slate-600 dark:text-slate-200">
                                <li v-for="item in plan.limits" :key="item" class="flex gap-2">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-indigo-400"></span>
                                    <span>{{ item }}</span>
                                </li>
                                <li v-for="feat in plan.features" :key="feat" class="flex gap-2">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400"></span>
                                    <span>{{ feat }}</span>
                                </li>
                            </ul>

                            <div class="pt-2">
                                <a :href="plan.ctaUrl"
                                    class="inline-flex items-center rounded-md px-4 py-2 text-sm font-semibold shadow transition"
                                    :class="[
                                        plan.featured ? 'bg-indigo-600 dark:bg-indigo-500 text-white hover:bg-indigo-500 dark:hover:bg-indigo-400 shadow-indigo-500/30 dark:shadow-indigo-900/30' : 'border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-100 hover:border-indigo-400 bg-white dark:bg-transparent',
                                        plan.disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''
                                    ]">
                                    {{ plan.ctaLabel }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section id="faq" class="bg-slate-50 dark:bg-slate-950">
                <div class="max-w-6xl mx-auto px-4 py-12 space-y-6">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">FAQ</h2>
                    <div class="grid md:grid-cols-2 gap-4 text-sm text-slate-600 dark:text-slate-200">
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/70 p-4 space-y-2 shadow-sm">
                            <p class="font-semibold text-slate-900 dark:text-white">LinkForge impacte-t-il le SEO ?</p>
                            <p>Non, redirections propres (301/302), pas de contenu dupliqué.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/70 p-4 space-y-2 shadow-sm">
                            <p class="font-semibold text-slate-900 dark:text-white">Puis-je modifier un lien après partage ?</p>
                            <p>Oui, vous pouvez mettre à jour l’URL cible à tout moment sans changer le lien court.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/70 p-4 space-y-2 shadow-sm">
                            <p class="font-semibold text-slate-900 dark:text-white">Compatible avec l’affiliation Amazon ?</p>
                            <p>Oui, nous gardons vos paramètres et UTM pour préserver le tracking Amazon.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/70 p-4 space-y-2 shadow-sm">
                            <p class="font-semibold text-slate-900 dark:text-white">Dois-je ajouter du code ?</p>
                            <p>Non, tout se passe via l’app : générez vos liens courts, copiez-collez, c’est tout.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        <Footer />
    </div>
</template>
