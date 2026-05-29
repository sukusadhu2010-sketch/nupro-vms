<?php

namespace App\Services;

use App\Models\Ledger;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LedgerService
{
    /**
     * Get or create ledger account, map payment_mode to account.
     */
    public function getLedgerAccount(string $paymentMode, string $accountName = null): Ledger
    {
        $accountMap = [
            'cash' => 'cash',
            'fund_transfer' => $accountName ?? 'bank_main',
            'cheque' => $accountName ?? 'bank_main',
            'upi' => $accountName ?? 'bank_main',
        ];

        $accountName = $accountMap[$paymentMode] ?? 'cash';
        
        return Ledger::firstOrCreate(
            ['account_name' => $accountName],
            ['account_type' => $this->getAccountType($accountName), 'balance' => 0]
        );
    }

    /**
     * Record payment and update ledger balance.
     */
    public function recordPayment(Payment $payment, $isIncome = true)
    {
        DB::transaction(function () use ($payment, $isIncome) {
            $payment->save();
            
            $ledger = $payment->ledgerAccount;
            $delta = $isIncome ? $payment->amount : -$payment->amount;
            
            $ledger->updateBalance($delta, $payment->created_by);
        });
    }

    protected function getAccountType(string $accountName): string
    {
        return match (true) {
            str_contains($accountName, 'bank') => 'bank',
            str_contains($accountName, 'post') => 'post_office',
            default => 'cash',
        };
    }

    /**
     * Get ledger summary.
     */
    public function getSummary()
    {
        return Ledger::select('account_name', 'account_type', 'balance')
            ->orderBy('account_type')
            ->orderBy('balance', 'desc')
            ->get();
    }
}
