@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card no-export">
			<div class="card-header d-flex align-items-center">
				<span class="panel-title">{{ _lang('Investments') }}</span>
				<a class="btn btn-primary btn-xs ml-auto ajax-modal" data-title="{{ _lang('Add New Investment') }}" href="{{ route('investments.create') }}"><i class="ti-plus"></i>&nbsp;{{ _lang('Add New') }}</a>
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
								<span class="dropdown">
									<button class="btn btn-primary dropdown-toggle btn-xs" type="button" id="investmentDropdown{{ $investment->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										{{ _lang('Action') }}
									</button>
									<form action="{{ route('investments.destroy', $investment->id) }}" method="post">
										{{ csrf_field() }}
										<input name="_method" type="hidden" value="DELETE">

										<div class="dropdown-menu" aria-labelledby="investmentDropdown{{ $investment->id }}">
											<a href="{{ route('investments.show', $investment->id) }}" data-title="{{ _lang('Investment Details') }}" class="dropdown-item ajax-modal"><i class="ti-eye"></i>&nbsp;{{ _lang('View') }}</a>
											<a href="{{ route('investments.edit', $investment->id) }}" data-title="{{ _lang('Update Investment') }}" class="dropdown-item dropdown-edit ajax-modal"><i class="ti-pencil-alt"></i>&nbsp;{{ _lang('Edit') }}</a>
											<button class="btn-remove dropdown-item" type="submit"><i class="ti-trash"></i>&nbsp;{{ _lang('Delete') }}</button>
										</div>
									</form>
								</span>
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
