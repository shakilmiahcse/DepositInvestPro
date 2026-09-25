@extends('layouts.app')

@section('content')

<!-- Member Welcome Header -->
<div class="row mb-3">
	<div class="col-12">
		<div class="d-flex align-items-center justify-content-between p-3 bg-white rounded-lg shadow-sm border">
			<div class="d-flex align-items-center">
				<img src="{{ profile_picture() }}" class="rounded-circle mr-3 border" style="width: 48px; height: 48px; object-fit: cover;" alt="avatar">
				<div>
					<h5 class="mb-0 font-weight-bold text-dark">{{ _lang('Welcome back') }}, {{ Auth::user()->name }} 👋</h5>
					<small class="text-muted">{{ _lang('Member No') }}: #{{ Auth::user()->member->member_no ?? Auth::user()->member->id }}</small>
				</div>
			</div>
			<div>
				<a href="{{ route('profile.membership_details') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
					<i class="fas fa-id-card mr-1"></i> {{ _lang('ID Card') }}
				</a>
			</div>
		</div>
	</div>
</div>

<!-- Mobile Fintech Wallet Cards -->
<div class="row">
	@php $accountDetails = get_account_details(auth()->user()->member->id); @endphp
	@foreach($accountDetails as $account)
	<div class="col-lg-6 col-12">
		<div class="fintech-wallet-card">
			<div class="wallet-header">
				<span class="wallet-type-badge">{{ $account->savings_type->name }}</span>
				<span class="wallet-acc-no" onclick="copyAccountNumber('{{ $account->account_number }}', '{{ _lang('Account number copied!') }}')" title="{{ _lang('Click to copy') }}">
					<span>{{ $account->account_number }}</span>
					<i class="far fa-copy"></i>
				</span>
			</div>
			
			<div class="wallet-balance-label">{{ _lang('Available Balance') }}</div>
			<div class="wallet-balance-amount">
				{{ decimalPlace($account->balance - $account->blocked_amount, currency($account->savings_type->currency->name)) }}
			</div>

			<div class="wallet-footer">
				<div>
					<span class="opacity-75">{{ _lang('Total Balance') }}:</span>
					<strong>{{ decimalPlace($account->balance, currency($account->savings_type->currency->name)) }}</strong>
				</div>
				@if($account->blocked_amount > 0)
				<div>
					<span class="opacity-75">{{ _lang('Blocked') }}:</span>
					<strong>{{ decimalPlace($account->blocked_amount, currency($account->savings_type->currency->name)) }}</strong>
				</div>
				@endif
			</div>
		</div>
	</div>
	@endforeach
</div>

<!-- Quick Action Grid -->
<div class="row">
	<div class="col-12">
		<div class="quick-actions-grid">
			<a href="{{ route('deposit.automatic_methods') }}" class="quick-action-item">
				<div class="action-icon bg-deposit">
					<i class="fas fa-wallet"></i>
				</div>
				<span class="action-label">{{ _lang('Deposit') }}</span>
			</a>

			<a href="{{ route('transfer.own_account_transfer') }}" class="quick-action-item">
				<div class="action-icon bg-transfer">
					<i class="fas fa-exchange-alt"></i>
				</div>
				<span class="action-label">{{ _lang('Transfer') }}</span>
			</a>

			<a href="{{ route('withdraw.manual_methods') }}" class="quick-action-item">
				<div class="action-icon bg-withdraw">
					<i class="fas fa-money-check-alt"></i>
				</div>
				<span class="action-label">{{ _lang('Withdraw') }}</span>
			</a>

			<a href="{{ route('customer_reports.account_statement') }}" class="quick-action-item">
				<div class="action-icon bg-passbook">
					<i class="fas fa-book"></i>
				</div>
				<span class="action-label">{{ _lang('Passbook') }}</span>
			</a>
		</div>
	</div>
</div>

