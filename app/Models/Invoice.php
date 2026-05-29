<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'sales_order_id',
        'customer_id',
        'status',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'notes',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Statuses available for invoices
     */
    public static $statuses = ['draft', 'sent', 'paid', 'partial', 'cancelled'];

    /**
     * Get the sales order that this invoice belongs to.
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the customer that this invoice belongs to.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all items for this invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get all audit logs for this invoice.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(InvoiceAuditLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all payments for this invoice.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable')->orderBy('payment_date', 'desc');
    }

    /**
     * Get total paid amount from payments (aggregate).
     */
    public function getTotalPaidFromPaymentsAttribute(): float
    {
        return $this->payments->sum('amount');
    }

    /**
     * Get the user who created this invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who cancelled this invoice.
     */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Check if the invoice is cancelled.
     */
    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled' && $this->cancelled_at !== null;
    }

    /**
     * Check if the invoice is paid.
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if the invoice is partially paid.
     */
    public function getIsPartialAttribute(): bool
    {
        return $this->status === 'partial';
    }

    /**
     * Get the outstanding balance.
     */
    public function getOutstandingAmountAttribute(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    /**
     * Get status badge CSS class.
     */
    public function getStatusBadgeAttribute(): string
    {
        $config = [
            'draft' => 'bg-secondary',
            'sent' => 'bg-info',
            'paid' => 'bg-success',
            'partial' => 'bg-warning',
            'cancelled' => 'bg-danger',
        ];

        return $config[$this->status] ?? 'bg-secondary';
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'partial' => 'Partially Paid',
            default => ucfirst($this->status),
        };
    }

    /**
     * Generate unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = now()->format('Y');
        $month = now()->format('m');
        
        $lastInvoice = self::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;
        
        return sprintf('%s-%s-%s-%04d', $prefix, $year, $month, $sequence);
    }

    /**
     * Log an action to the audit trail.
     */
    public function logAudit(string $action, ?array $oldValues = null, ?array $newValues = null, ?string $notes = null): void
    {
        InvoiceAuditLog::create([
            'invoice_id' => $this->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'notes' => $notes,
        ]);
    }

    /**
     * Cancel this invoice.
     */
    public function cancel(string $reason): bool
    {
        if ($this->status === 'cancelled') {
            return false;
        }

        $oldValues = $this->toArray();

        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'cancelled_by' => Auth::id(),
        ]);

        $this->logAudit('cancelled', $oldValues, $this->toArray(), $reason);

        return true;
    }

    /**
     * Record payment - Enhanced to create InvoicePayment record.
     */
    public function recordPayment(array $paymentData): void
    {
        if ($this->status === 'cancelled') {
            throw new \Exception('Cannot record payment for cancelled invoice.');
        }

        $oldPaidAmount = $this->paid_amount;
        $oldStatus = $this->status;

        // Create payment record
        $payment = $this->payments()->create($paymentData);

        // Update aggregate
        $this->paid_amount = $this->payments()->sum('amount');
        
        if ($this->paid_amount >= $this->total_amount) {
            $this->status = 'paid';
            $this->paid_amount = $this->total_amount;
        } elseif ($this->paid_amount > 0 && $this->status !== 'partial') {
            $this->status = 'partial';
        }

        $this->save();

        $this->logAudit('payment_recorded', 
            ['paid_amount' => $oldPaidAmount, 'status' => $oldStatus],
            ['paid_amount' => $this->paid_amount, 'status' => $this->status],
            $paymentData['notes'] ?? null
        );
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to get only cancelled invoices.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to get unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'partial']);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by customer.
     */
    public function scopeCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
