<div class="row px-2">
	<div class="col-md-12">
		<table class="table table-bordered">
			<tr><td>{{ _lang('Name') }}</td><td>{{ $investment->name }}</td></tr>
			<tr><td>{{ _lang('Description') }}</td><td>{{ $investment->description ?? _lang('N/A') }}</td></tr>
			<tr><td>{{ _lang('Invested Amount') }}</td><td>{{ decimalPlace($investment->invested_amount, currency()) }}</td></tr>
			<tr><td>{{ _lang('Start Date') }}</td><td>{{ $investment->start_date->format('Y-m-d') }}</td></tr>
			<tr><td>{{ _lang('End Date') }}</td><td>{{ optional($investment->end_date)->format('Y-m-d') ?? _lang('Ongoing') }}</td></tr>
			<tr><td>{{ _lang('Expected Return') }}</td><td>{{ $investment->expected_return !== null ? decimalPlace($investment->expected_return, currency()) : _lang('N/A') }}</td></tr>
			<tr><td>{{ _lang('Status') }}</td><td>{!! $investment->status === 'active' ? xss_clean(show_status(_lang('Active'), 'success')) : xss_clean(show_status(_lang('Completed'), 'info')) !!}</td></tr>
			<tr><td>{{ _lang('Created At') }}</td><td>{{ $investment->created_at }}</td></tr>
			<tr><td>{{ _lang('Updated At') }}</td><td>{{ $investment->updated_at }}</td></tr>
		</table>
	</div>
</div>
