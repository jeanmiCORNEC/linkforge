<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="LinkForge – Suis enfin les liens qui rapportent" />

    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">
        <!-- Top bar -->
        <header class="border-b border-slate-800">
            <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <ApplicationLogo class="w-8 h-8 text-indigo-400" />
                    <span class="font-semibold text-lg tracking-tight">
                        LinkForge
                    </span>
                </div>

                <nav class="flex items-center gap-4 text-sm">
                    <a href="#how-it-works" class="hover:text-indigo-300 transition">Comment ça marche</a>
                    <a href="#for-who" class="hover:text-indigo-300 transition">Pour qui ?</a>
                    <a href="#features" class="hover:text-indigo-300 transition">Fonctionnalités</a>

                    <div class="h-6 w-px bg-slate-700 mx-2" />

                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="text-sm hover:text-indigo-300 transition"
                    >
                        Connexion
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="inline-flex items-center rounded-full bg-indigo-500 px-4 py-1.5 text-sm font-medium text-white shadow hover:bg-indigo-400 transition"
                    >
                        Créer un compte gratuit
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <main class="flex-1">
            <section class="border-b border-slate-800 bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900">
                <div class="max-w-6xl mx-auto px-4 py-16 grid md:grid-cols-2 gap-12 items-center">
                    <div class="space-y-6">
                        <p class="inline-flex items-center rounded-full border border-indigo-500/40 bg-indigo-500/10 px-3 py-1 text-xs font-medium text-indigo-200 uppercase tracking-[0.15em]">
                            SaaS pour créateurs & affiliés
                        </p>

                        <h1 class="text-3xl md:text-4xl font-bold tracking-tight">
                            <span class="block">Sache enfin</span>
                            <span class="block text-indigo-400">quels liens rapportent vraiment.</span>
                        </h1>

                        <p class="text-sm md:text-base text-slate-300 leading-relaxed">
                            LinkForge suit chaque clic sur tes liens d’affiliation
                            sur TikTok, YouTube, Instagram & co, et te montre
                            quelles vidéos, stories ou posts génèrent réellement
                            des clics et des revenus.
                        </p>

                        <ul class="space-y-2 text-sm text-slate-300">
                            <li class="flex gap-2">
                                <span class="mt-1 h-4 w-4 rounded-full bg-indigo-500 flex-shrink-0" />
                                <span>Relie chaque lien à une <strong>source</strong> : vidéo, story, post…</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="mt-1 h-4 w-4 rounded-full bg-emerald-500 flex-shrink-0" />
                                <span>Vois en un coup d’œil tes liens et contenus les plus rentables.</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="mt-1 h-4 w-4 rounded-full bg-fuchsia-500 flex-shrink-0" />
                                <span>Prouve ta valeur aux marques avec des stats claires.</span>
                            </li>
                        </ul>

                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="inline-flex items-center rounded-md bg-indigo-500 px-5 py-2.5 text-sm font-medium text-white shadow hover:bg-indigo-400 transition"
                            >
                                Commencer gratuitement
                            </Link>

                            <p class="text-xs text-slate-400">
                                Plan gratuit pour toujours • Idéal 1 000 – 50 000 abonnés.
                            </p>
                        </div>
                    </div>

                    <!-- Faux dashboard aperçu -->
                    <div class="hidden md:block">
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-4 shadow-xl shadow-indigo-900/30">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="text-xs text-slate-400">Dashboard créateur</p>
                                    <p class="text-sm font-semibold">Exemple : @setup.tok</p>
                                </div>
                                <span class="text-[10px] rounded-full bg-emerald-500/10 text-emerald-300 px-2 py-0.5 border border-emerald-500/40">
                                    Plan FREE
                                </span>
                            </div>

                            <div class="grid grid-cols-3 gap-3 mb-4">
                                <div class="rounded-xl bg-slate-950/60 border border-slate-800 p-3">
                                    <p class="text-[11px] text-slate-400">Clics (7 jours)</p>
                                    <p class="text-lg font-semibold">1 284</p>
                                    <p class="text-[11px] text-emerald-400 mt-1">+32%</p>
                                </div>
                                <div class="rounded-xl bg-slate-950/60 border border-slate-800 p-3">
                                    <p class="text-[11px] text-slate-400">Liens trackés</p>
                                    <p class="text-lg font-semibold">27</p>
                                    <p class="text-[11px] text-slate-400 mt-1">6 pages bio</p>
                                </div>
                                <div class="rounded-xl bg-slate-950/60 border border-slate-800 p-3">
                                    <p class="text-[11px] text-slate-400">Sources actives</p>
                                    <p class="text-lg font-semibold">14</p>
                                    <p class="text-[11px] text-slate-400 mt-1">Shorts, Reels, Stories</p>
                                </div>
                            </div>

                            <p class="text-[11px] text-slate-400 mb-2">Top sources</p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="flex justify-between">
                                    <span>🎧 TikTok – Setup 2025</span>
                                    <span class="text-slate-300">432 clics</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>📦 YouTube – Unboxing micro</span>
                                    <span class="text-slate-300">311 clics</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>🛒 Story Insta – Code -10%</span>
                                    <span class="text-slate-300">187 clics</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section : Pour qui ? -->
            <section id="for-who" class="border-b border-slate-800 bg-slate-950">
                <div class="max-w-6xl mx-auto px-4 py-10 grid md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <h2 class="text-base font-semibold mb-2">Pour qui ?</h2>
                        <p class="text-slate-300">
                            Pour les créateurs entre <strong>1k et 50k abonnés</strong>
                            qui font de l’affiliation et veulent arrêter de
                            travailler “dans le flou”.
                        </p>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold mb-2">Quel problème ?</h2>
                        <p class="text-slate-300">
                            Tu sais que tu as des clics, mais tu ignores
                            <strong>quelle vidéo</strong> ou <strong>quelle story</strong>
                            les a générés. LinkForge relie chaque clic à une
                            source précise.
                        </p>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold mb-2">Ce que tu gagnes</h2>
                        <p class="text-slate-300">
                            Tu peux : arrêter les contenus qui ne performent pas,
                            doubler sur ceux qui convertissent, et montrer des
                            stats claires aux marques qui te contactent.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Section : Comment ça marche -->
            <section id="how-it-works" class="bg-slate-950">
                <div class="max-w-6xl mx-auto px-4 py-10 text-sm">
                    <h2 class="text-base font-semibold mb-4">Comment ça marche ?</h2>
                    <ol class="grid md:grid-cols-3 gap-4 list-decimal list-inside text-slate-300">
                        <li>
                            Tu crées un compte et définis tes premières
                            <strong>sources</strong> : “Short setup”, “Story promo micro”, etc.
                        </li>
                        <li>
                            Tu colles tes liens d’affiliation, LinkForge génère
                            des liens courts trackés pour chaque source.
                        </li>
                        <li>
                            Tu colles ces liens dans tes bios / descriptions.
                            LinkForge enregistre chaque clic et te montre ce qui
                            performe vraiment.
                        </li>
                    </ol>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-800 bg-slate-950">
            <div class="max-w-6xl mx-auto px-4 py-4 text-[11px] flex justify-between text-slate-500">
                <span>© {{ new Date().getFullYear() }} LinkForge – Micro-SaaS pour créateurs & affiliés.</span>
                <span>Made en France 🇫🇷</span>
            </div>
        </footer>
    </div>
</template>
