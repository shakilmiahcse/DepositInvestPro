<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('investments.store') }}" enctype="multipart/form-data">
	{{ csrf_field() }}
	<div class="row px-2">
		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label">{{ _lang('Name') }}</label>
				<input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
			</div>
		</div>

		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label">{{ _lang('Description') }}</label>
				<textarea class="form-control" name="description" rows="4">{{ old('description') }}</textarea>
			</div>
		</div>

		<div class="col-md-6">
			<div class="form-group">
				<label class="control-label">{{ _lang('Invested Amount') }}</label>
				<input type="number" class="form-control" name="invested_amount" value="{{ old('invested_amount') }}" min="0" step="0.01" required>
			</div>
		</div>

		<div class="col-md-6">
			<div class="form-group">
				<label class="control-label">{{ _lang('Expected Return') }}</label>
				<input type="number" class="form-control" name="expected_return" value="{{ old('expected_return') }}" min="0" step="0.01">
			</div>
		</div>

		<div class="col-md-6">
			<div class="form-group">
				<label class="control-label">{{ _lang('Start Date') }}</label>
				<input type="text" class="form-control datepicker" name="start_date" value="{{ old('start_date') }}" readOnly="true" required>
			</div>
		</div>

		<div class="col-md-6">
			<div class="form-group">
				<label class="control-label">{{ _lang('End Date') }}</label>
				<input type="text" class="form-control datepicker" name="end_date" value="{{ old('end_date') }}" readOnly="true">
			</div>
		</div>

		<div class="col-md-12">
			<div class="form-group">
				<label class="control-label">{{ _lang('Status') }}</label>
				<select class="form-control auto-select" data-selected="{{ old('status', 'active') }}" name="status" required>
					<option value="active">{{ _lang('Active') }}</option>
					<option value="completed">{{ _lang('Completed') }}</option>
				</select>
			</div>
		</div>

		<div class="col-md-12 mt-2">
			<div class="form-group">
				<button type="submit" class="btn btn-primary"><i class="ti-check-box"></i>&nbsp;{{ _lang('Save') }}</button>
			</div>
		</div>
	</div>
</form>
