<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

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

    public function getTotalProfits()
    {
        return (float) Transaction::whereNotNull('savings_account_id')
            ->where('dr_cr', 'cr')
            ->where('status', 2)
            ->where('type', 'Profit')
            ->sum('amount');
    }

    public function getTotalAccountWithdrawals()
    {
        return (float) Transaction::whereNotNull('savings_account_id')
            ->where('dr_cr', 'dr')
            ->where('status', 2)
            ->where('type', 'Withdraw')
            ->sum('amount');
    }

    public function getTotalExpenses()
    {
        return (float) DB::select("SELECT IFNULL(SUM(amount),0) as total_expense FROM expenses")[0]->total_expense;
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
        return (float) ($this->getTotalAccountDeposits() - $this->getTotalInvested($excludedInvestmentId)) - $this->getTotalAccountWithdrawals() - $this->getTotalExpenses() + $this->getTotalProfits();
    }

    public function hasSufficientFunds($amount, $excludedInvestmentId = null)
    {
        return (float) $amount <= $this->getAvailableBalance($excludedInvestmentId);
    }

    public function getFundSummary($excludedInvestmentId = null)
    {
        $totalAccountDeposits = $this->getTotalAccountDeposits() - $this->getTotalAccountWithdrawals() - $this->getTotalExpenses() + $this->getTotalProfits();
        $totalInvested        = $this->getTotalInvested($excludedInvestmentId);

        return [
            'total_account_deposits' => $totalAccountDeposits,
            'total_invested'         => $totalInvested,
            'available_balance'      => (float) ($totalAccountDeposits - $totalInvested),
        ];
    }
}
