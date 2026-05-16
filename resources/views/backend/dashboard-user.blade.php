@extends('layouts.app')

@section('content')
@php $permissions = permission_list(); @endphp
<div class="row">
	@if (in_array('dashboard.total_customer_widget', $permissions))
	<div class="col-xl-3 col-md-6 col-sm-12">
		<div class="card mb-4 primary-card dashboard-card">
			<div class="card-body">
				<div class="d-flex align-items-start justify-content-between">
					<div class="flex-grow-1">
						<h5 class="mb-2">{{ _lang('Total Members') }}</h5>
						<h4 class="mb-3"><b>{{ $total_customer }}</b></h4>
						<a href="{{ route('members.index') }}" class="btn btn-sm btn-light"><i class="ti-arrow-right"></i>{{ _lang('View') }}</a>
					</div>
					<div class="card-icon text-right"><i class="ti-user"></i></div>
				</div>
			</div>
		</div>
	</div>
	@endif

	@if (in_array('dashboard.deposit_requests_widget',$permissions))
	<div class="col-xl-3 col-md-6 col-sm-12">
		<div class="card mb-4 success-card dashboard-card">
			<div class="card-body">
				<div class="d-flex align-items-start justify-content-between">
					<div class="flex-grow-1">
						<h5 class="mb-2">{{ _lang('Deposit Requests') }}</h5>
						<h4 class="mb-3"><b>{{ request_count('deposit_requests') }}</b></h4>
						<a href="{{ route('deposit_requests.index') }}" class="btn btn-sm btn-light"><i class="ti-arrow-right"></i>{{ _lang('View') }}</a>
					</div>
					<div class="card-icon text-right"><i class="ti-import"></i></div>
				</div>
			</div>
		</div>
	</div>
	@endif

	@if (in_array('dashboard.withdraw_requests_widget',$permissions))
	<div class="col-xl-3 col-md-6 col-sm-12">
		<div class="card mb-4 warning-card dashboard-card">
			<div class="card-body">
				<div class="d-flex align-items-start justify-content-between">
					<div class="flex-grow-1">
						<h5 class="mb-2">{{ _lang('Withdraw Requests') }}</h5>
						<h4 class="mb-3"><b>{{ request_count('withdraw_requests') }}</b></h4>
						<a href="{{ route('withdraw_requests.index') }}" class="btn btn-sm btn-light"><i class="ti-arrow-right"></i>{{ _lang('View') }}</a>
					</div>
					<div class="card-icon text-right"><i class="ti-export"></i></div>
				</div>
			</div>
		</div>
	</div>
	@endif

	@if (in_array('dashboard.investment_overview_widget', $permissions))
	<div class="col-xl-3 col-md-6 col-sm-12">
		<div class="card mb-4 info-card dashboard-card">
			<div class="card-body">
				<div class="d-flex align-items-start justify-content-between">
					<div class="flex-grow-1">
						<h5 class="mb-2">{{ _lang('Total Investments') }}</h5>
						<h4 class="mb-3"><b>{{ $total_investments }}</b></h4>
						<a href="{{ route('investments.index') }}" class="btn btn-sm btn-light"><i class="ti-arrow-right"></i>{{ _lang('View') }}</a>
					</div>
					<div class="card-icon text-right"><i class="ti-bag"></i></div>
				</div>
			</div>
		</div>
	</div>
	@endif

</div>

@if (in_array('dashboard.investment_overview_widget', $permissions))
<div class="row">
	<div class="col-xl-3 col-md-6 col-sm-12 mb-4">
		<div class="card h-100 border-left-primary">
			<div class="card-body">
				<h6 class="text-muted mb-2"><i class="ti-money"></i> {{ _lang('Total Invested Amount') }}</h6>
				<h4 class="mb-0">{{ decimalPlace($investment_total_invested, currency()) }}</h4>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 col-sm-12 mb-4">
		<div class="card h-100 border-left-success">
			<div class="card-body">
				<h6 class="text-muted mb-2"><i class="ti-arrow-top"></i> {{ _lang('Total Returns') }}</h6>
				<h4 class="mb-0">{{ decimalPlace($investment_total_returns, currency()) }}</h4>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 col-sm-12 mb-4">
		<div class="card h-100 border-left-danger">
			<div class="card-body">
				<h6 class="text-muted mb-2"><i class="ti-minus"></i> {{ _lang('Total Expenses') }}</h6>
				<h4 class="mb-0">{{ decimalPlace($investment_total_expenses, currency()) }}</h4>
			</div>
		</div>
	</div>

	<div class="col-xl-3 col-md-6 col-sm-12 mb-4">
		<div class="card h-100 border-left-warning">
			<div class="card-body">
				<h6 class="text-muted mb-2"><i class="ti-plus"></i> {{ _lang('Total Profit') }}</h6>
				<h4 class="mb-0">{{ decimalPlace($investment_total_profit, currency()) }}</h4>
			</div>
		</div>
	</div>