<!-- Desktop Accounts Overview Table -->
<div class="row d-none d-md-block">
	<div class="col-xl-12">
		<div class="card mb-4">
			<div class="card-header font-weight-bold">
				<i class="fas fa-landmark text-primary mr-1"></i> {{ _lang('Accounts Overview') }}
			</div>
			<div class="card-body px-0 pt-0">
				<div class="table-responsive">
					<table class="table table-bordered mb-0">
						<thead class="bg-light">
							<tr>
								<th class="text-nowrap pl-4">{{ _lang('Account Number') }}</th>
								<th class="text-nowrap">{{ _lang('Account Type') }}</th>
								<th>{{ _lang('Currency') }}</th>
								<th class="text-right">{{ _lang('Balance') }}</th>
								<th class="text-nowrap text-right">{{ _lang('Blocked Amount') }}</th>
								<th class="text-nowrap text-right pr-4">{{ _lang('Available Balance') }}</th>
							</tr>
						</thead>
						<tbody>
							@foreach($accountDetails as $account)
							<tr>
								<td class="pl-4 font-weight-bold text-primary">{{ $account->account_number }}</td>
								<td class="text-nowrap">{{ $account->savings_type->name }}</td>
								<td><span class="badge badge-secondary">{{ $account->savings_type->currency->name }}</span></td>
								<td class="text-nowrap text-right">{{ decimalPlace($account->balance, currency($account->savings_type->currency->name)) }}</td>
								<td class="text-nowrap text-right text-danger">{{ decimalPlace($account->blocked_amount, currency($account->savings_type->currency->name)) }}</td>
								<td class="text-nowrap text-right pr-4 font-weight-bold text-success">{{ decimalPlace($account->balance - $account->blocked_amount, currency($account->savings_type->currency->name)) }}</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Investment Summary -->
<div class="row">
	<div class="col-xl-12">
		<div class="card mb-4">
			<div class="card-header font-weight-bold">
				<i class="fas fa-chart-line text-info mr-1"></i> {{ _lang('Investment Summary') }}
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-3 col-6 mb-3">
						<div class="card h-100 border-left-primary shadow-sm">
							<div class="card-body p-3">
								<h6 class="text-muted small mb-1">{{ _lang('Total Investments') }}</h6>
								<h4 class="mb-0 font-weight-bold">{{ $investment_total_count }}</h4>
							</div>
						</div>
					</div>

					<div class="col-md-3 col-6 mb-3">
						<div class="card h-100 border-left-success shadow-sm">
							<div class="card-body p-3">
								<h6 class="text-muted small mb-1">{{ _lang('Active Investments') }}</h6>
								<h4 class="mb-0 font-weight-bold text-success">{{ $investment_active_count }}</h4>
							</div>
						</div>
					</div>

					<div class="col-md-3 col-6 mb-3">
						<div class="card h-100 border-left-info shadow-sm">
							<div class="card-body p-3">
								<h6 class="text-muted small mb-1">{{ _lang('Total Invested') }}</h6>
								<h4 class="mb-0 font-weight-bold text-info">{{ decimalPlace($investment_total_invested, currency()) }}</h4>
							</div>
						</div>
					</div>

					<div class="col-md-3 col-6 mb-3">
						<div class="card h-100 border-left-warning shadow-sm">
							<div class="card-body p-3">
								<h6 class="text-muted small mb-1">{{ _lang('Profit Received') }}</h6>
								<h4 class="mb-0 font-weight-bold text-warning">{{ decimalPlace($member_investment_profit, currency()) }}</h4>
							</div>
						</div>
					</div>
				</div>

				<div class="table-responsive">
					<table class="table table-bordered mb-0">
						<thead class="bg-light">
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
									<td colspan="5" class="text-center text-muted py-3">{{ _lang('No investment data available') }}</td>
								</tr>
							@endif

							@foreach($recent_investments as $investment)
								<tr>
									<td class="pl-4 font-weight-bold">{{ $investment->name }}</td>
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

