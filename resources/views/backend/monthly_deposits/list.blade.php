@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card no-export">
            <div class="card-header d-flex align-items-center">
                <span class="panel-title">{{ _lang('Monthly Deposits') }}</span>
            </div>
            <div class="card-body">
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
@endsection

@section('js-script')
<script>
(function ($) {

    "use strict";

    var monthly_deposits_table = $('#monthly_deposits_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ url('admin/monthly_deposits/get_table_data') }}',
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

    $(document).on('click', '.mark-paid', function (e) {
        e.preventDefault();
        var id = $(this).data('id');

        if (!confirm('{{ _lang('Mark this deposit as paid?') }}')) {
            return;
        }

        $.ajax({
            method: 'POST',
            url: '{{ url('admin/monthly_deposits') }}/' + id + '/mark_paid',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.result === 'success') {
                    monthly_deposits_table.draw();
                }
            }
        });
    });

    $(document).on("ajax-submit", function () {
        monthly_deposits_table.draw();
    });

})(jQuery);
</script>
@endsection
