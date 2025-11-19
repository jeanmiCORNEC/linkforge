<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * ---- CLICKs : analytics & visiteurs uniques ----
         *
         * État actuel (dump 2025-11-17_221259) :
         * - PK (id)
         * - KEY clicks_tracked_link_id_foreign (tracked_link_id)
         * - KEY clicks_visitor_hash_index (visitor_hash)
         *
         * On ajoute seulement des index composites,
         * sans toucher aux index existants.
         */
        Schema::table('clicks', function (Blueprint $table) {
            // Agrégations par lien tracké + période
            // pour les graphes "clics par jour"
            $table->index(
                ['tracked_link_id', 'created_at'],
                'clicks_tracked_link_id_created_at_index'
            );

            // Accélérer les calculs de visiteurs uniques
            // (COUNT(DISTINCT visitor_hash) filtré par tracked_link_id)
            $table->index(
                ['tracked_link_id', 'visitor_hash'],
                'clicks_tracked_link_id_visitor_hash_index'
            );
        });

        /*
         * ---- TRACKED_LINKS : jointures fréquentes & listings ----
         */
        Schema::table('tracked_links', function (Blueprint $table) {
            // Listing / filtres par date de création
            $table->index('created_at', 'tracked_links_created_at_index');

            // Joins fréquents : source -> tracked_links -> links
            $table->index(
                ['source_id', 'link_id'],
                'tracked_links_source_id_link_id_index'
            );
        });

        /*
         * ---- LINKS : listings par date ----
         */
        Schema::table('links', function (Blueprint $table) {
            $table->index('created_at', 'links_created_at_index');
        });

        /*
         * ---- CAMPAIGNS : filtres status + date ----
         */
        Schema::table('campaigns', function (Blueprint $table) {
            $table->index('created_at', 'campaigns_created_at_index');
            $table->index('status', 'campaigns_status_index');
        });

        /*
         * ---- SOURCES : listings par date ----
         */
        Schema::table('sources', function (Blueprint $table) {
            $table->index('created_at', 'sources_created_at_index');
        });
    }

    public function down(): void
    {
        // On retire uniquement les index ajoutés par cette migration.
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropIndex('clicks_tracked_link_id_created_at_index');
            $table->dropIndex('clicks_tracked_link_id_visitor_hash_index');
        });

        Schema::table('tracked_links', function (Blueprint $table) {
            $table->dropIndex('tracked_links_created_at_index');
            $table->dropIndex('tracked_links_source_id_link_id_index');
        });

        Schema::table('links', function (Blueprint $table) {
            $table->dropIndex('links_created_at_index');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropIndex('campaigns_created_at_index');
            $table->dropIndex('campaigns_status_index');
        });

        Schema::table('sources', function (Blueprint $table) {
            $table->dropIndex('sources_created_at_index');
        });
    }
};
