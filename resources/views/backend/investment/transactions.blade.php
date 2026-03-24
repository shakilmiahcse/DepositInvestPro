@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header d-flex align-items-center">
				<span class="header-title">{{ _lang('Investment Transactions') }}</span>
				<a href="{{ route('investments.index') }}" class="btn btn-outline-primary btn-xs ml-auto mr-2">
					<i class="ti-arrow-left"></i>&nbsp;{{ _lang('Back') }}
				</a>
				<a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Add Transaction') }}" href="{{ route('investments.transactions.add', $investment->id) }}">
					<i class="ti-plus"></i>&nbsp;{{ _lang('Add New') }}
				</a>
			</div>

			<div class="card-body">
				<table class="table table-bordered mb-4">
					<tr><td>{{ _lang('Investment') }}</td><td>{{ $investment->name }}</td></tr>
					<tr><td>{{ _lang('Base Invested Amount') }}</td><td>{{ decimalPlace($investment->invested_amount, currency()) }}</td></tr>
					<tr><td>{{ _lang('Total Invest Entries') }}</td><td>{{ decimalPlace($totals['invest'], currency()) }}</td></tr>
					<tr><td>{{ _lang('Total Return Entries') }}</td><td>{{ decimalPlace($totals['return'], currency()) }}</td></tr>
					<tr><td>{{ _lang('Total Expense Entries') }}</td><td>{{ decimalPlace($totals['expense'], currency()) }}</td></tr>
				</table>

				<table id="investment_transactions_table" class="table table-bordered data-table">
					<thead>
						<tr>
							<th>{{ _lang('Date') }}</th>
							<th>{{ _lang('Type') }}</th>
							<th>{{ _lang('Amount') }}</th>
							<th>{{ _lang('Note') }}</th>
						</tr>
					</thead>
					<tbody>
						@forelse($investment->transactions as $transaction)
						<tr>
							<td>{{ $transaction->date->format('Y-m-d') }}</td>
							<td>
								@if($transaction->type === 'invest')
									{!! xss_clean(show_status(_lang('Invest'), 'primary')) !!}
								@elseif($transaction->type === 'return')
									{!! xss_clean(show_status(_lang('Return'), 'success')) !!}
								@else
									{!! xss_clean(show_status(_lang('Expense'), 'danger')) !!}
								@endif
							</td>
							<td>{{ decimalPlace($transaction->amount, currency()) }}</td>
							<td>{{ $transaction->note ?? _lang('N/A') }}</td>
						</tr>
						@empty
						<tr>
							<td colspan="4" class="text-center">{{ _lang('No Data Found') }}</td>
						</tr>
						@endforelse
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

	$(document).on("ajax-submit", function () {
		window.location.reload();
	});
})(jQuery);
</script>
@endsection
