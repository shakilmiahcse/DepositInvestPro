@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <span class="header-title">{{ _lang('Monthly Deposit History') }}</span>
                <a href="{{ route('savings_accounts.show', $account->id) }}" class="btn btn-outline-primary btn-xs ml-auto">
                    <i class="ti-arrow-left"></i>&nbsp;{{ _lang('Back') }}
                </a>
            </div>

            <div class="card-body">
                <table class="table table-bordered mb-4">
                    <tr><td>{{ _lang('Account Number') }}</td><td>{{ $account->account_number }}</td></tr>
                    <tr><td>{{ _lang('Member') }}</td><td>{{ $account->member->first_name . ' ' . $account->member->last_name }}</td></tr>
                    <tr><td>{{ _lang('Account Type') }}</td><td>{{ $account->savings_type->name }}</td></tr>
                    <tr><td>{{ _lang('Currency') }}</td><td>{{ $account->savings_type->currency->name }}</td></tr>
                </table>

                <table id="monthly_deposits_table" class="table table-bordered">
                    <thead>
                        <tr>
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
@endsection

@section('js-script')
<script>
(function ($) {

    "use strict";

    var monthly_deposits_table = $('#monthly_deposits_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ url('admin/monthly_deposits/get_table_data/' . $account->id) }}',
        "columns" : [
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

    $(document).ajaxError(function () {
        Swal.fire({
            icon: 'error',
            text: '{{ _lang('Something went wrong, please try again') }}'
        });
    });

    $(document).on("ajax-submit", function () {
        monthly_deposits_table.draw();
    });

})(jQuery);
</script>
@endsection
