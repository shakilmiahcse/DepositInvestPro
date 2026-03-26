@extends('layouts.app')

@section('content')
@php
	$permissions = auth()->user()->user_type === 'user' ? permission_list() : [];
	$canCreateInvestment = auth()->user()->user_type === 'admin' || in_array('investments.create', $permissions);
	$canViewInvestment = auth()->user()->user_type === 'admin' || in_array('investments.show', $permissions);
	$canEditInvestment = auth()->user()->user_type === 'admin' || in_array('investments.edit', $permissions);
	$canDeleteInvestment = auth()->user()->user_type === 'admin' || in_array('investments.destroy', $permissions);
	$canViewTransactions = auth()->user()->user_type === 'admin' || in_array('investments.transactions.index', $permissions);
	$hasInvestmentActions = $canViewInvestment || $canEditInvestment || $canDeleteInvestment || $canViewTransactions;
@endphp
<div class="row">
	<div class="col-lg-12">
		<div class="row">
			<div class="col-md-4">
				<div class="card">
					<div class="card-body">
						<h6 class="text-muted mb-1">
							{{ _lang('Net Fund Balance') }}
							<i class="fas fa-info-circle text-info ml-1" data-toggle="tooltip" title="{{ _lang('Used for investment funding. Formula: Deposits - Withdrawals - Expenses + Profit') }}"></i>
						</h6>
						<div class="small text-muted mb-2">{{ _lang('Used as the fund pool for investments') }}</div>
						<h4 class="mb-0">{{ decimalPlace($fundSummary['total_account_deposits'], currency()) }}</h4>
					</div>
				</div>
			</div>

			<div class="col-md-4">
				<div class="card">
					<div class="card-body">
						<h6 class="text-muted">{{ _lang('Total Invested') }}</h6>
						<h4 class="mb-0">{{ decimalPlace($fundSummary['total_invested'], currency()) }}</h4>
					</div>
				</div>
			</div>

			<div class="col-md-4">
				<div class="card">
					<div class="card-body">
						<h6 class="text-muted">{{ _lang('Available Balance') }}</h6>
						<h4 class="mb-0 {{ $fundSummary['available_balance'] < 0 ? 'text-danger' : 'text-success' }}">{{ decimalPlace($fundSummary['available_balance'], currency()) }}</h4>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-12">
		<div class="card no-export">
			<div class="card-header d-flex align-items-center">
				<span class="panel-title">{{ _lang('Investments') }}</span>
				@if($canCreateInvestment)
				<a class="btn btn-primary btn-xs ml-auto ajax-modal" data-title="{{ _lang('Add New Investment') }}" href="{{ route('investments.create') }}"><i class="ti-plus"></i>&nbsp;{{ _lang('Add New') }}</a>
				@endif
			</div>
			<div class="card-body">
				<table id="investments_table" class="table table-bordered data-table">
					<thead>
						<tr>
							<th>{{ _lang('Name') }}</th>
							<th>{{ _lang('Invested Amount') }}</th>
							<th>{{ _lang('Start Date') }}</th>
							<th>{{ _lang('End Date') }}</th>
							<th>{{ _lang('Expected Return') }}</th>
							<th>{{ _lang('Status') }}</th>
							<th class="text-center">{{ _lang('Action') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach($investments as $investment)
						<tr data-id="row_{{ $investment->id }}">
							<td class="name">{{ $investment->name }}</td>
							<td class="invested_amount">{{ decimalPlace($investment->invested_amount, currency()) }}</td>
							<td class="start_date">{{ $investment->start_date->format('Y-m-d') }}</td>
							<td class="end_date">{{ optional($investment->end_date)->format('Y-m-d') ?? _lang('Ongoing') }}</td>
							<td class="expected_return">{{ $investment->expected_return !== null ? decimalPlace($investment->expected_return, currency()) : _lang('N/A') }}</td>
							<td class="status">{!! $investment->status === 'active' ? xss_clean(show_status(_lang('Active'), 'success')) : xss_clean(show_status(_lang('Completed'), 'info')) !!}</td>
							<td class="text-center">
								@if($hasInvestmentActions)
								<span class="dropdown">
									<button class="btn btn-primary dropdown-toggle btn-xs" type="button" id="investmentDropdown{{ $investment->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										{{ _lang('Action') }}
									</button>
									<form action="{{ route('investments.destroy', $investment->id) }}" method="post">
										{{ csrf_field() }}
										<input name="_method" type="hidden" value="DELETE">

										<div class="dropdown-menu" aria-labelledby="investmentDropdown{{ $investment->id }}">
											@if($canViewInvestment)
											<a href="{{ route('investments.show', $investment->id) }}" data-title="{{ _lang('Investment Details') }}" class="dropdown-item ajax-modal"><i class="ti-eye"></i>&nbsp;{{ _lang('View') }}</a>
											@endif
											@if($canViewTransactions)
											<a href="{{ route('investments.transactions.index', $investment->id) }}" class="dropdown-item"><i class="ti-list"></i>&nbsp;{{ _lang('Transactions') }}</a>
											@endif
											@if($canEditInvestment)
											<a href="{{ route('investments.edit', $investment->id) }}" data-title="{{ _lang('Update Investment') }}" class="dropdown-item dropdown-edit ajax-modal"><i class="ti-pencil-alt"></i>&nbsp;{{ _lang('Edit') }}</a>
											@endif
											@if($canDeleteInvestment)
											<button class="btn-remove dropdown-item" type="submit"><i class="ti-trash"></i>&nbsp;{{ _lang('Delete') }}</button>
											@endif
										</div>
									</form>
								</span>
								@else
								<span class="text-muted">{{ _lang('N/A') }}</span>
								@endif
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js-script')
<script>
(function ($) {
	"use strict";

	$('[data-toggle="tooltip"]').tooltip();
})(jQuery);
</script>
@endsection
