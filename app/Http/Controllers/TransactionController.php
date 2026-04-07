<?php
namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use App\Notifications\DepositMoney;
use App\Notifications\TransferMoney;
use App\Notifications\WithdrawMoney;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class TransactionController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        date_default_timezone_set(get_option('timezone', 'Asia/Dhaka'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.transaction.list', [
            'request_type' => request('type'),
        ]);
    }

    public function transfer_requests()
    {
        return view('backend.transaction.list', [
            'request_type' => 'transfer_requests',
        ]);
    }

    public function get_table_data()
    {

        $transactions = Transaction::select('transactions.*')
            ->with(['member', 'account', 'account.savings_type'])
            ->orderBy("transactions.trans_date", "desc");

        if (request('type') == 'transfer_requests') {
            $transactions->where('transactions.type', 'Transfer')
                ->where('transactions.dr_cr', 'dr')
                ->whereNull('transactions.parent_id')
                ->where('transactions.status', 0);
        }

        return Datatables::eloquent($transactions)
            ->editColumn('member.first_name', function ($transactions) {
                return $transactions->member->first_name . ' ' . $transactions->member->last_name;
            })
            ->editColumn('dr_cr', function ($transactions) {
                return strtoupper($transactions->dr_cr);
            })
            ->editColumn('status', function ($transactions) {
                return transaction_status($transactions->status);
            })
            ->editColumn('amount', function ($transaction) {
                $symbol = $transaction->dr_cr == 'dr' ? '-' : '+';
                $class  = $transaction->dr_cr == 'dr' ? 'text-danger' : 'text-success';
                return '<span class="' . $class . '">' . $symbol . ' ' . decimalPlace($transaction->amount, currency($transaction->account->savings_type->currency->name)) . '</span>';
            })
            ->editColumn('type', function ($transaction) {
                return ucwords(str_replace('_', ' ', $transaction->type));
            })
            ->filterColumn('member.first_name', function ($query, $keyword) {
                $query->whereHas('member', function ($query) use ($keyword) {
                    return $query->where("first_name", "like", "{$keyword}%")
                        ->orWhere("last_name", "like", "{$keyword}%");
                });
            }, true)
            ->addColumn('action', function ($transaction) {
                $actions = '';

                if ($transaction->type == 'Transfer' && $transaction->dr_cr == 'dr' && $transaction->parent_id == null && $transaction->status == 0) {
                    $actions .= '<a class="dropdown-item" href="' . route('transactions.approve_transfer', $transaction['id']) . '"><i class="ti-check-box"></i> ' . _lang('Approve') . '</a>';
                    $actions .= '<a class="dropdown-item" href="' . route('transactions.reject_transfer', $transaction['id']) . '"><i class="ti-close"></i> ' . _lang('Reject') . '</a>';
                }

                $actions .= '<a class="dropdown-item" href="' . route('transactions.edit', $transaction['id']) . '"><i class="ti-pencil-alt"></i> ' . _lang('Edit') . '</a>'
                . '<a class="dropdown-item" href="' . route('transactions.show', $transaction['id']) . '"><i class="ti-eye"></i>  ' . _lang('Details') . '</a>'
                . '<a class="dropdown-item" href="' . route('transactions.show', $transaction['id']) . '?print=general" target="_blank"><i class="fas fa-print"></i>  ' . _lang('Regular Print') . '</a>'
                . '<a class="dropdown-item" href="' . route('transactions.show', $transaction['id']) . '?print=pos" target="_blank"><i class="fas fa-print"></i>  ' . _lang('POS Receipt') . '</a>'
                . '<form action="' . route('transactions.destroy', $transaction['id']) . '" method="post">'
                . csrf_field()
                . '<input name="_method" type="hidden" value="DELETE">'
                . '<button class="dropdown-item btn-remove" type="submit"><i class="ti-trash"></i> ' . _lang('Delete') . '</button>'
                    . '</form>';

                return '<div class="dropdown text-center">'
                . '<button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">' . _lang('Action')
                . '&nbsp;</button>'
                . '<div class="dropdown-menu">'
                . $actions
                    . '</div>'
                    . '</div>';
            })
            ->setRowId(function ($transaction) {
                return "row_" . $transaction->id;
            })
            ->rawColumns(['action', 'status', 'amount'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (! $request->ajax()) {
            return view('backend.transaction.create');
        } else {
            return view('backend.transaction.modal.create');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trans_date'         => 'required',
            'member_id'          => 'required',
            'savings_account_id' => 'required',
            'amount'             => 'required|numeric',
            'dr_cr'              => 'required|in:dr,cr',
            'type'               => 'required',
            'status'             => 'required',
            'description'        => 'required',
        ], [
            'dr_cr.in' => 'Transaction must have a debit or credit',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $accountType = SavingsAccount::find($request->savings_account_id)->savings_type;

        if (! $accountType) {
            return back()
                ->with('error', _lang('Account type not found'))
                ->withInput();
        }

        if ($request->dr_cr == 'dr') {
            if ($accountType->allow_withdraw == 0) {
                return back()
                    ->with('error', _lang('Withdraw is not allowed for') . ' ' . $accountType->name)
                    ->withInput();
            }

            $account_balance = get_account_balance($request->savings_account_id, $request->member_id);
            if (($account_balance - $request->amount) < $accountType->minimum_account_balance) {
                return back()
                    ->with('error', _lang('Sorry Minimum account balance will be exceeded'))
                    ->withInput();
            }

            if ($account_balance < $request->amount) {
                return back()
                    ->with('error', _lang('Insufficient account balance'))
                    ->withInput();
            }

        } else {
            if ($request->amount < $accountType->minimum_deposit_amount) {
                return back()
                    ->with('error', _lang('You must deposit minimum') . ' ' . $accountType->minimum_deposit_amount . ' ' . $accountType->currency->name)
                    ->withInput();
            }
        }

        $transaction                     = new Transaction();
        $transaction->trans_date         = $request->input('trans_date');
        $transaction->member_id          = $request->input('member_id');
        $transaction->savings_account_id = $request->input('savings_account_id');
        $transaction->amount             = $request->input('amount');
        $transaction->dr_cr              = $request->dr_cr == 'dr' ? 'dr' : 'cr';
        $transaction->type               = ucwords($request->type);
        $transaction->method             = 'Manual';
        $transaction->status             = $request->input('status');
        $transaction->description        = $request->input('description');
        $transaction->created_user_id    = auth()->id();

        $transaction->save();

        if ($transaction->dr_cr == 'dr') {
            try {
                $transaction->member->notify(new WithdrawMoney($transaction));
            } catch (\Exception $e) {}
        } else if ($transaction->dr_cr == 'cr') {
            try {
                $transaction->member->notify(new DepositMoney($transaction));
            } catch (\Exception $e) {}
        }

        if (! $request->ajax()) {
            return redirect()->route('transactions.show', $transaction->id)->with('success', _lang('The transaction has been created successfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('The transaction has been created successfully'), 'data' => $transaction, 'table' => '#transactions_table']);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $alert_col   = 'col-lg-8 offset-lg-2';
        $transaction = Transaction::find($id);
        if (! $request->ajax()) {
            return view('backend.transaction.view', compact('transaction', 'id', 'alert_col'));
        } else {
            return view('backend.transaction.modal.view', compact('transaction', 'id'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $transaction = Transaction::find($id);
        if (! $request->ajax()) {
            return view('backend.transaction.edit', compact('transaction', 'id'));
        } else {
            return view('backend.transaction.modal.edit', compact('transaction', 'id'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'trans_date'         => 'required',
            'member_id'          => 'required',
            'savings_account_id' => 'required',
            'amount'             => 'required|numeric',
            'status'             => 'required',
            'description'        => 'required',
        ], [
            'dr_cr.in' => 'Transaction must have a debit or credit',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('transactions.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $transaction = Transaction::find($id);
        $oldStatus    = $transaction->status;

        $accountType = SavingsAccount::find($request->savings_account_id)->savings_type;

        if (! $accountType) {
            return back()
                ->with('error', _lang('Account type not found'))
                ->withInput();
        }

        if ($request->dr_cr == 'dr') {
            if ($accountType->allow_withdraw == 0) {
                return back()
                    ->with('error', _lang('Withdraw is not allowed for') . ' ' . $accountType->name)
                    ->withInput();
            }

            $account_balance = get_account_balance($request->savings_account_id, $request->member_id);
            $previousAmount  = $request->member_id == $transaction->member_id ? $transaction->amount : 0;

            if ((($account_balance + $previousAmount) - $request->amount) < $accountType->minimum_account_balance) {
                return back()
                    ->with('error', _lang('Sorry Minimum account balance will be exceeded'))
                    ->withInput();
            }

            if (($account_balance + $previousAmount) < $request->amount) {
                return back()
                    ->with('error', _lang('Insufficient account balance'))
                    ->withInput();
            }
        } else {
            if ($request->amount < $accountType->minimum_deposit_amount) {
                return back()
                    ->with('error', _lang('You must deposit minimum') . ' ' . $accountType->minimum_deposit_amount . ' ' . $accountType->currency->name)
                    ->withInput();
            }
        }

        $transaction->trans_date         = $request->input('trans_date');
        $transaction->member_id          = $request->input('member_id');
        $transaction->savings_account_id = $request->input('savings_account_id');
        $transaction->amount             = $request->input('amount');
        $transaction->status             = $request->input('status');
        $transaction->description        = $request->input('description');
        $transaction->updated_user_id    = auth()->id();
        $transaction->save();

        if ($transaction->type == 'Transfer' && $transaction->dr_cr == 'dr' && $transaction->parent_id == null && $oldStatus != $transaction->status) {
            $childTransactions = Transaction::where('parent_id', $transaction->id)->get();

            foreach ($childTransactions as $childTransaction) {
                $childTransaction->status          = $transaction->status;
                $childTransaction->updated_user_id = auth()->id();
                $childTransaction->save();
            }

            if ($transaction->status == 2) {
                $creditTransaction = $childTransactions->firstWhere('dr_cr', 'cr');

                if ($creditTransaction) {
                    try {
                        $creditTransaction->member->notify(new TransferMoney($creditTransaction));
                    } catch (\Throwable $e) {}
                }
            }
        }

        if (! $request->ajax()) {
            return redirect()->route('transactions.index')->with('success', _lang('Updated Successfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated Successfully'), 'data' => $transaction, 'table' => '#transactions_table']);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        $transaction = Transaction::find($id);

        if ($transaction->loan_id != null) {
            $loan = Loan::find($transaction->loan_id);
            if ($loan->status == 1 || $loan->status == 2) {
                return back()->with('error', _lang('Sorry, this transaction is associated with a loan !'));
            }
        }

        $transaction->delete();

        DB::commit();

        return redirect()->route('transactions.index')->with('success', _lang('Deleted Successfully'));
    }

    public function approve_transfer($id)
    {
        DB::beginTransaction();

        $transaction = Transaction::where('type', 'Transfer')
            ->where('dr_cr', 'dr')
            ->whereNull('parent_id')
            ->findOrFail($id);

        $transaction->status          = 2;
        $transaction->updated_user_id = auth()->id();
        $transaction->save();

        $childTransactions = Transaction::where('parent_id', $transaction->id)->get();

        foreach ($childTransactions as $childTransaction) {
            $childTransaction->status          = 2;
            $childTransaction->updated_user_id = auth()->id();
            $childTransaction->save();
        }

        $creditTransaction = $childTransactions->firstWhere('dr_cr', 'cr');

        if ($creditTransaction) {
            try {
                $creditTransaction->member->notify(new TransferMoney($creditTransaction));
            } catch (\Throwable $e) {}
        }

        DB::commit();

        return redirect()->route('transactions.index')->with('success', _lang('Transfer Request Approved'));
    }

    public function reject_transfer($id)
    {
        DB::beginTransaction();

        $transaction = Transaction::where('type', 'Transfer')
            ->where('dr_cr', 'dr')
            ->whereNull('parent_id')
            ->findOrFail($id);

        $transaction->status          = 1;
        $transaction->updated_user_id = auth()->id();
        $transaction->save();

        $childTransactions = Transaction::where('parent_id', $transaction->id)->get();

        foreach ($childTransactions as $childTransaction) {
            $childTransaction->status          = 1;
            $childTransaction->updated_user_id = auth()->id();
            $childTransaction->save();
        }

        DB::commit();

        return redirect()->route('transactions.index')->with('success', _lang('Transfer Request Rejected'));
    }
}
