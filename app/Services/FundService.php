<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentTransaction;
use App\Models\Transaction;

class FundService
{
    public function getTotalAccountDeposits()
    {
        return (float) Transaction::whereNotNull('savings_account_id')
            ->where('dr_cr', 'cr')
            ->where('status', 2)
            ->where('type', 'Deposit')
            ->sum('amount');
    }

    public function getTotalBaseInvested($excludedInvestmentId = null)
    {
        return (float) Investment::query()
            ->when($excludedInvestmentId, function ($query, $excludedInvestmentId) {
                $query->where('id', '!=', $excludedInvestmentId);
            })
            ->sum('invested_amount');
    }

    public function getTotalTransactionInvested($excludedInvestmentId = null)
    {
        return (float) InvestmentTransaction::query()
            ->where('type', 'invest')
            ->when($excludedInvestmentId, function ($query, $excludedInvestmentId) {
                $query->whereHas('investment', function ($investmentQuery) use ($excludedInvestmentId) {
                    $investmentQuery->where('id', '!=', $excludedInvestmentId);
                });
            })
            ->sum('amount');
    }

    public function getTotalInvested($excludedInvestmentId = null)
    {
        return $this->getTotalBaseInvested($excludedInvestmentId) + $this->getTotalTransactionInvested($excludedInvestmentId);
    }

    public function getAvailableBalance($excludedInvestmentId = null)
    {
        return (float) ($this->getTotalAccountDeposits() - $this->getTotalInvested($excludedInvestmentId));
    }

    public function hasSufficientFunds($amount, $excludedInvestmentId = null)
    {
        return (float) $amount <= $this->getAvailableBalance($excludedInvestmentId);
    }

    public function getFundSummary($excludedInvestmentId = null)
    {
        $totalAccountDeposits = $this->getTotalAccountDeposits();
        $totalInvested        = $this->getTotalInvested($excludedInvestmentId);

        return [
            'total_account_deposits' => $totalAccountDeposits,
            'total_invested'         => $totalInvested,
            'available_balance'      => (float) ($totalAccountDeposits - $totalInvested),
        ];
    }
}
