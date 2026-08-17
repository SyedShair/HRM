@extends('layouts.default')

    @section('meta')
        <title>Reports | Workday Time Clock</title>
        <meta name="description" content="Workday reports, view reports, and export or download reports.">
    @endsection

    @section('styles')
        <link href="{{ asset('/assets/vendor/air-datepicker/dist/css/datepicker.min.css') }}" rel="stylesheet">
        <style>
            .report-toolbar { display:flex; align-items:center; justify-content:flex-end; gap:8px; }
            .table thead th {
                font-size: 12px; font-weight: 600; text-transform: uppercase;
                letter-spacing: 0.03em; color: #6b7280; background-color: #f9fafb;
                border-bottom: 2px solid #e5e7eb;
            }
            .table tbody tr { border-bottom: 1px solid #f1f1f1; }
            .table tbody tr:hover { background-color: #fafafa; }
            .tablefooter td { background: #f9fafb; }

            #selected-employee-panel {
                display: none;
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                padding: 14px 18px;
                margin-bottom: 16px;
            }
            #selected-employee-panel.visible { display: block; }
            #selected-employee-panel .emp-name {
                font-size: 15px;
                font-weight: 700;
                color: #111827;
                margin-bottom: 6px;
            }
            #selected-employee-panel .emp-detail-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 6px 20px;
                font-size: 13px;
            }
            #selected-employee-panel .emp-detail-grid dt {
                color: #6b7280;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.03em;
            }
            #selected-employee-panel .emp-detail-grid dd {
                margin: 0;
                color: #111827;
            }

            #mark-attendance-panel {
                display: none;
                background: #fffbeb;
                border: 1px solid #fde68a;
                border-radius: 6px;
                padding: 14px 18px;
                margin-bottom: 16px;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
            }
            #mark-attendance-panel.visible { display: flex; }
            #mark-attendance-panel p {
                margin: 0;
                font-size: 13px;
                color: #92400e;
            }

            /* Preloader overlay - shown over the table while a filter or
               Mark Attendance request is in flight, so the admin gets
               feedback instead of staring at a table that looks frozen. */
            .reportstable {
                position: relative;
            }
            #report-preloader {
                display: none;
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(255, 255, 255, 0.75);
                z-index: 50;
                align-items: center;
                justify-content: center;
                border-radius: 6px;
            }
            #report-preloader.visible { display: flex; }
            #report-preloader .spinner {
                width: 36px;
                height: 36px;
                border: 4px solid #e5e7eb;
                border-top-color: #2185d0;
                border-radius: 50%;
                animation: report-preloader-spin 0.7s linear infinite;
            }
            @keyframes report-preloader-spin {
                to { transform: rotate(360deg); }
            }
        </style>
    @endsection

    @section('content')
    
    <div class="container-fluid">
        <div class="row">
            <h2 class="page-title">{{ __("Employee Attendance Report") }}
                <a href="{{ url('reports') }}" class="ui basic blue button mini offsettop5 float-right"><i class="ui icon chevron left"></i>{{ __("Return") }}</a>
            </h2> 
        </div>

        <div class="row">
            <div class="box box-success">
                <div class="box-body reportstable">
                    <div id="report-preloader"><div class="spinner"></div></div>

                    <form action="{{ url('export/report/attendance') }}" method="post" accept-charset="utf-8" class="ui small form form-filter" id="filterform">
                        @csrf
                        <div class="inline three fields">
                           

                            <div class="two wide field">
                                <input id="datefrom" type="text" name="datefrom" value="" placeholder="Start Date" class="airdatepicker">
                                <i class="ui icon calendar alternate outline calendar-icon"></i>
                            </div>

                            <div class="two wide field">
                                <input id="dateto" type="text" name="dateto" value="" placeholder="End Date" class="airdatepicker">
                                <i class="ui icon calendar alternate outline calendar-icon"></i>
                            </div>
                            <div class="three wide field">
                                <select name="employee" class="ui search dropdown getid">
                                    <option value="">{{ __("Employee") }}</option>
                                    @isset($employee)
                                        @foreach($employee as $e)
                                            <option
                                                value="{{ $e->lastname }}, {{ $e->firstname }}"
                                                data-id="{{ $e->idno }}"
                                                data-name="{{ $e->firstname }} {{ $e->lastname }}"
                                                data-department="{{ $e->department }}"
                                                data-jobposition="{{ $e->jobposition }}"
                                                data-company="{{ $e->company }}"
                                                data-pay="{{ $e->perhourpay }}"
                                            >{{ $e->lastname }}, {{ $e->firstname }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <input type="hidden" name="emp_id" value="">
                            <input type="hidden" name="emp_name" value="">
                            <button id="btnfilter" class="ui icon button positive small inline-button"><i class="ui icon filter alternate"></i> {{ __("Filter") }}</button>
                            <button type="submit" name="submit" class="ui icon button blue small inline-button"><i class="ui icon download"></i> {{ __("Download CSV") }}</button>
                            <a href="#" id="btnpdf" target="_blank" class="ui icon button red small inline-button"><i class="ui icon file pdf"></i> {{ __("Download PDF") }}</a>
                        </div>
                    </form>

                    {{-- Populated whenever a specific employee is selected in the
                         filter above - shows their department/job title/company
                         so the report is self-contained, not just a name + rows. --}}
                    <div id="selected-employee-panel">
                        <div class="emp-name" id="sep-name"></div>
                        <dl class="emp-detail-grid">
                            <div><dt>{{ __('ID No') }}</dt><dd id="sep-idno"></dd></div>
                            <div><dt>{{ __('Company') }}</dt><dd id="sep-company"></dd></div>
                            <div><dt>{{ __('Department') }}</dt><dd id="sep-department"></dd></div>
                            <div><dt>{{ __('Job Title') }}</dt><dd id="sep-jobposition"></dd></div>
                        </dl>
                    </div>

                    {{-- Shown only when a specific employee + full date range is
                         selected and the filtered result has no attendance rows -
                         lets the admin fill in the missing days in one click
                         instead of adding each one manually. --}}
                    <div id="mark-attendance-panel">
                        <p>{{ __('No attendance records found for this employee in the selected date range.') }}</p>
                        <button type="button" id="btnmarkattendance" class="ui icon button positive small">
                            <i class="ui icon check"></i> {{ __('Mark Attendance') }}
                        </button>
                    </div>

                    <table width="100%" class="table table-striped table-hover" id="dataTables-example">
                        <thead>
                            <tr>
                                <th>{{ __("Date") }}</th>
                                <th>{{ __("Employee Name") }}</th>
                                <th>{{ __("Time In") }}</th>
                                <th>{{ __("Time Out") }}</th>
                                <th>{{ __("Total Hours") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                          @isset($empAtten)
@foreach ($empAtten as $v)
<tr>
    <td>{{ $v->date }}</td>
    <td>{{ $v->employee }}</td>

    {{-- Always plain 24-hour "HH:MM" - never AM/PM. --}}
    <td>{{ $v->timein ? date('H:i', strtotime($v->timein)) : '' }}</td>
    <td>{{ $v->timeout ? date('H:i', strtotime($v->timeout)) : '' }}</td>

    <td>
        @if(!empty($v->timein) && !empty($v->timeout))
            @php
                $timeIn = strtotime($v->timein);
                $timeOut = strtotime($v->timeout);

                if ($timeOut < $timeIn) {
                    $timeOut += 86400;
                }

                $decimalHours = ($timeOut - $timeIn) / 3600;
            @endphp

            {{ number_format($decimalHours, 2) }} hrs
        @endif
    </td>
</tr>
@endforeach
@endisset
                        </tbody>
                        {{-- Summary row lives in a REAL tfoot, entirely separate
                             from DataTables' data rows. It used to be appended
                             into <tbody> with colspan cells (2 real <td> standing
                             in for 5 columns) - that mismatched DataTables'
                             expected per-row cell count, and combined with a
                             destroy()+reinit() on every filter, left the plugin
                             in a broken state after the first refresh (the
                             table would populate once, then stop responding to
                             later filters). Keeping it in tfoot means it's never
                             touched by clear()/rows.add()/draw() at all. --}}
                        <tfoot>
                            <tr class="tablefooter">
                                <td colspan="4">
                                    <strong>{{ __('TOTAL HOURS') }}</strong>
                                    <div class="pay-summary-row" style="display:none;"><br><strong>{{ __('Per Hour Pay') }}</strong></div>
                                    <div class="pay-summary-row" style="display:none;"><br><strong>{{ __('Total Pay') }}</strong></div>
                                </td>
                                <td>
                                    <strong id="tf-totalhours">0.00</strong>
                                    <div class="pay-summary-row" style="display:none;"><br><strong id="tf-payrate"></strong></div>
                                    <div class="pay-summary-row" style="display:none;"><br><strong id="tf-totalpay"></strong></div>
                                </td>
                            </tr>
                        </tfoot>
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
    // Initialized ONCE. Every filter after this reuses the same instance
    // via clear()/rows.add()/draw() instead of destroying and rebuilding
    // the whole plugin - that destroy/reinit cycle (combined with the old
    // colspan summary row inside <tbody>) is what made filtering work the
    // first time and then silently stop working on every filter after.
    var attendanceTable = $('#dataTables-example').DataTable({
        responsive: true,
        pageLength: 15,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [[0, 'desc']]
    });

    // transfer idno + show the selected employee's details panel + run the
    // filter immediately. Previously, picking a name only set the hidden
    // emp_id field - the table itself never re-queried until "Filter" was
    // clicked separately, so selecting someone appeared to do nothing (the
    // page-load table always shows every employee's attendance).
    // FIX: previously this re-searched `<option>` elements for one whose
    // .val() string-matched the value Semantic UI passed back, then read
    // data-id off THAT match. That match could silently fail (typed/partial
    // search text, case/whitespace differences, etc.), leaving emp_id
    // unset - which is exactly why the table kept showing every employee
    // even though the box displayed a name. $selectedItem IS the actual
    // selected <option> element (Semantic UI operates directly on a native
    // <select>'s options), so its data-* attributes can be read straight
    // off it with no re-matching step to fail.
    // FIX: Semantic UI's dropdown module rebuilds its own menu items from
    // the <select>'s <option> tags, and those regenerated items do NOT
    // carry over custom data-* attributes (data-id, data-name, etc.) -
    // only value/text. That's why $selectedItem.attr('data-id') was
    // reading as undefined and emp_id/emp_name never got populated, even
    // though picking a name visibly changed the dropdown's display text.
    //
    // Semantic UI DOES keep the underlying native <select> in sync and
    // fires a real 'change' event on it - and that native <select> still
    // has the original <option> elements with every data-* attribute
    // intact. So we read directly from there instead of trusting
    // $selectedItem.
    $('.ui.dropdown.getid').dropdown();

    $('select[name="employee"]').on('change', function () {
        var $opt = $(this).find('option:selected');
        var id = $opt.attr('data-id') || '';
        var empName = $opt.attr('data-name') || '';

        // emp_name rides alongside emp_id as a fallback match key - the
        // backend uses it when idno linkage between tables can't be
        // trusted, so selecting an employee still filters correctly.
        $('input[name="emp_name"]').val(empName);
        $('input[name="emp_id"]').val(id).trigger('change');

        if (id) {
            showSelectedEmployeePanel({
                name: empName,
                idno: id,
                company: $opt.attr('data-company'),
                department: $opt.attr('data-department'),
                jobposition: $opt.attr('data-jobposition')
            });
        } else {
            $('input[name="emp_name"]').val('');
            hideSelectedEmployeePanel();
        }
    });

    function showSelectedEmployeePanel(emp) {
        $('#sep-name').text(emp.name || '');
        $('#sep-idno').text(emp.idno || '');
        $('#sep-company').text(emp.company || '—');
        $('#sep-department').text(emp.department || '—');
        $('#sep-jobposition').text(emp.jobposition || '—');
        $('#selected-employee-panel').addClass('visible');
    }

    function hideSelectedEmployeePanel() {
        $('#selected-employee-panel').removeClass('visible');
    }

    function showMarkAttendancePanel() {
        $('#mark-attendance-panel').addClass('visible');
    }

    function hideMarkAttendancePanel() {
        $('#mark-attendance-panel').removeClass('visible');
    }

    function showPreloader() {
        $('#report-preloader').addClass('visible');
    }

    function hidePreloader() {
        $('#report-preloader').removeClass('visible');
    }

    // Keep the PDF link's query string in sync with the current filter
    // fields at all times, so clicking it always reflects whatever is
    // currently in the Employee/Start Date/End Date fields - even if
    // "Filter" was never clicked.
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
        $('#btnpdf').attr('href', $("#_url").val() + '/reports/pdf/attendance?' + params.toString());
    }
    syncPdfLink();

    // FIX: this single 'change' handler is now the ONE source of truth for
    // "a filter field changed" - it fires for the hidden emp_id input
    // (triggered manually above after the dropdown sets it) AND for the
    // date inputs, no matter whether the date got there via the calendar
    // popup, manual typing, or being cleared. Previously runFilter() was
    // only wired to the datepicker's onSelect callback, so typing a date
    // by hand (or any other non-popup change) updated the PDF link but
    // left the table showing stale/unfiltered data - which is why the
    // table could show every employee/date even while the filter row
    // displayed a specific employee and date range.
    $(document).on('change', 'input[name="emp_id"], input[name="emp_name"], #datefrom, #dateto', function () {
        syncPdfLink();
        runFilter();
    });

    // Shared by: selecting an employee, picking either date, and clicking
    // "Filter" explicitly - all three now actually re-query the table
    // instead of only the button doing so.
    function runFilter() {
        var emp_id = $('input[name="emp_id"]').val();
        var emp_name = $('input[name="emp_name"]').val();
        var date_from = $('#datefrom').val();
        var date_to = $('#dateto').val();
        var url = $("#_url").val();

        showPreloader();

        $.ajax({
            url: url + '/get/employee-attendance/',
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
                    message: 'Could not load the attendance report. Please try again.'
                }, { type: 'danger', timer: 500 });
            },

            complete: function() {
                hidePreloader();
            }
        });
    }

    // "Filter" still works as an explicit trigger (useful once someone's
    // finished picking both dates), and selecting an employee or either
    // date now also runs the same filter immediately on their own.
    $('#btnfilter').click(function(event) {
        event.preventDefault();
        runFilter();
    });

    $('.airdatepicker').datepicker({
        language: 'en',
        dateFormat: 'yyyy-mm-dd',
        onSelect: function () {
            // Route through the shared 'change' handler above instead of
            // calling runFilter() directly, so calendar picks and manual
            // edits go through exactly one code path.
            $(this.el).trigger('change');
        }
    });

    function showdata(response) {

        var rows = response.rows || [];
        var totalHours = response.totalHours || 0;
        var selectedEmployee = response.selectedEmployee || null;

        if (selectedEmployee) {
            showSelectedEmployeePanel({
                name: selectedEmployee.name,
                idno: selectedEmployee.idno,
                company: selectedEmployee.company,
                department: selectedEmployee.department,
                jobposition: selectedEmployee.jobposition
            });
        } else {
            hideSelectedEmployeePanel();
        }

        // Only offer "Mark Attendance" once someone has narrowed the
        // report down to one specific employee AND a full date range -
        // marking a range for "everyone" or an open-ended range isn't
        // well-defined, so the button stays hidden until both are set.
        var hasFullRangeSelected = !!selectedEmployee && !!$('#datefrom').val() && !!$('#dateto').val();
        if (hasFullRangeSelected && rows.length === 0) {
            showMarkAttendancePanel();
        } else {
            hideMarkAttendancePanel();
        }

        // Rebuild the table's DATA rows only, via the DataTables API -
        // no destroy(), no reinit(). This is the supported way to refresh
        // a DataTable's contents and is what keeps every filter after the
        // first one working.
        var tableRows = rows.map(function (row) {
            return [
                row.date,
                row.employee,
                // Server already formats these as plain 24-hour "HH:MM" -
                // no client-side re-parsing/splitting of raw datetime
                // strings, which is what previously made this drift out
                // of sync with the initial page load's formatting.
                row.timein_display || "",
                row.timeout_display || "",
                row.hours !== null ? row.hours.toFixed(2) + " hrs" : ""
            ];
        });

        attendanceTable.clear();
        attendanceTable.rows.add(tableRows);
        attendanceTable.draw();

        // Summary row - lives in <tfoot>, untouched by clear()/rows.add(),
        // so it's just updated directly here every time.
        var singleEmployeeFiltered = !!selectedEmployee;
        var pay = selectedEmployee ? (selectedEmployee.perhourpay || 0) : 0;

        $('#tf-totalhours').text(totalHours.toFixed(2));

        if (singleEmployeeFiltered) {
            var total_pay = (totalHours * pay).toFixed(2);
            $('#tf-payrate').text('£' + pay);
            $('#tf-totalpay').text('£' + total_pay);
            $('.pay-summary-row').show();
        } else {
            $('.pay-summary-row').hide();
        }
    }

    // Bulk-fills missing attendance for the currently selected employee
    // across the currently selected date range, using their schedule's
    // in/out times - the same logic the live clock system uses to decide
    // "In Time"/"Late In" etc. After it finishes, the filter re-runs so
    // the table (and this button, once rows exist) reflects the result.
    $('#btnmarkattendance').on('click', function () {
        var $btn = $(this);
        var emp_id = $('input[name="emp_id"]').val();
        var date_from = $('#datefrom').val();
        var date_to = $('#dateto').val();
        var url = $("#_url").val();

        if (!emp_id || !date_from || !date_to) {
            return;
        }

        $btn.prop('disabled', true).addClass('loading');
        showPreloader();

        $.ajax({
            url: url + '/attendance/mark-range',
            type: 'post',
            dataType: 'json',
            data: {
                emp_id: emp_id,
                datefrom: date_from,
                dateto: date_to
            },
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                var created = response.created || 0;
                var skippedExisting = response.skippedExisting || 0;
                var skippedNoSchedule = response.skippedNoSchedule || 0;
                var skippedRestDay = response.skippedRestDay || 0;

                var message;
                if (created > 0) {
                    message = created + ' day(s) marked.';
                    if (skippedNoSchedule > 0) {
                        message += ' ' + skippedNoSchedule + ' day(s) skipped (no schedule set).';
                    }
                    if (skippedRestDay > 0) {
                        message += ' ' + skippedRestDay + ' day(s) skipped (rest day).';
                    }
                } else if (skippedNoSchedule > 0) {
                    message = 'Nothing marked - this employee has no active schedule to base times on.';
                } else if (skippedRestDay > 0) {
                    message = 'Nothing marked - every day in this range is a rest day for this employee.';
                } else {
                    message = 'Nothing to mark - all days in range already have records.';
                }

                $.notify({
                    icon: 'ui icon ' + (created > 0 ? 'check' : 'info'),
                    message: message
                }, { type: created > 0 ? 'success' : 'warning', timer: 900 });

                runFilter();
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Could not mark attendance. Please try again.';
                $.notify({
                    icon: 'ui icon times',
                    message: msg
                }, { type: 'danger', timer: 900 });
            },
            complete: function () {
                $btn.prop('disabled', false).removeClass('loading');
                // runFilter() (called on success above) shows/hides its own
                // preloader; this covers the error path, where runFilter()
                // never runs and the overlay would otherwise stay stuck.
                hidePreloader();
            }
        });
    });
    </script>
    @endsection