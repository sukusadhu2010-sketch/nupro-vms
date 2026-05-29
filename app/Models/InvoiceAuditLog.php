<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAuditLog extends Model
{
    protected $table = 'invoice_audit_logs';

    protected $fillable = [
        'invoice_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the invoice that this audit log belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Invoice Created',
            'updated' => 'Invoice Updated',
            'sent' => 'Invoice Sent',
            'paid' => 'Payment Recorded',
            'partial' => 'Partial Payment',
            'cancelled' => 'Invoice Cancelled',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get the changes as an array.
     */
    public function getChangesAttribute(): array
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];
        
        $changes = [];
        foreach ($new as $key => $value) {
            if (!isset($old[$key])) {
                $changes[$key] = ['added' => $value];
            } elseif ($old[$key] !== $value) {
                $changes[$key] = ['from' => $old[$key], 'to' => $value];
            }
        }

        return $changes;
    }
}
