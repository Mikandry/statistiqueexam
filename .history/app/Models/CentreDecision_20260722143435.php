<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class CentreDecision extends Model
{
    protected $fillable = [
        'annee',
        'centre_correction_id',
        'centre_ecrit_id',
        'type_examen',
        'actif',
    ];

    public function centreCorrection(): BelongsTo
    {
        return $this->belongsTo(CentreCorrection::class);
    }

    public function centreEcrit(): BelongsTo
    {
        return $this->belongsTo(CentreEcrit::class);
    }

    public static function availableYears(): Collection
    {
        $decisionYears = self::query()
            ->select('annee')
            ->whereNotNull('annee')
            ->where('annee', '<>', '')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee');

        $repartitionYears = RepartitionSalle::query()
            ->select('annee')
            ->whereNotNull('annee')
            ->where('annee', '<>', '')
            ->distinct()
            ->orderByDesc('annee')
            ->pluck('annee');

        return $decisionYears
            ->merge($repartitionYears)
            ->filter(fn ($annee) => filled($annee))
            ->unique()
            ->sortDesc()
            ->values();
    }

    public static function getActiveForSession(?string $annee, ?string $typeExamen = null): Collection
    {
        $year = trim((string) $annee);
        if ($year === '') {
            return collect();
        }

        $active = self::query()
            ->where('annee', $year)
            ->where('actif', true)
            ->when(filled($typeExamen), fn ($query) => $query->where('type_examen', $typeExamen))
            ->orderBy('centre_ecrit_id')
            ->get();

        if ($active->isNotEmpty()) {
            return $active;
        }

        $fallbackYear = self::resolvePreviousYear($year);
        if ($fallbackYear === null || $fallbackYear === $year) {
            return collect();
        }

        return self::query()
            ->where('annee', $fallbackYear)
            ->where('actif', true)
            ->when(filled($typeExamen), fn ($query) => $query->where('type_examen', $typeExamen))
            ->orderBy('centre_ecrit_id')
            ->get();
    }

    public static function resolvePreviousYear(?string $annee): ?string
    {
        $year = trim((string) $annee);
        if ($year === '') {
            return null;
        }

        $years = self::availableYears()
            ->filter(fn ($candidate) => self::compareYears($candidate, $year) < 0)
            ->values();

        if ($years->isEmpty()) {
            return null;
        }

        return (string) $years->first();
    }

    public static function importFromPreviousSession(string $targetAnnee, ?string $sourceAnnee = null, ?string $typeExamen = null): int
    {
        $source = trim((string) ($sourceAnnee ?? ''));
        if ($source === '') {
            $source = self::resolvePreviousYear($targetAnnee) ?? '';
        }

        if ($source === '') {
            return 0;
        }

        $previousRows = self::query()
            ->where('annee', $source)
            ->where('actif', true)
            ->when(filled($typeExamen), fn ($query) => $query->where('type_examen', $typeExamen))
            ->get();

        foreach ($previousRows as $row) {
            self::updateOrCreate(
                [
                    'annee' => $targetAnnee,
                    'centre_ecrit_id' => $row->centre_ecrit_id,
                ],
                [
                    'centre_correction_id' => $row->centre_correction_id,
                    'type_examen' => $row->type_examen,
                    'actif' => true,
                ]
            );
        }

        return $previousRows->count();
    }

    private static function compareYears(string $candidate, string $reference): int
    {
        $candidateValue = self::yearValue($candidate);
        $referenceValue = self::yearValue($reference);

        if ($candidateValue === null || $referenceValue === null) {
            return strcmp($candidate, $reference);
        }

        return $candidateValue <=> $referenceValue;
    }

    private static function yearValue(?string $value): ?int
    {
        $year = trim((string) $value);
        if ($year === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{4})$/', $year, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/^(\d{4})$/', $year, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
