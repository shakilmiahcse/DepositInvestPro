<?php

namespace App\Http\Controllers;

use App\Models\MonthlyDeposit;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use App\Notifications\DepositMoney;
use App\Notifications\MonthlyDepositReminder;
use App\Services\MonthlyDepositService;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyDepositController extends Controller {
    protected MonthlyDepositService $monthlyDepositService;

    public function __construct(MonthlyDepositService $monthlyDepositService) {
        $this->monthlyDepositService = $monthlyDepositService;
        date_default_timezone_set(get_option('timezone', 'Asia/Dhaka'));
    }

    public function index() {
        $currentMonthLabel = now()->format('F Y');
        $hasMissingDeposits = $this->monthlyDepositService->hasMissingForMonth(now());
        $availableYears = MonthlyDeposit::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($availableYears->isEmpty()) {
            $availableYears = collect([now()->year]);
        }

        return view('backend.monthly_deposits.list', compact('currentMonthLabel', 'hasMissingDeposits', 'availableYears'));
    }

    public function get_table_data(Request $request, $account_id = null) {
        $deposits = MonthlyDeposit::with(['member', 'account.savings_type.currency'])->select('monthly_deposits.*');

        if ($account_id) {
            $deposits->where('account_id', $account_id);
        }

        if ($request->filled('status') && in_array($request->status, ['pending', 'paid'])) {
            $deposits->where('status', $request->status);
        }

        if ($request->filled('month') && is_numeric($request->month)) {
            $deposits->where('month', (int) $request->month);
        }

        if ($request->filled('year') && is_numeric($request->year)) {
            $deposits->where('year', (int) $request->year);
        }

        $deposits->orderBy('year', 'desc')->orderBy('month', 'desc')->orderBy('id', 'desc');

        return Datatables::eloquent($deposits)
            ->editColumn('member.first_name', function ($deposit) {
                return '<a href="' . route('members.show', $deposit->member->id) . '">' . $deposit->member->first_name . ' ' . $deposit->member->last_name . '</a> ';
            })
            ->editColumn('account.account_number', function ($deposit) {
                return '<a href="' . route('savings_accounts.show', $deposit->account->id) . '">' . $deposit->account->account_number . '</a>';
            })
            ->editColumn('month', function ($deposit) {
                return date('F', mktime(0, 0, 0, $deposit->month, 1));
            })
            ->editColumn('status', function ($deposit) {
                if ($deposit->status === 'paid') {
                    return '<span class="badge badge-success">' . _lang('Paid') . '</span>';
                }
                return '<span class="badge badge-warning">' . _lang('Pending') . '</span>';
            })
            ->addColumn('action', function ($deposit) {
                $action = '<div class="d-flex justify-content-center flex-wrap">';

                if ($deposit->status === 'pending') {
                    $action .= '<button class="btn btn-warning btn-xs send-reminder mr-1 mb-1 px-2 py-1" data-id="' . $deposit->id . '"><i class="ti-bell"></i> ' . _lang('Send Reminder') . '</button>';
                    $action .= '<button class="btn btn-success btn-xs mark-paid mr-1 mb-1 px-2 py-1" data-id="' . $deposit->id . '"><i class="ti-check"></i> ' . _lang('Mark Paid') . '</button>';
                }

                if ($deposit->transaction_id) {
                    $action .= '<a class="btn btn-outline-primary btn-xs mb-1 px-2 py-1" href="' . route('transactions.show', $deposit->transaction_id) . '" target="_blank"><i class="ti-eye"></i> ' . _lang('Details') . '</a>';
                }

                return $action . '</div>';
            })
            ->rawColumns(['status', 'action', 'member.first_name', 'account.account_number'])
            ->make(true);
    }

    public function mark_paid(Request $request, $id) {
        $deposit = MonthlyDeposit::findOrFail($id);

        if ($deposit->status === 'paid') {
            return response()->json(['result' => 'success', 'message' => _lang('Already marked as paid')]);
        }

        DB::beginTransaction();

        // Create transaction for the deposit
        $transaction                     = new Transaction();
        $transaction->trans_date         = now();
        $transaction->member_id          = $deposit->member_id;
        $transaction->savings_account_id = $deposit->account_id;
        $transaction->amount             = $deposit->amount;
        $transaction->dr_cr              = 'cr';
        $transaction->type               = 'Deposit';
        $transaction->method             = _lang('Monthly Deposit');
        $transaction->status             = 2;
        $transaction->description        = _lang('Monthly Deposit for') . ' ' . date('F Y', mktime(0, 0, 0, $deposit->month, 1, $deposit->year));
        $transaction->created_user_id    = auth()->id();
        $transaction->branch_id          = auth()->user()->branch_id;
        $transaction->save();

        $deposit->status         = 'paid';
        $deposit->paid_date      = now();
        $deposit->transaction_id = $transaction->id;
        $deposit->save();

        DB::commit();

        try {
            $transaction->loadMissing(['member', 'account.savings_type.currency']);
            $transaction->member->notify(new DepositMoney($transaction));
        } catch (\Exception $e) {}

        return response()->json(['result' => 'success', 'message' => _lang('Marked as paid'), 'id' => $deposit->id]);
    }

    public function remind($id) {
        $deposit = MonthlyDeposit::with(['member', 'account.savings_type.currency'])->findOrFail($id);

        if ($deposit->status !== 'pending') {
            return response()->json(['result' => 'error', 'message' => _lang('Reminder can only be sent for pending deposits')], 422);
        }

        try {
            $deposit->member->notify(new MonthlyDepositReminder($deposit));
        } catch (\Exception $e) {
            return response()->json(['result' => 'error', 'message' => _lang('Failed to send reminder')], 500);
        }

        return response()->json(['result' => 'success', 'message' => _lang('Reminder sent successfully')]);
    }

    public function history($account_id) {
        $account = SavingsAccount::with(['member', 'savings_type.currency'])->withoutGlobalScopes(['status'])->findOrFail($account_id);
        $availableYears = MonthlyDeposit::where('account_id', $account_id)
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($availableYears->isEmpty()) {
            $availableYears = collect([now()->year]);
        }

        return view('backend.monthly_deposits.history', compact('account', 'availableYears'));
    }

    public function generate() {
        $created = $this->monthlyDepositService->generateForMonth(now());

        if ($created > 0) {
            return redirect()->route('monthly_deposits.index')->with('success', _lang('Monthly deposits generated successfully') . ': ' . $created);
        }

        return redirect()->route('monthly_deposits.index')->with('success', _lang('Monthly deposits already generated for this month'));
    }
}
