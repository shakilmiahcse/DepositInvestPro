<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use Illuminate\Http\Request;
use Validator;

class InvestmentController extends Controller
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
        $investments = Investment::orderByDesc('start_date')->orderByDesc('id')->get();

        return view('backend.investment.list', compact('investments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (! $request->ajax()) {
            return back();
        }

        return view('backend.investment.modal.create');
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
            'name'            => 'required|max:191',
            'description'     => 'nullable',
            'invested_amount' => 'required|numeric|min:0',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'expected_return' => 'nullable|numeric|min:0',
            'status'          => 'required|in:active,completed',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            }

            return redirect()->route('investments.create')
                ->withErrors($validator)
                ->withInput();
        }

        $investment                  = new Investment();
        $investment->name            = $request->input('name');
        $investment->description     = $request->input('description');
        $investment->invested_amount = $request->input('invested_amount');
        $investment->start_date      = $request->input('start_date');
        $investment->end_date        = $request->input('end_date');
        $investment->expected_return = $request->input('expected_return');
        $investment->status          = $request->input('status');
        $investment->save();

        if (! $request->ajax()) {
            return redirect()->route('investments.index')->with('success', _lang('Saved Successfully'));
        }

        return response()->json([
            'result'  => 'success',
            'action'  => 'store',
            'message' => _lang('Saved Successfully'),
            'data'    => $this->formatInvestmentForTable($investment),
            'table'   => '#investments_table',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        if (! $request->ajax()) {
            return back();
        }

        return view('backend.investment.modal.view', compact('investment', 'id'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $investment = Investment::findOrFail($id);

        if (! $request->ajax()) {
            return back();
        }

        return view('backend.investment.modal.edit', compact('investment', 'id'));
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
            'name'            => 'required|max:191',
            'description'     => 'nullable',
            'invested_amount' => 'required|numeric|min:0',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'expected_return' => 'nullable|numeric|min:0',
            'status'          => 'required|in:active,completed',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            }

            return redirect()->route('investments.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }

        $investment                  = Investment::findOrFail($id);
        $investment->name            = $request->input('name');
        $investment->description     = $request->input('description');
        $investment->invested_amount = $request->input('invested_amount');
        $investment->start_date      = $request->input('start_date');
        $investment->end_date        = $request->input('end_date');
        $investment->expected_return = $request->input('expected_return');
        $investment->status          = $request->input('status');
        $investment->save();

        if (! $request->ajax()) {
            return redirect()->route('investments.index')->with('success', _lang('Updated Successfully'));
        }

        return response()->json([
            'result'  => 'success',
            'action'  => 'update',
            'message' => _lang('Updated Successfully'),
            'data'    => $this->formatInvestmentForTable($investment),
            'table'   => '#investments_table',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $investment = Investment::findOrFail($id);
        $investment->delete();

        return redirect()->route('investments.index')->with('success', _lang('Deleted Successfully'));
    }

    /**
     * Prepare values used by the ajax table response.
     *
     * @param  \App\Models\Investment  $investment
     * @return array
     */
    private function formatInvestmentForTable($investment)
    {
        return [
            'id'              => $investment->id,
            'name'            => $investment->name,
            'invested_amount' => decimalPlace($investment->invested_amount, currency()),
            'start_date'      => $investment->start_date->format('Y-m-d'),
            'end_date'        => optional($investment->end_date)->format('Y-m-d') ?? _lang('Ongoing'),
            'expected_return' => $investment->expected_return !== null ? decimalPlace($investment->expected_return, currency()) : _lang('N/A'),
            'status'          => $investment->status === 'active'
                ? xss_clean(show_status(_lang('Active'), 'success'))
                : xss_clean(show_status(_lang('Completed'), 'info')),
        ];
    }
}
