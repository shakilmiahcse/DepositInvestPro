<?php
namespace App\Http\Controllers;

use App\Models\SavingsProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SavingsProductController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        date_default_timezone_set(get_option('timezone', 'Asia/Dhaka'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $savingsproducts = SavingsProduct::all()->sortByDesc("id");
        return view('backend.savings_product.list', compact('savingsproducts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        if (! $request->ajax()) {
            return back();
        } else {
            return view('backend.savings_product.modal.create');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name'                           => 'required',
            'account_number_prefix'          => 'nullable|max:10',
            'starting_account_number'        => 'required|integer',
            'currency_id'                    => 'required',
            'allow_withdraw'                 => 'required',
            'minimum_account_balance'        => 'required|numeric',
            'minimum_deposit_amount'         => 'required|numeric',
            'monthly_deposit_amount'         => 'required|numeric',
            'maintenance_fee'                => 'nullable|numeric',
            'maintenance_fee_posting_period' => '',
            'status'                         => 'required',
            'auto_create'                    => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('savings_products.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $savingsproduct                                 = new SavingsProduct();
        $savingsproduct->name                           = $request->input('name');
        $savingsproduct->account_number_prefix          = $request->input('account_number_prefix');
        $savingsproduct->starting_account_number        = $request->input('starting_account_number');
        $savingsproduct->currency_id                    = $request->input('currency_id');
        $savingsproduct->interest_rate                  = null;
        $savingsproduct->interest_method                = null;
        $savingsproduct->interest_period                = null;
        $savingsproduct->min_bal_interest_rate          = null;
        $savingsproduct->allow_withdraw                 = $request->input('allow_withdraw');
        $savingsproduct->minimum_account_balance        = $request->input('minimum_account_balance');
        $savingsproduct->minimum_deposit_amount         = $request->minimum_deposit_amount;
        $savingsproduct->monthly_deposit_amount         = $request->input('monthly_deposit_amount', 0);
        $savingsproduct->maintenance_fee                = $request->input('maintenance_fee');
        $savingsproduct->maintenance_fee_posting_period = $request->input('maintenance_fee_posting_period');
        $savingsproduct->status                         = $request->input('status');
        $savingsproduct->auto_create                    = $request->input('auto_create');

        $savingsproduct->save();

        //Prefix Output
        $savingsproduct->name            = $savingsproduct->name . ' - ' . $savingsproduct->currency->name;
        $savingsproduct->interest_rate   = $savingsproduct->interest_rate != NULL ? $savingsproduct->interest_rate . ' %' : '0 %';
        $savingsproduct->interest_method = _lang('Daily Outstanding Balance');
        $savingsproduct->interest_period = $savingsproduct->interest_period != NULL ? _lang('Every') . ' ' . $savingsproduct->interest_period . ' ' . _lang('month') : '';
        $savingsproduct->status          = status($savingsproduct->status);

        if (! $request->ajax()) {
            return redirect()->route('savings_products.create')->with('success', _lang('Saved Successfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Successfully'), 'data' => $savingsproduct, 'table' => '#savings_products_table']);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        $savingsproduct = SavingsProduct::find($id);
        if (! $request->ajax()) {
            return back();
        } else {
            return view('backend.savings_product.modal.view', compact('savingsproduct', 'id'));
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id) {
        $savingsproduct = SavingsProduct::find($id);
        if (! $request->ajax()) {
            return back();
        } else {
            return view('backend.savings_product.modal.edit', compact('savingsproduct', 'id'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'name'                           => 'required',
            'account_number_prefix'          => 'nullable|max:10',
            'starting_account_number'        => 'required|integer',
            'currency_id'                    => 'required',
            'allow_withdraw'                 => 'required',
            'minimum_account_balance'        => 'required|numeric',
            'minimum_deposit_amount'         => 'required|numeric',
            'monthly_deposit_amount'         => 'required|numeric',
            'maintenance_fee'                => 'nullable|numeric',
            'maintenance_fee_posting_period' => '',
            'status'                         => 'required',
            'auto_create'                    => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('savings_products.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $savingsproduct                                 = SavingsProduct::find($id);
        $savingsproduct->name                           = $request->input('name');
        $savingsproduct->account_number_prefix          = $request->input('account_number_prefix');
        $savingsproduct->starting_account_number        = $request->input('starting_account_number');
        $savingsproduct->currency_id                    = $request->input('currency_id');
        $savingsproduct->interest_rate                  = null;
        $savingsproduct->interest_method                = null;
        $savingsproduct->interest_period                = null;
        $savingsproduct->min_bal_interest_rate          = null;
        $savingsproduct->allow_withdraw                 = $request->input('allow_withdraw');
        $savingsproduct->minimum_account_balance        = $request->input('minimum_account_balance');
        $savingsproduct->minimum_deposit_amount         = $request->minimum_deposit_amount;
        $savingsproduct->monthly_deposit_amount         = $request->input('monthly_deposit_amount', 0);
        $savingsproduct->maintenance_fee                = $request->input('maintenance_fee');
        $savingsproduct->maintenance_fee_posting_period = $request->input('maintenance_fee_posting_period');
        $savingsproduct->status                         = $request->input('status');
        $savingsproduct->auto_create                    = $request->input('auto_create');

        $savingsproduct->save();

        //Prefix Output
        $savingsproduct->name            = $savingsproduct->name . ' - ' . $savingsproduct->currency->name;
        $savingsproduct->interest_rate   = $savingsproduct->interest_rate != NULL ? $savingsproduct->interest_rate . ' %' : '0 %';
        $savingsproduct->interest_method = _lang('Daily Outstanding Balance');
        $savingsproduct->interest_period = $savingsproduct->interest_period != NULL ? _lang('Every') . ' ' . $savingsproduct->interest_period . ' ' . _lang('month') : '';
        $savingsproduct->status          = status($savingsproduct->status);

        if (! $request->ajax()) {
            return redirect()->route('savings_products.index')->with('success', _lang('Updated Successfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated Successfully'), 'data' => $savingsproduct, 'table' => '#savings_products_table']);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $savingsproduct = SavingsProduct::find($id);
        $savingsproduct->delete();
        return redirect()->route('savings_products.index')->with('success', _lang('Deleted Successfully'));
    }
}
