<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\ProfitDistribution;
use App\Models\ProfitDistributionDetail;
use App\Services\ProfitDistributionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Validator;

class ProfitDistributionController extends Controller
{
    protected ProfitDistributionService $profitDistributionService;

    public function __construct(ProfitDistributionService $profitDistributionService)
    {
        date_default_timezone_set(get_option('timezone', 'Asia/Dhaka'));
        $this->profitDistributionService = $profitDistributionService;
    }

    public function distributionHistory()
    {
        $investments = Investment::orderByDesc('start_date')->orderByDesc('id')->get();

        $distributionSummaries = ProfitDistribution::with('investment')
            ->withCount('details')
            ->latest('distribution_date')
            ->latest('id')
            ->get();

        $distributionDetails = ProfitDistributionDetail::with(['distribution.investment', 'member', 'account'])
            ->latest('id')
            ->get();

        $investmentProfitMap = $investments->mapWithKeys(function ($investment) {
            return [
                $investment->id => [
                    'net_profit'       => round((float) $investment->net_profit, 2),
                    'available_profit' => $this->profitDistributionService->getAvailableProfit($investment),
                    'distributed'      => ProfitDistribution::where('investment_id', $investment->id)->exists(),
                ],
            ];
        });

        $totalMemberDeposits = $this->profitDistributionService->getTotalMemberDeposits();

        return view('backend.profit_distribution.index', compact(
            'investments',
            'distributionSummaries',
            'distributionDetails',
            'investmentProfitMap',
            'totalMemberDeposits'
        ));
    }

    public function distributeProfit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'investment_id' => 'required|exists:investments,id',
            'total_profit'  => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return redirect()->route('profit_distributions.history')
                ->withErrors($validator)
                ->withInput();
        }

        $investment = Investment::findOrFail($request->input('investment_id'));

        try {
            $this->profitDistributionService->distribute($investment, (float) $request->input('total_profit'));
        } catch (ValidationException $exception) {
            return redirect()->route('profit_distributions.history')
                ->withErrors($exception->errors())
                ->withInput();
        }

        return redirect()->route('profit_distributions.history')
            ->with('success', _lang('Profit distributed successfully'));
    }
}
