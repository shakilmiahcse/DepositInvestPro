@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">
                            {{ _lang('Eligible Member Balance') }}
                            <i class="fas fa-info-circle text-info ml-1" data-toggle="tooltip" title="{{ _lang('Used for profit sharing. Formula: Total Credits - Total Debits for each savings account, then only positive balances are added') }}"></i>
                        </h6>
                        <div class="small text-muted mb-2">{{ _lang('Current positive savings balances used for profit distribution') }}</div>
                        <h4 class="mb-0">{{ decimalPlace($totalMemberDeposits, currency()) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted">{{ _lang('Total Distributions') }}</h6>
                        <h4 class="mb-0">{{ $distributionSummaries->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <span class="panel-title">{{ _lang('Distribute Profit') }}</span>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('profit_distributions.distribute') }}" autocomplete="off">
                    @csrf

                    <div class="form-group">
                        <label class="control-label">{{ _lang('Investment') }}</label>
                        <select class="form-control auto-select" name="investment_id" id="investment_id" data-selected="{{ old('investment_id') }}" required>
                            <option value="">{{ _lang('Select One') }}</option>
                            @foreach($investments as $investment)
                            @php $profitData = $investmentProfitMap[$investment->id]; @endphp
                            <option value="{{ $investment->id }}"
                                data-net-profit="{{ $profitData['net_profit'] }}"
                                data-available-profit="{{ $profitData['available_profit'] }}"
                                data-distributed="{{ $profitData['distributed'] ? 1 : 0 }}">
                                {{ $investment->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="alert alert-info mb-3" id="profit_distribution_info">
                        <div><strong>{{ _lang('Net Profit') }}:</strong> <span id="net_profit_display">{{ decimalPlace(0, currency()) }}</span></div>
                        <div><strong>{{ _lang('Available Profit') }}:</strong> <span id="available_profit_display">{{ decimalPlace(0, currency()) }}</span></div>
                        <div><strong>{{ _lang('Already Distributed') }}:</strong> <span id="distributed_status">{{ _lang('No') }}</span></div>
                    </div>

                    <div class="form-group">
                        <label class="control-label">{{ _lang('Total Profit') }}</label>
                        <input type="number" class="form-control" step="0.01" min="0.01" name="total_profit" id="total_profit" value="{{ old('total_profit') }}" required>
                    </div>

                    <div class="alert alert-warning d-none" id="profit_distribution_warning">
                        {{ _lang('Profit amount exceeds available investment profit') }}
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti-check-box"></i>&nbsp;{{ _lang('Distribute Profit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <span class="panel-title">{{ _lang('Distribution History') }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ _lang('Date') }}</th>
                                <th>{{ _lang('Investment') }}</th>
                                <th>{{ _lang('Total Profit') }}</th>
                                <th>{{ _lang('Distributed') }}</th>
                                <th>{{ _lang('Remaining') }}</th>
                                <th>{{ _lang('Entries') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($distributionSummaries as $distribution)
                            <tr>
                                <td>{{ $distribution->distribution_date->format('Y-m-d H:i') }}</td>
                                <td>{{ $distribution->investment->name }}</td>
                                <td>{{ decimalPlace($distribution->total_profit, currency()) }}</td>
                                <td>{{ decimalPlace($distribution->distributed_amount, currency()) }}</td>
                                <td>{{ decimalPlace($distribution->remaining_profit, currency()) }}</td>
                                <td>{{ $distribution->details_count }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ _lang('No Data Found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <span class="panel-title">{{ _lang('Distribution Details') }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ _lang('Date') }}</th>
                                <th>{{ _lang('Investment') }}</th>
                                <th>{{ _lang('Member') }}</th>
                                <th>{{ _lang('Account') }}</th>
                                <th>{{ _lang('Deposit Amount') }}</th>
                                <th>{{ _lang('Profit Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($distributionDetails as $detail)
                            <tr>
                                <td>{{ optional($detail->distribution->distribution_date)->format('Y-m-d H:i') }}</td>
                                <td>{{ optional($detail->distribution->investment)->name }}</td>
                                <td>{{ $detail->member->name }}</td>
                                <td>{{ $detail->account->account_number }}</td>
                                <td>{{ decimalPlace($detail->deposit_amount, currency()) }}</td>
                                <td>{{ decimalPlace($detail->profit_amount, currency()) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ _lang('No Data Found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script>
(function ($) {
    "use strict";

    function updateProfitInfo() {
        var selectedOption = $('#investment_id').find(':selected');
        var netProfit = parseFloat(selectedOption.data('net-profit') || 0);
        var availableProfit = parseFloat(selectedOption.data('available-profit') || 0);
        var isDistributed = parseInt(selectedOption.data('distributed') || 0, 10) === 1;
        var profitAmount = parseFloat($('#total_profit').val() || 0);

        $('#net_profit_display').text('{{ currency() }}' + netProfit.toFixed(2));
        $('#available_profit_display').text('{{ currency() }}' + availableProfit.toFixed(2));
        $('#total_profit').val(availableProfit);
        $('#distributed_status').text(isDistributed ? '{{ _lang('Yes') }}' : '{{ _lang('No') }}');

        $('#profit_distribution_warning').toggleClass('d-none', !(profitAmount > availableProfit || isDistributed));
    }

    $(document).on('change', '#investment_id', updateProfitInfo);
    $(document).on('input', '#total_profit', updateProfitInfo);

    updateProfitInfo();
    $('[data-toggle="tooltip"]').tooltip();
})(jQuery);
</script>
@endsection
