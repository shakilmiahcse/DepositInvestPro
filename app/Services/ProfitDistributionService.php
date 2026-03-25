<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\ProfitDistribution;
use App\Models\ProfitDistributionDetail;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfitDistributionService
{
    public function getAvailableProfit(Investment $investment): float
    {
        $distributedAmount = (float) ProfitDistribution::where('investment_id', $investment->id)->sum('distributed_amount');

        return max(0, round((float) $investment->net_profit - $distributedAmount, 2));
    }

    public function getEligibleAccounts(): Collection
    {
        return SavingsAccount::select('savings_accounts.*')
            ->selectRaw("((SELECT IFNULL(SUM(amount),0) FROM transactions WHERE dr_cr = 'cr' AND status = 2 AND savings_account_id = savings_accounts.id) - (SELECT IFNULL(SUM(amount),0) FROM transactions WHERE dr_cr = 'dr' AND status != 1 AND savings_account_id = savings_accounts.id)) as balance")
            ->with(['member', 'savings_type.currency'])
            ->get()
            ->map(function ($account) {
                $account->balance = round((float) $account->balance, 2);

                return $account;
            })
            ->filter(function ($account) {
                return $account->balance > 0;
            })
            ->values();
    }

    public function getTotalMemberDeposits(): float
    {
        return round((float) $this->getEligibleAccounts()->sum('balance'), 2);
    }

    public function prepareAllocation(Investment $investment, float $totalProfit): array
    {
        $this->validateDistribution($investment, $totalProfit);

        $accounts      = $this->getEligibleAccounts();
        $totalDeposits = round((float) $accounts->sum('balance'), 2);
        $remaining     = round($totalProfit, 2);
        $details       = [];

        foreach ($accounts->values() as $index => $account) {
            $isLastAccount = $index === $accounts->count() - 1;

            if ($isLastAccount) {
                $profitAmount = $remaining;
            } else {
                $profitAmount = round(($account->balance / $totalDeposits) * $totalProfit, 2);
                $profitAmount = min($profitAmount, $remaining);
            }

            $remaining = round($remaining - $profitAmount, 2);

            if ($profitAmount <= 0) {
                continue;
            }

            $details[] = [
                'member_id'      => $account->member_id,
                'account_id'     => $account->id,
                'deposit_amount' => round($account->balance, 2),
                'profit_amount'  => round($profitAmount, 2),
                'branch_id'      => optional($account->member)->branch_id,
            ];
        }

        $distributedAmount = round(collect($details)->sum('profit_amount'), 2);

        return [
            'accounts'           => $accounts,
            'details'            => $details,
            'total_deposits'     => $totalDeposits,
            'distributed_amount' => $distributedAmount,
            'remaining_profit'   => round($totalProfit - $distributedAmount, 2),
            'available_profit'   => $this->getAvailableProfit($investment),
        ];
    }

    public function distribute(Investment $investment, float $totalProfit): ProfitDistribution
    {
        $allocation = $this->prepareAllocation($investment, $totalProfit);

        return DB::transaction(function () use ($investment, $totalProfit, $allocation) {
            $distribution = ProfitDistribution::create([
                'investment_id'      => $investment->id,
                'total_profit'       => round($totalProfit, 2),
                'distributed_amount' => $allocation['distributed_amount'],
                'remaining_profit'   => $allocation['remaining_profit'],
                'distribution_date'  => now(),
            ]);

            foreach ($allocation['details'] as $detail) {
                ProfitDistributionDetail::create([
                    'profit_distribution_id' => $distribution->id,
                    'member_id'              => $detail['member_id'],
                    'account_id'             => $detail['account_id'],
                    'deposit_amount'         => $detail['deposit_amount'],
                    'profit_amount'          => $detail['profit_amount'],
                ]);

                $transaction                     = new Transaction();
                $transaction->trans_date         = now();
                $transaction->member_id          = $detail['member_id'];
                $transaction->savings_account_id = $detail['account_id'];
                $transaction->amount             = $detail['profit_amount'];
                $transaction->gateway_amount     = 0;
                $transaction->dr_cr              = 'cr';
                $transaction->type               = 'Profit';
                $transaction->method             = 'Investment';
                $transaction->status             = 2;
                $transaction->description        = _lang('Profit distribution for') . ' ' . $investment->name;
                $transaction->ref_id             = $distribution->id;
                $transaction->created_user_id    = auth()->id();
                $transaction->branch_id          = $detail['branch_id'] ?: optional(auth()->user())->branch_id;
                $transaction->save();
            }

            return $distribution->load(['investment', 'details.member', 'details.account']);
        });
    }

    protected function validateDistribution(Investment $investment, float $totalProfit): void
    {
        if (ProfitDistribution::where('investment_id', $investment->id)->exists()) {
            throw ValidationException::withMessages([
                'investment_id' => _lang('Profit has already been distributed for this investment'),
            ]);
        }

        $availableProfit = $this->getAvailableProfit($investment);

        if ($totalProfit <= 0) {
            throw ValidationException::withMessages([
                'total_profit' => _lang('Profit amount must be greater than zero'),
            ]);
        }

        if ($availableProfit <= 0) {
            throw ValidationException::withMessages([
                'investment_id' => _lang('No available investment profit found for distribution'),
            ]);
        }

        if ($totalProfit > $availableProfit) {
            throw ValidationException::withMessages([
                'total_profit' => _lang('Profit amount exceeds available investment profit'),
            ]);
        }

        if ($this->getTotalMemberDeposits() <= 0) {
            throw ValidationException::withMessages([
                'investment_id' => _lang('No savings balance found for profit distribution'),
            ]);
        }
    }
}
