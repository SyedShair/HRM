@extends('layouts.default')
    
    @section('meta')
        <title>Reports | Workday Time Clock</title>
        <meta name="description" content="Workday reports, view reports, and export or download reports">
    @endsection
    
    @section('styles')
        <link href="{{ asset('/assets/vendor/air-datepicker/dist/css/datepicker.min.css') }}" rel="stylesheet">
        <style>
            .table thead th {
                font-size: 12px; font-weight: 600; text-transform: uppercase;
                letter-spacing: 0.03em; color: #6b7280; background-color: #f9fafb;
                border-bottom: 2px solid #e5e7eb;
            }
            .table tbody tr { border-bottom: 1px solid #f1f1f1; }
            .table tbody tr:hover { background-color: #fafafa; }
        </style>
    @endsection

    @section('content')
    
    <div class="container-fluid">
        <div class="row">
            <h2 class="page-title">{{ __("Employee Leaves Report") }}
                <a href="{{ url('reports') }}" class="ui basic blue button mini offsettop5 float-right"><i class="ui icon chevron left"></i>{{ __("Return") }}</a>
            </h2>
        </div>

        <div class="row">
            <div class="box box-success">
                <div class="box-body reportstable">
                    <form action="{{ url('export/report/leaves') }}" method="post" accept-charset="utf-8" class="ui small form form-filter" id="filterform">
                        @csrf
                        <div class="inline three fields">
                            <div class="three wide field">
                                <select name="employee" class="ui search dropdown getid">
                                    <option value="">{{ __("Employee") }}</option>
                                    @isset($employee)
                                        @foreach($employee as $e)
                                            <option
                                                value="{{ $e->lastname }}, {{ $e->firstname }}"
                                                data-id="{{ $e->idno }}"
                                                data-name="{{ $e->firstname }} {{ $e->lastname }}"
                                            >{{ $e->lastname }}, {{ $e->firstname }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>

                            <div class="two wide field">
                                <input id="datefrom" type="text" name="datefrom" value="" placeholder="Start Date" class="airdatepicker">
                                <i class="ui icon calendar alternate outline calendar-icon"></i>
                            </div>

                            <div class="two wide field">
                                <input id="dateto" type="text" name="dateto" value="" placeholder="End Date" class="airdatepicker">
                                <i class="ui icon calendar alternate outline calendar-icon"></i>
                            </div>
                            <input type="hidden" name="emp_id" value="">
                            <input type="hidden" name="emp_name" value="">
                            <button id="btnfilter" class="ui icon button positive small inline-button"><i class="ui icon filter alternate"></i> {{ __("Filter") }}</button>
                            <button type="submit" name="submit" class="ui icon button blue small inline-button"><i class="ui icon download"></i> {{ __("Download CSV") }}</button>
                            <a href="#" id="btnpdf" target="_blank" class="ui icon button red small inline-button"><i class="ui icon file pdf"></i> {{ __("Download PDF") }}</a>
                        </div>
                    </form>
                    
                    <table width="100%" class="table table-striped table-hover" id="dataTables-example" data-order='[[ 0, "asc" ]]'>
                        <thead>
                            <tr>
                                <th>{{ __("Employee Name") }}</th>
                                <th>{{ __("Type") }}</th>
                                <th>{{ __("Leave from") }} <span class="help">(date)</span></th>
                                <th>{{ __("Leave to") }} <span class="help">(date)</span></th>
                                <th>{{ __("Reason") }}</th>
                                <th>{{ __("Status") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($empLeaves)
                            @foreach ($empLeaves as $v)
                                <tr>
                                    <td>{{ $v->employee }}</td>
                                    <td>{{ $v->type }}</td>
                                    <td>{{ $v->leavefrom }}</td>
                                    <td>{{ $v->leaveto }}</td>
                                    <td>{{ $v->reason }}</td>
                                    <td>{{ $v->status }}</td>
                                </tr>
                            @endforeach
                            @endisset
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    @endsection
    
    @section('scripts')
    <script src="{{ asset('/assets/vendor/air-datepicker/dist/js/datepicker.min.js') }}"></script>
    <script src="{{ asset('/assets/vendor/air-datepicker/dist/js/i18n/datepicker.en.js') }}"></script>

    <script type="text/javascript">
    $('#dataTables-example').DataTable({responsive: true,pageLength: 15,lengthChange: false,searching: false,ordering: true});

    // FIX: Semantic UI's dropdown module rebuilds its own menu items from
    // the <select>'s <option> tags, and those regenerated items do NOT
    // reliably carry custom data-* attributes (data-id, data-name) - only
    // value/text. The original code also tried working around this by
    // looping through the raw <option> elements and string-matching
    // .val() against the value Semantic UI reported, which is fragile
    // (partial/typed search text, case/whitespace) and was leaving
    // emp_id unset (confirmed: filter payload showed "id": null even
    // with an employee visibly selected).
    //
    // Semantic UI DOES keep the underlying native <select> in sync and
    // fires a real 'change' event on it, and that native <select> still
    // has the original <option> elements with every data-* attribute
    // intact - so read directly from there instead.
    $('.ui.dropdown.getid').dropdown();

    $('select[name="employee"]').on('change', function () {
        var $opt = $(this).find('option:selected');
        var id = $opt.attr('data-id') || '';
        var empName = $opt.attr('data-name') || '';

        // emp_name rides alongside emp_id as a fallback match key, in
        // case idno linkage between tables can't be trusted server-side.
        $('input[name="emp_name"]').val(empName).trigger('change');
        $('input[name="emp_id"]').val(id).trigger('change');
    });

    $('.airdatepicker').datepicker({
        language: 'en',
        dateFormat: 'yyyy-mm-dd',
        onSelect: function () {
            $(this.el).trigger('change');
        }
    });

    function syncPdfLink() {
        var params = new URLSearchParams();
        var empId = $('input[name="emp_id"]').val();
        var empName = $('input[name="emp_name"]').val();
        var from = $('#datefrom').val();
        var to = $('#dateto').val();
        if (empId) params.set('id', empId);
        if (empName) params.set('name', empName);
        if (from) params.set('datefrom', from);
        if (to) params.set('dateto', to);
        $('#btnpdf').attr('href', $("#_url").val() + '/reports/pdf/leaves?' + params.toString());
    }
    syncPdfLink();

    // Every filter field change re-syncs the PDF link AND re-queries the
    // table, so selecting an employee or picking/typing a date actually
    // updates the results immediately - not just on explicit "Filter".
    $(document).on('change', 'input[name="emp_id"], input[name="emp_name"], #datefrom, #dateto', function () {
        syncPdfLink();
        runFilter();
    });

    function runFilter() {
        var emp_id = $('input[name="emp_id"]').val();
        var emp_name = $('input[name="emp_name"]').val();
        var date_from = $('#datefrom').val();
        var date_to = $('#dateto').val();
        var url = $("#_url").val();

        $.ajax({
            url: url + '/get/employee-leaves/',
            type: 'get',
            dataType: 'json',
            data: {
                id: emp_id,
                name: emp_name,
                datefrom: date_from,
                dateto: date_to
            },
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showdata(response);
            },
            error: function(xhr) {
                $.notify({
                    icon: 'ui icon times',
                    message: 'Could not load the leaves report. Please try again.'
                }, { type: 'danger', timer: 500 });
            }
        });
    }

    $('#btnfilter').click(function(event) {
        event.preventDefault();
        runFilter();
    });

    function showdata(leaves) {
        var tbody = $('#dataTables-example tbody');

        $('#dataTables-example').DataTable().destroy();
        tbody.children('tr').remove();

        for (var i = 0; i < leaves.length; i++) {
            tbody.append("<tr>"+ "<td>"+leaves[i].employee+"</td>" + "<td>"+leaves[i].type+"</td>" + "<td>"+leaves[i].leavefrom+"</td>" + "<td>"+leaves[i].leaveto+"</td>" + "<td>"+leaves[i].reason+"</td>" + "<td>"+leaves[i].status+"</td>" + "</tr>");
        }

        $('#dataTables-example').DataTable({responsive: true,pageLength: 15,lengthChange: false,searching: false,ordering: true});
    }
    </script>
    @endsection