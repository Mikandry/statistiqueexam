<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'ip',
        'device_name',
        'user_agent',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(Request $request, string $action, array $meta = []): void
    {
        $user = $request->user();
        $userAgent = (string) ($request->userAgent() ?? '');
        $deviceHeader = trim((string) ($request->header('X-Device-Name') ?? ''));
        $deviceName = $deviceHeader !== '' ? $deviceHeader : self::inferDeviceName($userAgent);

        self::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'ip' => $request->ip(),
            'device_name' => $deviceName,
            'user_agent' => $userAgent,
            'meta' => $meta === [] ? null : $meta,
        ]);
    }

    private static function inferDeviceName(string $userAgent): ?string
    {
        if ($userAgent === '') {
            return null;
        }

        $label = 'Poste';
        if (str_contains($userAgent, 'Windows')) {
            $label = 'Windows';
        } elseif (str_contains($userAgent, 'Mac OS X') || str_contains($userAgent, 'Macintosh')) {
            $label = 'Mac';
        } elseif (str_contains($userAgent, 'Android')) {
            $label = 'Android';
        } elseif (str_contains($userAgent, 'iPhone')) {
            $label = 'iPhone';
        } elseif (str_contains($userAgent, 'iPad')) {
            $label = 'iPad';
        } elseif (str_contains($userAgent, 'Linux')) {
            $label = 'Linux';
        }

        return $label;
    }
}
