<script setup>
import { Head } from '@inertiajs/vue3';
import LegalLayout from '@/Layouts/LegalLayout.vue';
</script>

<template>
    <Head title="Politique de Confidentialité" />

    <LegalLayout>
        <article class="prose dark:prose-invert max-w-none">
            <h1>Politique de Confidentialité</h1>
            <p class="text-sm text-slate-500">Dernière mise à jour : 21 Novembre 2025</p>

            <h2>1. Collecte des données</h2>
            <p>
                Nous collectons les informations que vous nous fournissez directement (email, nom) lors de l'inscription.
                Nous collectons également des données techniques lors de l'utilisation de nos liens (adresse IP, User-Agent, Pays) à des fins d'analyse.
            </p>

            <h2>2. Utilisation des données</h2>
            <p>
                Nous utilisons vos données pour :
            </p>
            <ul>
                <li>Fournir et maintenir le service.</li>
                <li>Générer des statistiques anonymisées pour les créateurs de liens.</li>
                <li>Vous envoyer des notifications importantes (mises à jour, alertes de sécurité).</li>
            </ul>

            <h2>3. Partage des données</h2>
            <p>
                Nous ne vendons pas vos données personnelles. Nous partageons des données avec des tiers uniquement pour le fonctionnement du service
                (ex: Stripe pour les paiements, Mailtrap/AWS pour les emails).
            </p>

            <h2>4. Cookies</h2>
            <p>
                Nous utilisons des cookies pour maintenir votre session connectée et pour des analyses internes.
            </p>

            <h2>5. Sécurité</h2>
            <p>
                Nous mettons en œuvre des mesures de sécurité appropriées pour protéger vos données contre l'accès non autorisé.
            </p>

            <h2>6. Vos droits</h2>
            <p>
                Conformément au RGPD, vous disposez d'un droit d'accès, de rectification et de suppression de vos données.
                Vous pouvez exercer ces droits depuis votre compte ou en nous contactant.
            </p>

            <h2>7. Contact</h2>
            <p>
                Pour toute question sur la confidentialité, contactez-nous à <a href="mailto:hello@linkforge.app">hello@linkforge.app</a>.
            </p>
        </article>
    </LegalLayout>
</template>
