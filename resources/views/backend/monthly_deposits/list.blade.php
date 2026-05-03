@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card no-export">
            <div class="card-header d-flex align-items-center">
                <span class="panel-title">{{ _lang('Monthly Deposits') }}</span>
                <div class="ml-auto d-flex flex-wrap justify-content-end">
                    <button type="button" class="btn btn-outline-primary btn-xs mr-2 mb-1" id="monthly_deposit_reminder_settings">
                        <i class="ti-settings"></i>&nbsp;{{ _lang('Reminder Settings') }}
                    </button>
                    <button type="button" class="btn btn-warning btn-xs mr-2 mb-1" id="send_bulk_reminder">
                        <i class="ti-email"></i>&nbsp;{{ _lang('Send Bulk Reminder') }}
                    </button>
                @if($hasMissingDeposits)
                <form method="post" action="{{ route('monthly_deposits.generate') }}" class="generate-monthly-deposits-form mb-1">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-xs">
                        <i class="ti-reload"></i>&nbsp;{{ _lang('Generate Missing Deposits') }}
                    </button>
                </form>
                @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control auto-select filter-field" data-selected="" id="filter_status">
                            <option value="">{{ _lang('All Status') }}</option>
                            <option value="pending">{{ _lang('Pending') }}</option>
                            <option value="paid">{{ _lang('Paid') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control auto-select filter-field" data-selected="" id="filter_month">
                            <option value="">{{ _lang('All Months') }}</option>
                            @for($month = 1; $month <= 12; $month++)
                            <option value="{{ $month }}">{{ date('F', mktime(0, 0, 0, $month, 1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control auto-select filter-field" data-selected="" id="filter_year">
                            <option value="">{{ _lang('All Years') }}</option>
                            @foreach($availableYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-secondary btn-block" id="reset_filters">
                            <i class="ti-close"></i>&nbsp;{{ _lang('Reset Filters') }}
                        </button>
                    </div>
                </div>

                <table id="monthly_deposits_table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ _lang('Account') }}</th>
                            <th>{{ _lang('Member') }}</th>
                            <th>{{ _lang('Month') }}</th>
                            <th>{{ _lang('Year') }}</th>
                            <th>{{ _lang('Amount') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th class="text-center">{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reminderSettingsModal" tabindex="-1" role="dialog" aria-labelledby="reminderSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="monthly_deposit_reminder_settings_form" autocomplete="off">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="reminderSettingsModalLabel">{{ _lang('Reminder Settings') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ _lang('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Daily Scheduled Reminder') }}</label>
                                <select class="form-control" name="monthly_deposit_auto_reminder_enabled">
                                    <option value="1" {{ $reminderSettings['auto_enabled'] == '1' ? 'selected' : '' }}>{{ _lang('Enabled') }}</option>
                                    <option value="0" {{ $reminderSettings['auto_enabled'] != '1' ? 'selected' : '' }}>{{ _lang('Disabled') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Reminder Time') }}</label>
                                <input type="time" class="form-control" name="monthly_deposit_reminder_time" value="{{ $reminderSettings['time'] }}" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Recipient Mode') }}</label>
                                <select class="form-control" name="monthly_deposit_reminder_mode" id="monthly_deposit_reminder_mode">
                                    <option value="all_except" {{ $reminderSettings['mode'] == 'all_except' ? 'selected' : '' }}>{{ _lang('All Eligible Members') }}</option>
                                    <option value="selected_only" {{ $reminderSettings['mode'] == 'selected_only' ? 'selected' : '' }}>{{ _lang('Selected Members Only') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 reminder-selected-members">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Send Only To') }}</label>
                                <select class="form-control reminder-member-select" name="monthly_deposit_reminder_member_ids[]" data-placeholder="{{ _lang('Select Member') }}" multiple>
                                    @foreach($selectedReminderMembers as $member)
                                    <option value="{{ $member['id'] }}" selected>{{ $member['text'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 reminder-excluded-members">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Do Not Send To') }}</label>
                                <select class="form-control reminder-member-select" name="monthly_deposit_reminder_excluded_member_ids[]" data-placeholder="{{ _lang('Select Member') }}" multiple>
                                    @foreach($excludedReminderMembers as $member)
                                    <option value="{{ $member['id'] }}" selected>{{ $member['text'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ _lang('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="ti-check-box"></i>&nbsp;{{ _lang('Save Settings') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script>
(function ($) {

    "use strict";

    var monthly_deposits_table = $('#monthly_deposits_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ url('admin/monthly_deposits/get_table_data') }}',
            data: function (d) {
                d.status = $('#filter_status').val();
                d.month = $('#filter_month').val();
                d.year = $('#filter_year').val();
            }
        },
        "columns" : [
            { data : 'account.account_number', name : 'account.account_number', 'defaultContent': '' },
            { data : 'member.first_name', name : 'member.first_name', 'defaultContent': '' },
            { data : 'month', name : 'month' },
            { data : 'year', name : 'year' },
            { data : 'amount', name : 'amount' },
            { data : 'status', name : 'status' },
            { data : "action", name : "action" },
        ],
        responsive: true,
        "bStateSave": true,
        "bAutoWidth":false,
        "ordering": false,
        "language": {
           "decimal":        "",
           "emptyTable":     "{{ _lang('No Data Found') }}",
           "info":           "{{ _lang('Showing') }} _START_ {{ _lang('to') }} _END_ {{ _lang('of') }} _TOTAL_ {{ _lang('Entries') }}",
           "infoEmpty":      "{{ _lang('Showing 0 To 0 Of 0 Entries') }}",
           "infoFiltered":   "(filtered from _MAX_ total entries)",
           "infoPostFix":    "",
           "thousands":      ",",
           "lengthMenu":     "{{ _lang('Show') }} _MENU_ {{ _lang('Entries') }}",
           "loadingRecords": "{{ _lang('Loading...') }}",
           "processing":     "{{ _lang('Processing...') }}",
           "search":         "{{ _lang('Search') }}",
           "zeroRecords":    "{{ _lang('No matching records found') }}",
           "paginate": {
              "first":      "{{ _lang('First') }}",
              "last":       "{{ _lang('Last') }}",
              "previous":   "<i class='ti-angle-left'></i>",
              "next" :      "<i class='ti-angle-right'></i>",
          }
        },
        drawCallback: function () {
            $(".dataTables_paginate > .pagination").addClass("pagination-bordered");
        }
    });

    function toggleReminderRecipientFields() {
        if ($('#monthly_deposit_reminder_mode').val() === 'selected_only') {
            $('.reminder-selected-members').removeClass('d-none');
            $('.reminder-excluded-members').addClass('d-none');
        } else {
            $('.reminder-selected-members').addClass('d-none');
            $('.reminder-excluded-members').removeClass('d-none');
        }
    }

    toggleReminderRecipientFields();

    function initReminderMemberSelects() {
        if (!$.fn.select2) {
            return;
        }

        $('#reminderSettingsModal .reminder-member-select').each(function () {
            if ($(this).data('select2')) {
                return;
            }

            $(this).select2({
                width: '100%',
                dropdownParent: $('#reminderSettingsModal'),
                placeholder: $(this).data('placeholder'),
                allowClear: true,
                ajax: {
                    url: '{{ route('monthly_deposits.search_reminder_members') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term || ''
                        };
                    },
                    processResults: function (data) {
                        return data;
                    },
                    cache: true
                }
            });
        });
    }

    $('#reminderSettingsModal').on('shown.bs.modal', function () {
        initReminderMemberSelects();
    });

    $(document).on('change', '#monthly_deposit_reminder_mode', function () {
        toggleReminderRecipientFields();
    });

    $(document).on('click', '#monthly_deposit_reminder_settings', function (e) {
        e.preventDefault();
        $('#reminderSettingsModal').modal('show');
    });

    $(document).on('submit', '#monthly_deposit_reminder_settings_form', function (e) {
        e.preventDefault();
        var submitButton = $(this).find('button[type="submit"]');

        $.ajax({
            method: 'POST',
            url: '{{ route('monthly_deposits.reminder_settings') }}',
            data: $(this).serialize(),
            beforeSend: function () {
                submitButton.prop('disabled', true);
            },
            complete: function () {
                submitButton.prop('disabled', false);
            },
            success: function (response) {
                if (response.result === 'success') {
                    $('#reminderSettingsModal').modal('hide');
                }

                Swal.fire({
                    icon: response.result,
                    text: response.message,
                    timer: 1600,
                    showConfirmButton: false
                });
            }
        });
    });

    $(document).on('click', '#send_bulk_reminder', function (e) {
        e.preventDefault();
        var button = $(this);

        Swal.fire({
            title: '{{ _lang('Are you sure?') }}',
            text: '{{ _lang('Send reminder to all pending monthly deposit recipients?') }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ _lang('Yes, Send Reminder') }}',
            cancelButtonText: '{{ _lang('Cancel') }}',
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    method: 'POST',
                    url: '{{ route('monthly_deposits.bulk_remind') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        month: $('#filter_month').val(),
                        year: $('#filter_year').val()
                    },
                    beforeSend: function () {
                        button.prop('disabled', true);
                    },
                    complete: function () {
                        button.prop('disabled', false);
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: response.result,
                            text: response.message,
                            timer: 2200,
                            showConfirmButton: false
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.mark-paid', function (e) {
        e.preventDefault();
        var id = $(this).data('id');

        Swal.fire({
            title: '{{ _lang('Are you sure?') }}',
            text: '{{ _lang('Mark this deposit as paid?') }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '{{ _lang('Yes, Mark Paid') }}',
            cancelButtonText: '{{ _lang('Cancel') }}',
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    method: 'POST',
                    url: '{{ url('admin/monthly_deposits') }}/' + id + '/mark_paid',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.result === 'success') {
                            Swal.fire({
                                icon: 'success',
                                text: response.message,
                                timer: 1600,
                                showConfirmButton: false
                            });

                            monthly_deposits_table.draw();
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.send-reminder', function (e) {
        e.preventDefault();
        var id = $(this).data('id');

        Swal.fire({
            title: '{{ _lang('Are you sure?') }}',
            text: '{{ _lang('Send reminder for this pending deposit?') }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ _lang('Yes, Send Reminder') }}',
            cancelButtonText: '{{ _lang('Cancel') }}',
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    method: 'POST',
                    url: '{{ url('admin/monthly_deposits') }}/' + id + '/remind',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: response.result,
                            text: response.message,
                            timer: 1600,
                            showConfirmButton: false
                        });
                    }
                });
            }
        });
    });

    $(document).on('submit', '.generate-monthly-deposits-form', function (e) {
        e.preventDefault();

        var form = this;

        Swal.fire({
            title: '{{ _lang('Are you sure?') }}',
            text: '{{ _lang('Generate monthly deposits for') }} {{ $currentMonthLabel }}?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ _lang('Yes, Generate') }}',
            cancelButtonText: '{{ _lang('Cancel') }}',
        }).then(function (result) {
            if (result.value) {
                form.submit();
            }
        });
    });

    $(document).ajaxError(function () {
        Swal.fire({
            icon: 'error',
            text: '{{ _lang('Something went wrong, please try again') }}'
        });
    });

    $(document).on("ajax-submit", function () {
        monthly_deposits_table.draw();
    });

    $(document).on('change', '.filter-field', function () {
        monthly_deposits_table.draw();
    });

    $(document).on('click', '#reset_filters', function () {
        $('#filter_status').val('').trigger('change');
        $('#filter_month').val('').trigger('change');
        $('#filter_year').val('').trigger('change');
        monthly_deposits_table.draw();
    });

})(jQuery);
</script>
@endsection
