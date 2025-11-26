<script setup>
import { Head } from '@inertiajs/vue3';
import LegalLayout from '@/Layouts/LegalLayout.vue';
</script>

<template>
    <Head title="Conditions Générales d'Utilisation" />

    <LegalLayout>
        <article class="prose dark:prose-invert max-w-none">
            <h1>Conditions Générales d'Utilisation</h1>
            <p class="text-sm text-slate-500">Dernière mise à jour : 22 Novembre 2025</p>

            <h2>Mentions Légales</h2>
            <p>
                <strong>Éditeur du Service :</strong><br>
                Cornec Jean-Michel<br>
                Forme juridique :  EI<br>
                SIRET : 88293195900028<br>
                Siège social : 12 rue chateaubriand 13007 Marseille<br>
                Directeur de la publication : Cornec Jean-Michel<br>
            </p>
            <p>
                <strong>Hébergeur :</strong><br>
                Nom : Hetzner<br>
                Adresse : Hetzner Online GmbH<br>
                Industriestr. 25<br>
                91710 Gunzenhausen<br>
                Germany<br>
            </p>
            <hr class="my-8 border-slate-200 dark:border-slate-700">

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

            <h2>4. Utilisation acceptable (Tolérance Zéro)</h2>
            <p>
                Il est strictement interdit d'utiliser LinkForge pour masquer des URL de destination menant vers : du phishing (hameçonnage), des malwares, de la pornographie illégale, de la vente de produits illicites ou contrefaits.
            </p>
            <p>
                <strong>LinkForge se réserve le droit de désactiver immédiatement tout lien suspect et de bannir le compte de l'utilisateur sans remboursement ni préavis.</strong>
                Nous collaborons avec les autorités et les services de sécurité (Google Safe Browsing) pour signaler les abus.
            </p>

            <h2>5. Abonnements et Droit de Rétractation</h2>
            <p>
                Certaines fonctionnalités nécessitent un abonnement payant (Plan Pro). Les paiements sont traités de manière sécurisée via Stripe.
            </p>
            <p>
                <strong>Renoncement au droit de rétractation :</strong><br>
                Conformément à l'article L221-28 du Code de la consommation, en souscrivant au Plan Pro, l'utilisateur accepte que la fourniture du service commence immédiatement et renonce expressément à son droit de rétractation de 14 jours. Aucun remboursement ne sera effectué pour la période en cours.
            </p>

            <h2>6. Limitation de responsabilité</h2>
            <p>
                LinkForge est fourni "tel quel". Nous ne garantissons pas que le service sera ininterrompu ou exempt d'erreurs.
                Nous ne sommes pas responsables des dommages indirects résultant de l'utilisation du service.
            </p>

            <h2>7. Contact</h2>
            <p>
                Pour toute question concernant ces conditions, contactez-nous à <a href="mailto:contact@linkforge.fr">contact@linkforge.fr</a>.
            </p>
        </article>
    </LegalLayout>
</template>
