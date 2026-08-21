@extends('layouts.default')

    @section('meta')
        <title>Schedules |Jpingos</title>
        <meta name="description" content="Workday schedules, view all employee schedules, add schedule or shift, edit, and delete schedules">
    @endsection

    @section('styles')
    <link href="{{ asset('/assets/vendor/mdtimepicker/mdtimepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/assets/vendor/air-datepicker/dist/css/datepicker.min.css') }}" rel="stylesheet">
    <style>
        /* .ui.active.modal {position: relative !important;} */
        .datepicker {z-index: 999 !important;}
        .datepickers-container {z-index: 9999 !important;}
    </style>
    @endsection

    @section('content')
    @include('admin.modals.modal-add-schedule')
    
    <div class="container-fluid">
        <div class="row">
            <h2 class="page-title">{{ __('Schedules') }}
                <button class="ui positive button mini offsettop5 btn-add float-right"><i class="ui icon plus"></i>{{ __('Add') }}</button>
            </h2>
        </div>

        <div class="row">
            <div class="box box-success">
                <div class="box-body">
                    <table width="100%" class="table table-striped table-hover" id="dataTables-example" data-order='[[ 6, "dec" ]]'>
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Hours') }}</th>
                                <th>{{ __('Rest Days') }}</th>
                                <th>{{ __('From (Date)') }}</th>
                                <th>{{ __('To (Date)') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($schedules)
                            @foreach ($schedules as $sched)
                            <tr>
                                <td>{{ $sched->employee }}</td>
                                
                                <td>{{ $sched->hours }} hr</td>
                                <td>{{ $sched->restday }}</td>
                                <td>@php echo e(date('D, M d, Y', strtotime($sched->datefrom))) @endphp</td>
                                <td>@php echo e(date('D, M d, Y', strtotime($sched->dateto))) @endphp</td>
                                <td>
                                    @if($sched->archive == '0') 
                                        <span class="green">{{ __('Present') }}</span>
                                    @else
                                        <span class="teal">{{ __('Previous') }}</span>
                                    @endif
                                </td>
                                <td class="align-right">
                                    @if($sched->archive == '0') 
                                    <a href="{{ url('/schedules/pdf/'.$sched->id) }}" class="ui red circular icon button tiny" title="Download PDF">
    <i class="file pdf icon"></i>
</a>
<a href="javascript:void(0)" 
   class="ui blue circular icon button tiny btn-weekly" 
   data-id="{{ $sched->id }}" 
   data-restday="{{ $sched->restday }}" 
   data-hours="{{ $sched->hours }}"
   title="Weekly Rota">
    <i class="calendar alternate outline icon"></i>
</a>
                                        <a href="{{ url('/schedules/edit/'.$sched->id) }}" class="ui circular basic icon button tiny"><i class="icon edit outline"></i></a>
                                        <a href="{{ url('/schedules/delete/'.$sched->id) }}" class="ui circular basic icon button tiny"><i class="icon trash alternate outline"></i></a>
                                        <a href="{{ url('/schedules/archive/'.$sched->id) }}" class="ui circular basic icon button tiny"><i class="icon archive"></i></a>
                                    @else
                                        <a href="{{ url('/schedules/delete/'.$sched->id) }}" class="ui circular basic icon button tiny"><i class="icon trash alternate outline"></i></a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            @endisset
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
<div class="ui modal weekly small">
    <div class="header">
        <i class="calendar alternate outline icon"></i>
        Weekly Rota Setup
    </div>

    <div class="content">
        <form id="weekly_shift_form" class="ui form" method="POST" action="{{ url('schedules/weekly') }}">
            @csrf

            <!-- ðŸ”¥ hidden schedule id -->
            <input type="hidden" name="schedule_id" id="schedule_id">

           

            <!-- Rest Days -->
            <div class="grouped fields">
                <label>Select Rest Days</label>

                <div class="inline fields">
                    @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                    <div class="field">
                        <div class="ui checkbox">
                            <input type="checkbox" class="restday" value="{{ $day }}">
                            <label>{{ $day }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="ui divider"></div>

            <!-- Weekly Grid -->
            <div class="ui grid">
                @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                <div class="eight wide column day-row" data-day="{{ $day }}">
                    <div class="ui segment">
                        <strong>{{ $day }}</strong>

                        <div class="two fields mt-1">
                            <div class="field">
                                <input type="text" name="shift[{{ $day }}][in]" class="jtimepicker" placeholder="Time In">
                            </div>
                            <div class="field">
                                <input type="text" name="shift[{{ $day }}][out]" class="jtimepicker" placeholder="Time Out">
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
 <div class="ui message mt-2">
                <strong>Total Hours:</strong>
                <span id="total_hours">0</span> hrs
            </div>

            <!-- ✅ WARNING -->
            <div class="ui red message hidden" id="hours_warning"></div>

        </form>
    </div>

    <div class="actions">
        <button type="submit" form="weekly_shift_form" class="ui green button">
            <i class="check icon"></i> Save
        </button>
        <button class="ui grey button cancel">
            Cancel
        </button>
    </div>
    
</div>
    @endsection
    
    @section('scripts')
    <script src="{{ asset('/assets/vendor/air-datepicker/dist/js/datepicker.min.js') }}"></script>
    <script src="{{ asset('/assets/vendor/air-datepicker/dist/js/i18n/datepicker.en.js') }}"></script>
    <script src="{{ asset('/assets/vendor/mdtimepicker/mdtimepicker.min.js') }}"></script>

    <script type="text/javascript">
    $('#dataTables-example').DataTable({responsive: true,pageLength: 15,lengthChange: true,searching: true,ordering: true});
    $('.airdatepicker').datepicker({ language: 'en', dateFormat: 'yyyy-mm-dd' });

    @isset($tf)
        @if($tf == 1)
            $('.jtimepicker').mdtimepicker({format:'h:mm tt', theme: 'blue', hourPadding: true});
        @else
            $('.jtimepicker').mdtimepicker({format:'hh:mm', theme: 'blue', hourPadding: true});
        @endif
    @endisset

    $('.ui.dropdown.getid').dropdown({ onChange: function(value, text, $selectedItem) {
        $('select[name="employee"] option').each(function() {
            if($(this).val()==value) {var id = $(this).attr('data-id');$('input[name="id"]').val(id);};
        });
    }});
    
    let maxHours = 0;

    // OPEN MODAL + PASS ID
$('.btn-weekly').click(function () {
    let id = $(this).data('id');
    let restdays = $(this).data('restday'); // e.g "Monday, Tuesday"
maxHours = parseFloat($(this).data('hours')) || 0;
    $('#schedule_id').val(id);

    // reset form
    $('#weekly_shift_form')[0].reset();

    // show all first
    $('.day-row').show();
    $('.restday').prop('checked', false);

    // ðŸ”¥ HANDLE EXISTING REST DAYS
    if (restdays) {

        let daysArray = restdays.split(',');

        daysArray.forEach(function(day) {

            day = day.trim();

            // check checkbox
            $('.restday[value="'+day+'"]').prop('checked', true);

            // hide that day
            $('.day-row[data-day="'+day+'"]').hide();
        });
    }

    $('.ui.modal.weekly').modal('show');
    
     $.get('/schedules/weekly/' + id, function (res) {

        // =========================
        // REST DAYS
        // =========================
        if (res.restDays && res.restDays.length > 0) {

            res.restDays.forEach(function (day) {

                $('.restday[value="' + day + '"]').prop('checked', true);
                $('.day-row[data-day="' + day + '"]').hide();
            });
        }

        // =========================
        // SHIFT TIMES
        // =========================
        if (res.shifts) {

            $.each(res.shifts, function (day, shift) {

                let row = $('.day-row[data-day="' + day + '"]');

                row.find('input[name="shift[' + day + '][in]"]').val(shift.time_in);
                row.find('input[name="shift[' + day + '][out]"]').val(shift.time_out);
            });
        }
 setTimeout(calculateHours, 150);
    });


});

$(document).on('change', '.restday', function () {

    let day = $(this).val();

    if ($(this).is(':checked')) {
        $('.day-row[data-day="'+day+'"]').hide().find('input').val('');
    } else {
        $('.day-row[data-day="'+day+'"]').show();
    }

    calculateHours();
});


// =========================
// TIME INPUT CHANGE
// =========================
$(document).on('keyup change', '.jtimepicker', function () {
    calculateHours();
});


// =========================
// TIME PARSER
// =========================
function parseTime(str) {

    if (!str) return null;

    str = str.trim().toLowerCase();

    let pm = str.includes('pm');
    let am = str.includes('am');

    str = str.replace(/am|pm/g, '').trim();

    let parts = str.split(':');
    if (parts.length < 2) return null;

    let h = parseInt(parts[0]);
    let m = parseInt(parts[1]);

    if (isNaN(h) || isNaN(m)) return null;

    if (pm && h < 12) h += 12;
    if (am && h === 12) h = 0;

    return h * 60 + m;
}


// =========================
// CALCULATE HOURS
// =========================
function calculateHours() {

    let totalMinutes = 0;

    $('.day-row:visible').each(function () {

        let timeIn = $(this).find('[name*="[in]"]').val();
        let timeOut = $(this).find('[name*="[out]"]').val();

        let start = parseTime(timeIn);
        let end = parseTime(timeOut);

        if (start === null || end === null) return;

        // overnight shift
        if (end < start) {
            end += 1440;
        }

        let diff = end - start;

        if (diff > 0) {
            totalMinutes += diff;
        }
    });

    let totalHours = (totalMinutes / 60).toFixed(2);

    $('#total_hours').text(totalHours);

    // VALIDATION
    if (parseFloat(totalHours) > maxHours) {

        $('#hours_warning')
            .removeClass('hidden')
            .text(`Total hours (${totalHours}) exceed allowed (${maxHours})`);

        $('.ui.green.button').prop('disabled', true);

    } else {

        $('#hours_warning').addClass('hidden');
        $('.ui.green.button').prop('disabled', false);
    }
}
    </script>
    @endsection 