<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a critical action. Never throws — auditing must not break the app.
     */
    public static function record(string $action, ?string $module = null, ?string $description = null): void
    {
        try {
            static::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'ip_address' => request()?->ip(),
                'user_agent' => substr((string) request()?->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            // swallow - auditing must never break business flow
        }
    }
}
