<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_name',
        'account_type',
        'balance',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'ledger_account_id');
    }

    // Update balance atomically
    public function updateBalance($delta, $userId = null)
    {
        $this->increment('balance', $delta > 0 ? $delta : 0);
        $this->decrement('balance', $delta < 0 ? abs($delta) : 0);
        $this->update(['last_updated' => now()]);
        
        // Log transaction (double-entry would be more complex)
        \Log::info("Ledger {$this->account_name} balance updated by {$delta}. New balance: {$this->fresh()->balance}", [
            'user_id' => $userId,
            'old_balance' => $this->getOriginal('balance'),
        ]);
    }
}
