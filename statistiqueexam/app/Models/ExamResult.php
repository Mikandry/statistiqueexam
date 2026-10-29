<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'en_attente';
    public const STATUS_IN_PROGRESS = 'en_cours';
    public const STATUS_PUBLISHED = 'publie';

    protected $fillable = [
        'dren_id',
        'cisco_id',
        'created_by',
        'year',
        'exam_name',
        'total_candidates',
        'absent_candidates',
        'present_candidates',
        'admitted_candidates',
        'admission_threshold',
        'success_rate',
        'abandonment_rate',
        'published_at',
        'status',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'admission_threshold' => 'decimal:2',
            'success_rate' => 'decimal:2',
            'abandonment_rate' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ExamResult $result): void {
            $result->recalculate();
        });
    }

    public function dren(): BelongsTo
    {
        return $this->belongsTo(Dren::class);
    }

    public function cisco(): BelongsTo
    {
        return $this->belongsTo(Cisco::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculate(): void
    {
        $total = max(0, (int) $this->total_candidates);
        $absents = max(0, min($total, (int) ($this->absent_candidates ?? 0)));
        $presents = max(0, $total - $absents);
        $admitted = max(0, min($presents, (int) ($this->admitted_candidates ?? 0)));

        $this->present_candidates = $presents;
        $this->admitted_candidates = $this->admitted_candidates === null ? null : $admitted;
        $this->success_rate = $presents > 0 ? round(($admitted / $presents) * 100, 2) : 0;
        $this->abandonment_rate = $total > 0 ? round(($absents / $total) * 100, 2) : 0;
        $this->status = $this->resolveStatus();

        if ($this->status === self::STATUS_PUBLISHED && ! $this->published_at) {
            $this->published_at = now();
        }
    }

    public function resolveStatus(): string
    {
        $hasAnyOfficialData = $this->absent_candidates !== null
            || $this->admitted_candidates !== null
            || $this->admission_threshold !== null;

        if (! $hasAnyOfficialData) {
            return self::STATUS_PENDING;
        }

        if (
            $this->absent_candidates !== null
            && $this->admitted_candidates !== null
            && $this->admission_threshold !== null
        ) {
            return self::STATUS_PUBLISHED;
        }

        return self::STATUS_IN_PROGRESS;
    }

    public function scopeFiltered($query, array $filters)
    {
        return $query
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->where('year', $year))
            ->when($filters['exam_name'] ?? null, fn ($query, $exam) => $query->where('exam_name', $exam))
            ->when($filters['dren_id'] ?? null, fn ($query, $drenId) => $query->where('dren_id', $drenId))
            ->when($filters['cisco_id'] ?? null, fn ($query, $ciscoId) => $query->where('cisco_id', $ciscoId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['published_at'] ?? null, fn ($query, $date) => $query->whereDate('published_at', $date))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->whereHas('dren', fn ($q) => $q->where('nom', 'like', "%{$search}%"))
                        ->orWhereHas('cisco', fn ($q) => $q->where('nom', 'like', "%{$search}%"));
                });
            });
    }
}
