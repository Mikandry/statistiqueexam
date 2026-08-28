<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrDocumentSetting extends Model
{
    protected $table = 'hr_document_settings';

    protected $fillable = [
        'ministere',
        'secretariat_general',
        'direction_generale',
        'direction',
        'service',

        'reference_prefix',
        'next_reference_number',
        'reference_year',

        'signataire',
        'signataire_qualite',
        'signataire_niveau',

        'ville',

        'fields_config',
    ];

    protected function casts(): array
    {
        return [
            'reference_year' => 'integer',
            'next_reference_number' => 'integer',
            'fields_config' => 'array',
        ];
    }

    /**
     * Champs à afficher pour un type de document donné.
     */
    public function fieldsFor(string $document): array
    {
        $config = $this->fields_config ?? [];

        if (isset($config[$document]) && is_array($config[$document])) {
            return $config[$document];
        }

        // Valeurs par défaut
        return match ($document) {
            'fiche-administrative' => [
                'nom', 'im', 'date_naissance', 'corps', 'grade', 'indice', 'categorie',
                'echelon', 'budget', 'chapitre', 'date_recrutement', 'date_prise_service',
                'direction', 'service', 'bureau', 'fonction', 'situation_administrative',
            ],
            default => ['nom', 'im', 'corps_grade'],
        };
    }

    /**
     * Tous les champs disponibles avec leurs libellés.
     */
    public static function availableFields(): array
    {
        return [
            'nom' => 'Nom et Prénoms',
            'im' => 'IM / Matricule',
            'corps_grade' => 'Corps et Grade',
            'budget_chapitre' => 'Budget / Chapitre',
            'date_recrutement' => 'Date d’entrée dans l’Administration',
            'date_naissance' => 'Date de naissance',
            'corps' => 'Corps',
            'grade' => 'Grade',
            'indice' => 'Indice',
            'categorie' => 'Catégorie',
            'echelon' => 'Échelon',
            'budget' => 'Budget',
            'chapitre' => 'Chapitre',
            'date_prise_service' => 'Date de prise de service',
            'direction' => 'Direction',
            'service' => 'Service',
            'service_direction' => 'Direction / Service',
            'bureau' => 'Bureau',
            'fonction' => 'Fonction',
            'situation_administrative' => 'Situation administrative',
        ];
    }
}
