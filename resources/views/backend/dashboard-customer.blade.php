@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-xl-12">
		<div class="card mb-4">
			<div class="card-header">
				<div>{{ _lang('Accounts Overview') }}</div>
			</div>
			<div class="card-body px-0 pt-0">
				<div class="table-responsive">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="text-nowrap pl-4">{{ _lang('Account Number') }}</th>
								<th class="text-nowrap">{{ _lang('Account Type') }}</th>
								<th>{{ _lang('Currency') }}</th>
								<th class="text-right">{{ _lang('Balance') }}</th>
								<th class="text-nowrap text-right">{{ _lang('Blocked Amount') }}</th>
								<th class="text-nowrap text-right pr-4">{{ _lang('Current Balance') }}</th>
							</tr>
						</thead>
						<tbody>
							@foreach(get_account_details(auth()->user()->member->id) as $account)
							<tr>
								<td class="pl-4">{{ $account->account_number }}</td>
								<td class="text-nowrap">{{ $account->savings_type->name }}</td>
								<td>{{ $account->savings_type->currency->name }}</td>
								<td class="text-nowrap text-right">{{ decimalPlace($account->balance, currency($account->savings_type->currency->name)) }}</td>
								<td class="text-nowrap text-right">{{ decimalPlace($account->blocked_amount, currency($account->savings_type->currency->name)) }}</td>
								<td class="text-nowrap text-right pr-4">{{ decimalPlace($account->balance - $account->blocked_amount, currency($account->savings_type->currency->name)) }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card mb-4">
			<div class="card-header">
				{{ _lang('Investment Summary') }}
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-3 col-sm-6 mb-3">
						<div class="card h-100 border-left-primary">
							<div class="card-body">
								<h6 class="text-muted mb-2">{{ _lang('Total Investments') }}</h6>
								<h4 class="mb-0">{{ $investment_total_count }}</h4>
							</div>
						</div>
					</div>

					<div class="col-md-3 col-sm-6 mb-3">
						<div class="card h-100 border-left-success">
							<div class="card-body">
								<h6 class="text-muted mb-2">{{ _lang('Active Investments') }}</h6>
								<h4 class="mb-0">{{ $investment_active_count }}</h4>
							</div>
						</div>
					</div>

					<div class="col-md-3 col-sm-6 mb-3">
						<div class="card h-100 border-left-info">
							<div class="card-body">
								<h6 class="text-muted mb-2">{{ _lang('Total Invested Amount') }}</h6>
								<h4 class="mb-0">{{ decimalPlace($investment_total_invested, currency()) }}</h4>
							</div>
						</div>
					</div>

					<div class="col-md-3 col-sm-6 mb-3">
						<div class="card h-100 border-left-warning">
							<div class="card-body">
								<h6 class="text-muted mb-2">{{ _lang('Your Profit Received') }}</h6>
								<h4 class="mb-0">{{ decimalPlace($member_investment_profit, currency()) }}</h4>
							</div>
						</div>
					</div>
				</div>

				<div class="table-responsive">
					<table class="table table-bordered mb-0">
						<thead>
							<tr>
								<th class="pl-4">{{ _lang('Investment') }}</th>
								<th>{{ _lang('Invested Amount') }}</th>
								<th>{{ _lang('Expected Return') }}</th>
								<th>{{ _lang('Start Date') }}</th>
								<th class="pr-4">{{ _lang('Status') }}</th>
							</tr>
						</thead>
						<tbody>
							@if($recent_investments->isEmpty())
								<tr>
									<td colspan="5" class="text-center">{{ _lang('No investment data available') }}</td>
								</tr>
							@endif

							@foreach($recent_investments as $investment)
								<tr>
									<td class="pl-4">{{ $investment->name }}</td>
									<td>{{ decimalPlace($investment->invested_amount, currency()) }}</td>
									<td>{{ $investment->expected_return !== null ? decimalPlace($investment->expected_return, currency()) : _lang('N/A') }}</td>
									<td>{{ $investment->start_date ? $investment->start_date->format(get_option('date_format','Y-m-d')) : '-' }}</td>
									<td class="pr-4">{!! $investment->status === 'active' ? xss_clean(show_status(_lang('Active'), 'success')) : xss_clean(show_status(_lang('Completed'), 'info')) !!}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card mb-4">
			<div class="card-header">
				{{ _lang('Monthly Deposit Overview') }}
			</div>
			<div class="card-body px-0 pt-0">
				@if($accounts->isEmpty())
					<div class="alert alert-info m-3 mb-0">
						{{ _lang('No savings account found') }}
					</div>
				@else
					<div class="table-responsive">
						<table class="table table-bordered mb-0">
							<thead>
								<tr>
									<th class="pl-4">{{ _lang('Account Number') }}</th>
									<th>{{ _lang('Monthly Deposit') }}</th>
									<th>{{ _lang('Month') }}</th>
									<th>{{ _lang('Year') }}</th>
									<th>{{ _lang('Status') }}</th>
									<th class="pr-4">{{ _lang('Paid Date') }}</th>
								</tr>
							</thead>
							<tbody>
								@php $hasMonthlyDeposits = false; @endphp
								@foreach($accounts as $account)
									@forelse($account->monthly_deposits as $deposit)
										@php $hasMonthlyDeposits = true; @endphp
										<tr>
											<td class="pl-4">{{ $account->account_number }}</td>
											<td>{{ decimalPlace($account->monthly_deposit_amount, currency($account->savings_type->currency->name)) }}</td>
											<td>{{ date('F', mktime(0, 0, 0, $deposit->month, 1)) }}</td>
											<td>{{ $deposit->year }}</td>
											<td>
												@if($deposit->status === 'pending')
													<span class="badge badge-warning">{{ _lang('Pending') }}</span>
                                                @else
                                                    <span class="badge badge-success">{{ _lang('Paid')}}</span>
												@endif
											</td>
											<td class="pr-4">{{ $deposit->paid_date ? \Carbon\Carbon::parse($deposit->paid_date)->format(get_option('date_format','Y-m-d')) : '-' }}</td>
										</tr>
									@empty
									@endforelse
								@endforeach

								@if($hasMonthlyDeposits === false)
									<tr>
										<td colspan="7" class="text-center">{{ _lang('No monthly deposit records found') }}</td>
									</tr>
								@endif
							</tbody>
						</table>
					</div>
				@endif
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card mb-4">
			<div class="card-header">
				{{ _lang('Recent Transactions') }}
			</div>
			<div class="card-body px-0 pt-0">
				<div class="table-responsive">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="pl-4">{{ _lang('Date') }}</th>
								<th>{{ _lang('AC Number') }}</th>
								<th class="text-right">{{ _lang('Amount') }}</th>
								<th>{{ _lang('DR/CR') }}</th>
								<th>{{ _lang('Type') }}</th>
								<th>{{ _lang('Status') }}</th>
								<th class="text-center">{{ _lang('Details') }}</th>
							</tr>
						</thead>
						<tbody>
							@if(count($recent_transactions) == 0)
								<tr>
									<td colspan="7"><p class="text-center">{{ _lang('No Data Available') }}</p></td>
								</tr>
							@endif
							@foreach($recent_transactions as $transaction)
							@php
							$symbol = $transaction->dr_cr == 'dr' ? '-' : '+';
							$class  = $transaction->dr_cr == 'dr' ? 'text-danger' : 'text-success';
							@endphp
							<tr>
								<td class="pl-4">{{ $transaction->trans_date }}</td>
								<td>{{ $transaction->account->account_number }} - {{ $transaction->account->savings_type->name }} ({{ $transaction->account->savings_type->currency->name }})</td>
								<td class="text-right"><span class="{{ $class }}">{{ $symbol.' '.decimalPlace($transaction->amount, currency($transaction->account->savings_type->currency->name)) }}</span></td>
								<td>{{ strtoupper($transaction->dr_cr) }}</td>
								<td>{{ ucwords(str_replace('_',' ',$transaction->type)) }}</td>
								<td>{!! xss_clean(transaction_status($transaction->status)) !!}</td>
								<td class="text-center"><a href="{{ route('trasnactions.details', $transaction->id) }}" target="_blank" class="btn btn-outline-primary btn-xs">{{ _lang('View') }}</a></td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
