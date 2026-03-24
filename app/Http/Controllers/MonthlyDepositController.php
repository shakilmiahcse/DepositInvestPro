<?php

namespace App\Http\Controllers;

use App\Models\MonthlyDeposit;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyDepositController extends Controller {

    public function __construct() {
        date_default_timezone_set(get_option('timezone', 'Asia/Dhaka'));
    }

    public function index() {
        return view('backend.monthly_deposits.list');
    }

    public function get_table_data(Request $request, $account_id = null) {
        $deposits = MonthlyDeposit::with(['member', 'account.savings_type.currency'])->select('monthly_deposits.*');

        if ($account_id) {
            $deposits->where('account_id', $account_id);
        }

        return Datatables::eloquent($deposits)
            ->editColumn('member.first_name', function ($deposit) {
                return $deposit->member->first_name . ' ' . $deposit->member->last_name;
            })
            ->editColumn('account.account_number', function ($deposit) {
                return $deposit->account->account_number;
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
                $action = '';

                if ($deposit->status === 'pending') {
                    $action .= '<button class="btn btn-success btn-sm mark-paid" data-id="' . $deposit->id . '"><i class="ti-check"></i> ' . _lang('Mark Paid') . '</button>';
                }

                return $action;
            })
            ->rawColumns(['status', 'action'])
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

        return response()->json(['result' => 'success', 'message' => _lang('Marked as paid'), 'id' => $deposit->id]);
    }

    public function history($account_id) {
        $account = SavingsAccount::with(['member', 'savings_type.currency'])->withoutGlobalScopes(['status'])->findOrFail($account_id);
        return view('backend.monthly_deposits.history', compact('account'));
    }
}
