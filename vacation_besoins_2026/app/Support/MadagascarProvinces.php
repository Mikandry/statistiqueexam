<?php

namespace App\Support;

/**
 * Rattachement DREN -> province (6 provinces de Madagascar) deviné à partir du
 * nom de la DREN. Sert uniquement de pré-remplissage : tout est modifiable dans
 * Vacation 2026 > Besoins & montants > Paramètres.
 */
final class MadagascarProvinces
{
    public const PROVINCES = ['Antananarivo', 'Toamasina', 'Mahajanga', 'Toliara', 'Antsiranana', 'Fianarantsoa'];

    /**
     * Ordre important : les motifs les plus spécifiques d'abord
     * (« atsimo atsinanana » avant « atsinanana », « atsimo andrefana » avant « andrefana »).
     * Le 3e élément indique la région chef-lieu de province.
     */
    private const MAP = [
        ['atsimo atsinanana', 'Fianarantsoa', false],
        ['atsimo andrefana', 'Toliara', true],
        ['haute matsiatra', 'Fianarantsoa', true],
        ['amoron', 'Fianarantsoa', false],
        ['vatovavy', 'Fianarantsoa', false],
        ['fitovinany', 'Fianarantsoa', false],
        ['ihorombe', 'Fianarantsoa', false],
        ['analamanga', 'Antananarivo', true],
        ['itasy', 'Antananarivo', false],
        ['bongolava', 'Antananarivo', false],
        ['vakinankaratra', 'Antananarivo', false],
        ['atsinanana', 'Toamasina', true],
        ['analanjirofo', 'Toamasina', false],
        ['alaotra', 'Toamasina', false],
        ['boeny', 'Mahajanga', true],
        ['sofia', 'Mahajanga', false],
        ['betsiboka', 'Mahajanga', false],
        ['melaky', 'Mahajanga', false],
        ['androy', 'Toliara', false],
        ['anosy', 'Toliara', false],
        ['menabe', 'Toliara', false],
        ['diana', 'Antsiranana', true],
        ['sava', 'Antsiranana', false],
    ];

    /** @return array{province:string, chef_lieu:bool}|null */
    public static function guess(string $drenName): ?array
    {
        $name = mb_strtolower(trim($drenName));
        $name = str_replace(['’', "'", '-'], ' ', $name);

        foreach (self::MAP as [$needle, $province, $chefLieu]) {
            if (str_contains($name, $needle)) {
                return ['province' => $province, 'chef_lieu' => $chefLieu];
            }
        }

        return null;
    }
}