</div>
@endif

@if (in_array('dashboard.investment_overview_widget', $permissions))
<div class="row mb-4">
	<div class="col-12">
		<div class="alert {{ $investment_available_balance < 0 ? 'alert-danger' : 'alert-info' }} mb-0">
			<strong><i class="ti-wallet"></i> {{ _lang('Available Investment Balance') }}:</strong>
			<span class="d-inline-block mt-2 mt-sm-0 ml-2">{{ decimalPlace($investment_available_balance, currency()) }}</span>
		</div>
	</div>
</div>
@endif

<div class="row">
	@if (in_array('dashboard.expense_overview_widget',$permissions))
	<div class="col-lg-4 col-md-12 mb-4">
		<div class="card h-100">
			<div class="card-header d-flex align-items-center flex-wrap">
				<span class="mb-2 mb-md-0"><i class="ti-bar-chart"></i> {{ _lang('Expense Overview').' - '.date('M Y') }}</span>
			</div>
			<div class="card-body">
				<canvas id="expenseOverview"></canvas>
			</div>
		</div>
	</div>
	@endif

	@if (in_array('dashboard.deposit_withdraw_analytics',$permissions))
	<div class="col-lg-8 col-md-12 mb-4">
		<div class="card h-100">
			<div class="card-header d-flex align-items-center flex-wrap">
				<span class="mb-2 mb-md-0"><i class="ti-line-double"></i> {{ _lang('Deposit & Withdraw Analytics').' - '.date('Y')  }}</span>
				<select class="filter-select ml-auto py-0 auto-select mt-2 mt-md-0" data-selected="{{ base_currency_id() }}">
					@foreach(\App\Models\Currency::where('status',1)->get() as $currency)
					<option value="{{ $currency->id }}" data-symbol="{{ currency($currency->name) }}">{{ $currency->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="card-body">
				<canvas id="transactionAnalysis"></canvas>
			</div>
		</div>
	</div>
	@endif
</div>

<div class="row">
	@if (in_array('dashboard.investment_overview_widget', $permissions))
	<div class="col-lg-4 col-md-12 mb-4">
		<div class="card h-100">
			<div class="card-header d-flex align-items-center">
				<span><i class="ti-pie-chart"></i> {{ _lang('Investment Summary') }}</span>
			</div>
			<div class="card-body">
				<canvas id="investmentSummaryChart"></canvas>
			</div>
		</div>
	</div>
	@endif

	@if (in_array('dashboard.investment_overview_widget', $permissions))
	<div class="col-lg-8 col-md-12 mb-4">
		<div class="card h-100">
			<div class="card-header d-flex align-items-center flex-wrap">
				<span class="mb-2 mb-md-0"><i class="ti-list"></i> {{ _lang('Investment List') }}</span>
				<a href="{{ route('investments.index') }}" class="btn btn-outline-primary btn-xs ml-auto mt-2 mt-md-0">{{ _lang('View All') }}</a>
			</div>
			<div class="card-body px-0 pt-0">
				<div class="table-responsive">
					<table class="table table-bordered mb-0 table-sm">
						<thead>
							<tr>
								<th class="pl-4">{{ _lang('Name') }}</th>
								<th>{{ _lang('Invested Amount') }}</th>
								<th>{{ _lang('Return') }}</th>
								<th>{{ _lang('Profit') }}</th>
								<th class="pr-4">{{ _lang('Status') }}</th>
							</tr>
						</thead>
						<tbody>
							@if($investments->isEmpty())
								<tr>
									<td colspan="5"><p class="text-center mb-0">{{ _lang('No Data Available') }}</p></td>
								</tr>
							@endif
							@foreach($investments as $investment)
								@php
									$totalInvested = (float) ($investment->total_invested_sum ?? 0);
									$totalReturn = (float) ($investment->total_return_sum ?? 0);
									$totalExpense = (float) ($investment->total_expense_sum ?? 0);
									$profit = $totalReturn - $totalInvested - $totalExpense;
								@endphp
								<tr>
									<td class="pl-4">{{ $investment->name }}</td>
									<td>{{ decimalPlace($totalInvested, currency()) }}</td>
									<td>{{ decimalPlace($totalReturn, currency()) }}</td>
									<td class="{{ $profit >= 0 ? 'text-success' : 'text-danger' }}">{{ decimalPlace($profit, currency()) }}</td>
									<td class="pr-4">{!! $investment->status === 'active' ? xss_clean(show_status(_lang('Active'), 'success')) : xss_clean(show_status(_lang('Completed'), 'info')) !!}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	@endif
</div>

@if (in_array('dashboard.recent_transaction_widget',$permissions))
<div class="row">
	<div class="col-12">
		<div class="card mb-4">
			<div class="card-header">
				<i class="ti-receipt"></i> {{ _lang('Recent Transactions') }}
			</div>
			<div class="card-body px-0 pt-0">
				<div class="table-responsive">
					<table class="table table-bordered table-sm">
						<thead>
							<tr>
								<th class="pl-4">{{ _lang('Date') }}</th>
								<th>{{ _lang('Member') }}</th>
								<th class="text-nowrap">{{ _lang('Account Number') }}</th>
								<th>{{ _lang('Amount') }}</th>
								<th class="text-nowrap">{{ _lang('Debit/Credit') }}</th>
								<th>{{ _lang('Type') }}</th>
								<th>{{ _lang('Status') }}</th>
								<th class="text-center">{{ _lang('Action') }}</th>
							</tr>
						</thead>
						<tbody>
						@if(count($recent_transactions) == 0)
							<tr>
								<td colspan="8"><p class="text-center">{{ _lang('No Data Available') }}</p></td>
							</tr>
						@endif
						@foreach($recent_transactions as $transaction)
							@php
							$symbol = $transaction->dr_cr == 'dr' ? '-' : '+';
							$class  = $transaction->dr_cr == 'dr' ? 'text-danger' : 'text-success';
							@endphp
							<tr>
								<td class="pl-4 text-nowrap"><small>{{ $transaction->trans_date }}</small></td>
								<td><small>{{ $transaction->member->name }}</small></td>
								<td><small>{{ $transaction->account->account_number }}</small></td>
								<td><span class="text-nowrap {{ $class }}"><small>{{ $symbol.' '.decimalPlace($transaction->amount, currency($transaction->account->savings_type->currency->name)) }}</small></span></td>
								<td><small>{{ strtoupper($transaction->dr_cr) }}</small></td>
								<td><small>{{ ucwords(str_replace('_',' ',$transaction->type)) }}</small></td>
								<td>{!! xss_clean(transaction_status($transaction->status)) !!}</td>
								<td class="text-center"><a href="{{ route('transactions.show', $transaction->id) }}" target="_blank" class="btn btn-outline-primary btn-xs"><i class="ti-arrow-right"></i>&nbsp;<span class="d-none d-md-inline">{{ _lang('View') }}</span></a></td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@endif
@endsection

@section('js-script')
@if (in_array('dashboard.investment_overview_widget', $permissions))
<script>
window.investmentSummaryData = {
	labels: ["{{ _lang('Invested') }}", "{{ _lang('Returns') }}", "{{ _lang('Expenses') }}", "{{ _lang('Profit') }}"],
	values: [{{ $investment_total_invested }}, {{ $investment_total_returns }}, {{ $investment_total_expenses }}, {{ $investment_total_profit }}]
};
</script>
@endif
<script src="{{ asset('public/backend/plugins/chartJs/chart.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/dashboard.js?v=1.1') }}"></script>
@endsection
