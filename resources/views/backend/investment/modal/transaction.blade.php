<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('investments.transactions.add', $investment->id) }}" enctype="multipart/form-data">
	{{ csrf_field() }}
	<div class="row px-2">
		<div class="col-md-12">
			<div class="alert alert-info">
				<div>
					<strong>{{ _lang('Net Fund Balance') }}:</strong> {{ decimalPlace($fundSummary['total_account_deposits'], currency()) }}
					<i class="fas fa-info-circle text-info ml-1" data-toggle="tooltip" title="{{ _lang('Used for investment funding. Formula: Deposits - Withdrawals - Expenses + Profit') }}"></i>
				</div>
				<div class="small text-muted mb-1">{{ _lang('Used as the fund pool for investments') }}</div>
				<div><strong>{{ _lang('Total Invested') }}:</strong> {{ decimalPlace($fundSummary['total_invested'], currency()) }}</div>
				<div><strong>{{ _lang('Available Balance') }}:</strong> {{ decimalPlace($fundSummary['available_balance'], currency()) }}</div>
			</div>
			<div class="alert alert-warning d-none" id="transaction_balance_warning">
				{{ _lang('Investment amount exceeds available balance') }}
			</div>
		</div>

		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label">{{ _lang('Type') }}</label>
				<select class="form-control auto-select" data-selected="{{ old('type', 'invest') }}" name="type" required>
					<option value="invest">{{ _lang('Invest') }}</option>
					<option value="return">{{ _lang('Return') }}</option>
					<option value="expense">{{ _lang('Expense') }}</option>
				</select>
			</div>
		</div>

		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label">{{ _lang('Amount') }}</label>
				<input type="number" class="form-control" name="amount" value="{{ old('amount') }}" min="0.01" step="0.01" required>
			</div>
		</div>

		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label">{{ _lang('Date') }}</label>
				<input type="text" class="form-control datepicker" name="date" value="{{ old('date', date('Y-m-d')) }}" readOnly="true" required>
			</div>
		</div>

		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label">{{ _lang('Note') }}</label>
				<textarea class="form-control" name="note" rows="4">{{ old('note') }}</textarea>
			</div>
		</div>

		<div class="col-md-12 mt-2">
			<div class="form-group">
				<button type="submit" class="btn btn-primary"><i class="ti-check-box"></i>&nbsp;{{ _lang('Save') }}</button>
			</div>
		</div>
	</div>
</form>

<script>
(function ($) {
	"use strict";

	var availableBalance = {{ $fundSummary['available_balance'] }};

	function toggleTransactionWarning() {
		var amount = parseFloat($("input[name='amount']").val() || 0);
		var type = $("select[name='type']").val();
		var shouldWarn = type === "invest" && amount > availableBalance;

		$("#transaction_balance_warning").toggleClass("d-none", !shouldWarn);
	}

	$(document).on("input", "input[name='amount']", toggleTransactionWarning);
	$(document).on("change", "select[name='type']", toggleTransactionWarning);
	toggleTransactionWarning();
	$('[data-toggle="tooltip"]').tooltip();
})(jQuery);
</script>
