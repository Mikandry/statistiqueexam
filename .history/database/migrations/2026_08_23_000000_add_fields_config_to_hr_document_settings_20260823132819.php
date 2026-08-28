<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_document_settings', function (Blueprint $table) {
            $table->json('fields_config')->nullable()->after('ville');
        });

        // Config par défaut des champs affichés pour chaque document
        \Illuminate\Support\Facades\DB::table('hr_document_settings')->update([
            'fields_config' => json_encode([
                'non-interruption' => [
                    'nom', 'im', 'corps_grade', 'budget_chapitre', 'date_recrutement', 'service_direction',
                ],
                'non-jouissance' => [
                    'nom', 'im', 'corps_grade', 'date_recrutement',
                ],
                'prise-service' => [
                    'nom', 'im', 'corps_grade', 'fonction', 'service_direction',
                ],
                'conge' => [
                    'nom', 'im', 'corps_grade',
                ],
                'absence' => [
                    'nom', 'im', 'corps_grade',
                ],
                'mission' => [
                    'nom', 'im', 'corps_grade',
                ],
                'formation' => [
                    'nom', 'im', 'corps_grade',
                ],
                'autre' => [
                    'nom', 'im', 'corps_grade',
                ],
                'fiche-administrative' => [
                    'nom', 'im', 'date_naissance', 'corps', 'grade', 'indice', 'categorie',
                    'echelon', 'budget', 'chapitre', 'date_recrutement', 'date_prise_service',
                    'direction', 'service', 'bureau', 'fonction', 'situation_administrative',
                ],
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('hr_document_settings', function (Blueprint $table) {
            $table->dropColumn('fields_config');
        });
    }
};