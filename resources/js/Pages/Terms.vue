<script setup>
import { Head } from '@inertiajs/vue3';
import LegalLayout from '@/Layouts/LegalLayout.vue';
</script>

<template>
    <Head title="Conditions Générales d'Utilisation" />

    <LegalLayout>
        <article class="prose dark:prose-invert max-w-none">
            <h1>Conditions Générales d'Utilisation</h1>
            <p class="text-sm text-slate-500">Dernière mise à jour : 21 Novembre 2025</p>

            <h2>1. Acceptation des conditions</h2>
            <p>
                En accédant et en utilisant LinkForge, vous acceptez d'être lié par les présentes conditions générales.
                Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser nos services.
            </p>

            <h2>2. Description du service</h2>
            <p>
                LinkForge fournit des outils de raccourcissement de liens et d'analyse d'audience.
                Nous nous réservons le droit de modifier ou d'interrompre le service à tout moment.
            </p>

            <h2>3. Inscription et Compte</h2>
            <p>
                Pour accéder à certaines fonctionnalités, vous devez créer un compte. Vous êtes responsable de la confidentialité
                de vos identifiants et de toutes les activités effectuées sous votre compte.
            </p>

            <h2>4. Utilisation acceptable</h2>
            <p>
                Vous vous engagez à ne pas utiliser le service pour :
            </p>
            <ul>
                <li>Diffuser du contenu illégal, nuisible ou frauduleux.</li>
                <li>Spammer ou envoyer des messages non sollicités.</li>
                <li>Tenter de perturber l'intégrité de nos systèmes.</li>
            </ul>
            <p>
                Nous nous réservons le droit de supprimer tout lien ou compte violant ces règles sans préavis.
            </p>

            <h2>5. Abonnements et Paiements</h2>
            <p>
                Certaines fonctionnalités nécessitent un abonnement payant (Plan Pro). Les paiements sont traités de manière sécurisée via Stripe.
                L'abonnement est renouvelé automatiquement sauf résiliation de votre part.
            </p>

            <h2>6. Limitation de responsabilité</h2>
            <p>
                LinkForge est fourni "tel quel". Nous ne garantissons pas que le service sera ininterrompu ou exempt d'erreurs.
                Nous ne sommes pas responsables des dommages indirects résultant de l'utilisation du service.
            </p>

            <h2>7. Contact</h2>
            <p>
                Pour toute question concernant ces conditions, contactez-nous à <a href="mailto:hello@linkforge.app">hello@linkforge.app</a>.
            </p>
        </article>
    </LegalLayout>
</template>
