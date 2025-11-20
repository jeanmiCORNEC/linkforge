<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

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
    if (props.plans.length) {
        return props.plans.map((plan) => ({
            id: plan.id,
            name: plan.name,
            price: plan.price,
            priceLabel: plan.price_label,
            description: plan.description,
            featured: plan.featured ?? false,
            limits: plan.limits ?? [],
            features: plan.features ?? [],
            ctaLabel: plan.cta_label ?? 'Choisir ce plan',
            ctaUrl: plan.cta_url ?? '#pricing',
            isFree: plan.price === 0,
        }));
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
            ctaLabel: 'Créer un compte',
            ctaUrl: props.canRegister ? route('register') : route('login'),
            isFree: true,
        },
        {
            id: 'pro',
            name: 'Creator',
            price: 9.9,
            priceLabel: '9,90€ / mois',
            description: 'Full options : heatmap, tops, exports CSV/raw.',
            featured: true,
            limits: [
                'Liens, campagnes et sources illimités',
                'Deltas, tops, heatmap horaire',
                'Exports CSV + raw',
            ],
            features: ['Support prioritaire'],
            ctaLabel: 'Passer en Pro',
            ctaUrl: props.canRegister ? route('register', { plan: 'pro' }) : route('login'),
            isFree: false,
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

    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
        <!-- Top bar -->
        <header class="border-b border-slate-800/60 bg-slate-950/95 backdrop-blur">
            <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <ApplicationLogo class="w-9 h-9 text-indigo-400" />
                    <span class="font-semibold text-lg tracking-tight">LinkForge</span>
                </div>

                <nav class="hidden md:flex items-center gap-4 text-sm">
                    <a href="#hero" class="hover:text-indigo-300 transition">Accueil</a>
                    <a href="#proof" class="hover:text-indigo-300 transition">Compatibilité</a>
                    <a href="#problem" class="hover:text-indigo-300 transition">Problème</a>
                    <a href="#features" class="hover:text-indigo-300 transition">Solution</a>
                    <a href="#pricing" class="hover:text-indigo-300 transition">Tarifs</a>

                    <div class="h-6 w-px bg-slate-700 mx-2" />

                    <Link v-if="auth.user" :href="route('dashboard')"
                        class="px-4 py-2 rounded-full bg-indigo-500 text-white font-semibold hover:bg-indigo-400">
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link v-if="canLogin" :href="route('login')" class="text-sm text-gray-300 hover:text-white">
                            Connexion
                        </Link>
                        <Link v-if="canRegister" :href="route('register')"
                            class="px-4 py-2 rounded-full bg-indigo-500 text-white font-semibold hover:bg-indigo-400">
                            Commencer gratuitement
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <!-- HERO -->
            <section id="hero" class="border-b border-slate-800 bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900">
                <div class="max-w-6xl mx-auto px-4 py-14 grid md:grid-cols-2 gap-12 items-center">
                    <div class="space-y-5">
                        <p class="inline-flex items-center rounded-full border border-indigo-500/40 bg-indigo-500/10 px-3 py-1 text-[11px] font-semibold text-indigo-200 uppercase tracking-[0.15em]">
                            Ne devinez plus d'où vient votre trafic
                        </p>
                        <h1 class="text-3xl md:text-4xl font-bold tracking-tight leading-tight">
                            Le gestionnaire de liens intelligent pour créateurs et solopreneurs.
                        </h1>
                        <p class="text-sm md:text-base text-slate-300 leading-relaxed">
                            Créez des liens courts, organisez vos campagnes et découvrez exactement ce qui convertit. Devices, pays, sources : tout est dans un dashboard clair.
                        </p>
                        <div class="flex flex-wrap items-center gap-3">
                            <Link v-if="canRegister" :href="route('register')"
                                class="inline-flex items-center rounded-md bg-indigo-500 px-5 py-2.5 text-sm font-medium text-white shadow-lg shadow-indigo-900/40 hover:bg-indigo-400 transition">
                                Commencer gratuitement
                            </Link>
                            <a href="#pricing"
                                class="inline-flex items-center rounded-md border border-slate-700 px-5 py-2.5 text-sm font-medium text-slate-200 hover:text-white hover:border-indigo-400 transition">
                                Voir les plans
                            </a>
                            <p class="text-xs text-slate-400">Pas de CB requise • Paramètres UTM conservés</p>
                        </div>
                    </div>

                    <div class="hidden md:block">
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5 shadow-xl shadow-indigo-900/30 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-400">Dashboard</p>
                                    <p class="text-sm font-semibold">Exemple campagne TikTok</p>
                                </div>
                                <span class="text-[10px] rounded-full bg-emerald-500/10 text-emerald-300 px-2 py-0.5 border border-emerald-500/40">
                                    Temps réel
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="rounded-xl bg-slate-950/60 border border-slate-800 p-3">
                                    <p class="text-xs text-slate-400">Clics (7 j)</p>
                                    <p class="text-2xl font-bold">1 284</p>
                                    <p class="text-[11px] text-emerald-400 mt-1">+32% vs période précédente</p>
                                </div>
                                <div class="rounded-xl bg-slate-950/60 border border-slate-800 p-3">
                                    <p class="text-xs text-slate-400">Mobile</p>
                                    <p class="text-xl font-semibold">72%</p>
                                    <p class="text-[11px] text-slate-400 mt-1">iOS 48% / Android 24%</p>
                                </div>
                                <div class="rounded-xl bg-slate-950/60 border border-slate-800 p-3">
                                    <p class="text-xs text-slate-400">Top pays</p>
                                    <p class="text-lg font-semibold">FR • CA • BE</p>
                                    <p class="text-[11px] text-slate-400 mt-1">Heatmap horaire incluse</p>
                                </div>
                            </div>
                            <div class="rounded-xl bg-slate-950/60 border border-slate-800 p-3">
                                <p class="text-xs text-slate-400 mb-2">Top sources</p>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span>Bio TikTok – Setup 2025</span>
                                        <span class="text-slate-200">432 clics</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>YouTube – Unboxing micro</span>
                                        <span class="text-slate-200">311 clics</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Story Insta – Code -10%</span>
                                        <span class="text-slate-200">187 clics</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SOCIAL PROOF -->
            <section id="proof" class="border-b border-slate-800 bg-slate-950">
                <div class="max-w-6xl mx-auto px-4 py-8">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500 text-center mb-4">Compatible avec vos plateformes</p>
                    <div class="flex flex-wrap items-center justify-center gap-6 text-slate-400 text-sm">
                        <span>TikTok</span>
                        <span>Instagram</span>
                        <span>YouTube</span>
                        <span>LinkedIn</span>
                        <span>Facebook</span>
                        <span>X (Twitter)</span>
                    </div>
                </div>
            </section>

            <!-- PROBLÈME -->
            <section id="problem" class="border-b border-slate-800 bg-slate-950/90">
                <div class="max-w-6xl mx-auto px-4 py-12 space-y-6">
                    <h2 class="text-2xl font-bold">Vous publiez du contenu, mais vous avancez à l'aveugle ?</h2>
                    <div class="grid md:grid-cols-3 gap-4 text-sm text-slate-300">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                            <p class="font-semibold text-white mb-2">Des liens partout</p>
                            <p>Bio, descriptions, newsletters... impossible de s’y retrouver.</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                            <p class="font-semibold text-white mb-2">Zéro visibilité</p>
                            <p>Les réseaux donnent un total de clics, mais pas le device ni le pays.</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                            <p class="font-semibold text-white mb-2">Perte de revenus</p>
                            <p>Sans savoir quelles campagnes marchent, vous gaspillez du temps sur les mauvais canaux.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SOLUTION / FEATURES -->
            <section id="features" class="border-b border-slate-800 bg-slate-950">
                <div class="max-w-6xl mx-auto px-4 py-12 space-y-8">
                    <h2 class="text-2xl font-bold">LinkForge est le remède</h2>
                    <div class="grid md:grid-cols-3 gap-6 text-sm text-slate-300">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-5 space-y-2">
                            <p class="text-xs uppercase tracking-[0.2em] text-indigo-300">Tracking chirurgical</p>
                            <p class="text-lg font-semibold text-white">Sachez tout de vos visiteurs</p>
                            <p>Pays, ville, appareil (mobile/desktop/tablette), navigateur. La data que les réseaux cachent.</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-5 space-y-2">
                            <p class="text-xs uppercase tracking-[0.2em] text-indigo-300">Campagnes & Sources</p>
                            <p class="text-lg font-semibold text-white">Organisez, ne subissez plus</p>
                            <p>Groupez par campagne (Black Friday, Lancement ebook) et par source (Bio TikTok, Story). Comparez en un coup d’œil.</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-5 space-y-2">
                            <p class="text-xs uppercase tracking-[0.2em] text-indigo-300">Redirection intelligente</p>
                            <p class="text-lg font-semibold text-white">Ne perdez jamais un clic</p>
                            <p>Propagation automatique des paramètres (UTM, affiliation) et redirection ultra-rapide.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- USE CASES -->
            <section id="for-who" class="border-b border-slate-800 bg-slate-950/90">
                <div class="max-w-6xl mx-auto px-4 py-12 grid md:grid-cols-3 gap-6 text-sm text-slate-300">
                    <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4 space-y-2">
                        <p class="text-xs uppercase tracking-[0.2em] text-indigo-300">Infopreneur</p>
                        <p class="text-white font-semibold">Comparer YouTube vs Instagram</p>
                        <p>Trackez les clics sur vos ebooks et identifiez les vidéos qui convertissent.</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4 space-y-2">
                        <p class="text-xs uppercase tracking-[0.2em] text-indigo-300">Affilié</p>
                        <p class="text-white font-semibold">Nettoyez vos liens</p>
                        <p>Masquez les URL moches, sécurisez vos paramètres et suivez chaque clic par source.</p>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4 space-y-2">
                        <p class="text-xs uppercase tracking-[0.2em] text-indigo-300">Marketer</p>
                        <p class="text-white font-semibold">Prouvez le ROI</p>
                        <p>Rapports clairs par campagne/source, exports CSV pour vos clients.</p>
                    </div>
                </div>
            </section>

            <!-- PRICING -->
            <section id="pricing" class="border-b border-slate-800 bg-slate-950">
                <div class="max-w-6xl mx-auto px-4 py-12 space-y-6">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-indigo-300">Pricing simple</p>
                            <h2 class="text-2xl font-bold">Une offre gratuite, une offre Pro : c'est tout.</h2>
                        </div>
                        <p class="text-sm text-slate-400">Essai gratuit. Pas de carte.</p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div
                            v-for="plan in pricingCards"
                            :key="plan.id"
                            :class="[
                                'rounded-2xl border p-6 space-y-3 bg-slate-950/70',
                                plan.featured ? 'border-indigo-500 shadow-xl shadow-indigo-900/30' : 'border-slate-800'
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ plan.name }}</p>
                                    <p class="text-3xl font-bold text-white">{{ plan.priceLabel }}</p>
                                </div>
                                <span v-if="plan.featured" class="text-[11px] rounded-full bg-indigo-500/15 text-indigo-200 px-3 py-1 border border-indigo-500/40">
                                    Le plus populaire
                                </span>
                            </div>
                            <p class="text-sm text-slate-300">{{ plan.description }}</p>

                            <ul class="space-y-1 text-sm text-slate-200">
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
                                    :class="plan.featured ? 'bg-indigo-500 text-white hover:bg-indigo-400 shadow-indigo-900/30' : 'border border-slate-700 text-slate-100 hover:border-indigo-400'">
                                    {{ plan.ctaLabel }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section id="faq" class="bg-slate-950">
                <div class="max-w-6xl mx-auto px-4 py-12 space-y-6">
                    <h2 class="text-2xl font-bold">FAQ</h2>
                    <div class="grid md:grid-cols-2 gap-4 text-sm text-slate-200">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-4 space-y-2">
                            <p class="font-semibold text-white">LinkForge impacte-t-il le SEO ?</p>
                            <p>Non, redirections propres (301/302), pas de contenu dupliqué.</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-4 space-y-2">
                            <p class="font-semibold text-white">Puis-je modifier un lien après partage ?</p>
                            <p>Oui, vous pouvez mettre à jour l’URL cible à tout moment sans changer le lien court.</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-4 space-y-2">
                            <p class="font-semibold text-white">Compatible avec l’affiliation Amazon ?</p>
                            <p>Oui, nous gardons vos paramètres et UTM pour préserver le tracking Amazon.</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-4 space-y-2">
                            <p class="font-semibold text-white">Dois-je ajouter du code ?</p>
                            <p>Non, tout se passe via l’app : générez vos liens courts, copiez-collez, c’est tout.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
