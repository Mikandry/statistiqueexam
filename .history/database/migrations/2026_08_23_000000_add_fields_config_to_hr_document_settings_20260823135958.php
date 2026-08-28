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
        $defaultFields = [
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
        ];

        $existing = \Illuminate\Support\Facades\DB::table('hr_document_settings')
            ->where('id', 1)
            ->first();

        if ($existing) {
            \Illuminate\Support\Facades\DB::table('hr_document_settings')
                ->where('id', $existing->id)
                ->update([
                    'fields_config' => json_encode($defaultFields),
                    'updated_at' => now(),
                ]);
        } else {
            \Illuminate\Support\Facades\DB::table('hr_document_settings')->insert([
                'fields_config' => json_encode($defaultFields),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