<!-- Monthly Deposit Overview -->
<div class="row">
	<div class="col-xl-12">
		<div class="card mb-4">
			<div class="card-header font-weight-bold">
				<i class="fas fa-calendar-alt text-success mr-1"></i> {{ _lang('Monthly Deposit Overview') }}
			</div>
			<div class="card-body px-0 pt-0">
				@if($accounts->isEmpty())
					<div class="alert alert-info m-3 mb-0">
						{{ _lang('No savings account found') }}
					</div>
				@else
					<div class="table-responsive">
						<table class="table table-bordered mb-0">
							<thead class="bg-light">
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
											<td class="pl-4 font-weight-bold">{{ $account->account_number }}</td>
											<td>{{ decimalPlace($account->monthly_deposit_amount, currency($account->savings_type->currency->name)) }}</td>
											<td>{{ date('F', mktime(0, 0, 0, $deposit->month, 1)) }}</td>
											<td>{{ $deposit->year }}</td>
											<td>
												@if($deposit->status === 'pending')
													<span class="badge badge-warning px-2 py-1"><i class="far fa-clock mr-1"></i>{{ _lang('Pending') }}</span>
                                                @else
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>{{ _lang('Paid') }}</span>
												@endif
											</td>
											<td class="pr-4">{{ $deposit->paid_date ? \Carbon\Carbon::parse($deposit->paid_date)->format(get_option('date_format','Y-m-d')) : '-' }}</td>
										</tr>
									@empty
									@endforelse
								@endforeach

								@if($hasMonthlyDeposits === false)
									<tr>
										<td colspan="6" class="text-center text-muted py-3">{{ _lang('No monthly deposit records found') }}</td>
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

<!-- Recent Transactions (Responsive Card Feed for Mobile + Desktop Table) -->
<div class="row">
	<div class="col-xl-12">
		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center">
				<span class="font-weight-bold"><i class="fas fa-history text-secondary mr-1"></i> {{ _lang('Recent Transactions') }}</span>
				<a href="{{ route('customer_reports.transactions_report') }}" class="btn btn-outline-primary btn-xs rounded-pill px-3">{{ _lang('View All') }}</a>
			</div>
			
			<div class="card-body px-0 pt-0">
				<!-- Mobile Feed -->
				<div class="mobile-tx-list">
					@if(count($recent_transactions) == 0)
						<div class="text-center text-muted py-4">{{ _lang('No Data Available') }}</div>
					@endif
					@foreach($recent_transactions as $transaction)
					@php
						$isDr = ($transaction->dr_cr == 'dr');
						$sign = $isDr ? '-' : '+';
						$color = $isDr ? 'text-danger' : 'text-success';
						$iconClass = $isDr ? 'dr' : 'cr';
						$icon = $isDr ? 'fa-arrow-up' : 'fa-arrow-down';
					@endphp
					<a href="{{ route('trasnactions.details', $transaction->id) }}" class="mobile-tx-item text-decoration-none">
						<div class="mobile-tx-left">
							<div class="mobile-tx-icon {{ $iconClass }}">
								<i class="fas {{ $icon }}"></i>
							</div>
							<div>
								<div class="mobile-tx-title">{{ ucwords(str_replace('_',' ',$transaction->type)) }}</div>
								<div class="mobile-tx-date">{{ $transaction->trans_date }} &bull; {{ $transaction->account->account_number }}</div>
							</div>
						</div>
						<div class="mobile-tx-right">
							<div class="mobile-tx-amount {{ $color }}">
								{{ $sign }} {{ decimalPlace($transaction->amount, currency($transaction->account->savings_type->currency->name)) }}
							</div>
							<div>
								{!! xss_clean(transaction_status($transaction->status)) !!}
							</div>
						</div>
					</a>
					@endforeach
				</div>

				<!-- Desktop Table -->
				<div class="table-responsive desktop-tx-table">
					<table class="table table-bordered mb-0">
						<thead class="bg-light">
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
									<td colspan="7"><p class="text-center text-muted py-3">{{ _lang('No Data Available') }}</p></td>
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
								<td class="text-right font-weight-bold"><span class="{{ $class }}">{{ $symbol.' '.decimalPlace($transaction->amount, currency($transaction->account->savings_type->currency->name)) }}</span></td>
								<td><span class="badge badge-light border">{{ strtoupper($transaction->dr_cr) }}</span></td>
								<td>{{ ucwords(str_replace('_',' ',$transaction->type)) }}</td>
								<td>{!! xss_clean(transaction_status($transaction->status)) !!}</td>
								<td class="text-center"><a href="{{ route('trasnactions.details', $transaction->id) }}" target="_blank" class="btn btn-outline-primary btn-xs rounded-pill px-3">{{ _lang('View') }}</a></td>
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
