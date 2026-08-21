<div class="ui modal add medium">
    <div class="header">{{ __("Add New Schedule") }}</div>

    <form id="add_schedule_form" action="{{ url('schedules/add') }}" class="ui form" method="post" accept-charset="utf-8">
        @csrf

        <div class="content">
            <p style="color:#6b7280; font-size:13px; margin-bottom:16px;">
                {{ __('Set the date range, the company\'s open/close time, and this employee\'s rest days. Saving this will automatically build their full weekly rota — no need to fill it in day-by-day.') }}
            </p>

            <div class="field">
                <label>{{ __('Employee') }}</label>
                <select class="ui search dropdown getid uppercase" name="employee">
                    <option value="">Select Employee</option>
                    @isset($employee)
                        @foreach ($employee as $data)
                            <option value="{{ $data->lastname }}, {{ $data->firstname }}" data-id="{{ $data->id }}">{{ $data->lastname }}, {{ $data->firstname }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div class="two fields">
                <div class="field">
                    <label for="">{{ __('Company Open Time') }}</label>
                    <input type="text" placeholder="00:00:00 AM" name="intime" class="jtimepicker" />
                </div>
                <div class="field">
                    <label for="">{{ __('Company Close Time') }}</label>
                    <input type="text" placeholder="00:00:00 PM" name="outime" class="jtimepicker" />
                </div>
            </div>

            <div class="field">
                <label for="">{{ __('Schedule Valid From') }}</label>
                <input type="text" placeholder="Date" name="datefrom" id="datefrom" class="airdatepicker" />
            </div>
            <div class="field">
                <label for="">{{ __('Schedule Valid To') }}</label>
                <input type="text" placeholder="Date" name="dateto" id="dateto" class="airdatepicker" />
            </div>

            <div class="eight wide field">
                <label for="">{{ __('Total hours (per day)') }}</label>
                <input type="text" placeholder="0" name="hours" />
            </div>

            <div class="grouped fields field">
                <label>{{ __('Choose Rest days') }} <span style="font-weight:400; color:#6b7280;">({{ __('these days will be marked OFF in the weekly rota') }})</span></label>

                <div class="field">
                    <div class="ui checkbox sunday">
                        <input type="checkbox" name="restday[]" value="Sunday">
                        <label>{{ __('Sunday') }}</label>
                    </div>
                </div>
                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" name="restday[]" value="Monday">
                        <label>{{ __('Monday') }}</label>
                    </div>
                </div>
                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" name="restday[]" value="Tuesday">
                        <label>{{ __('Tuesday') }}</label>
                    </div>
                </div>
                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" name="restday[]" value="Wednesday">
                        <label>{{ __('Wednesday') }}</label>
                    </div>
                </div>
                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" name="restday[]" value="Thursday">
                        <label>{{ __('Thursday') }}</label>
                    </div>
                </div>
                <div class="field">
                    <div class="ui checkbox">
                        <input type="checkbox" name="restday[]" value="Friday">
                        <label>{{ __('Friday') }}</label>
                    </div>
                </div>
                <div class="field" style="padding:0">
                    <div class="ui checkbox saturday">
                        <input type="checkbox" name="restday[]" value="Saturday">
                        <label>{{ __('Saturday') }}</label>
                    </div>
                </div>

                <div class="ui error message">
                    <i class="close icon"></i>
                    <div class="header"></div>
                    <ul class="list">
                        <li class=""></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="actions">
            <input type="hidden" name="id" value="">
            <button class="ui positive small button" type="submit" name="submit"><i class="ui checkmark icon"></i> {{ __('Save & Build Weekly Rota') }}</button>
            <button class="ui grey small button cancel" type="button"><i class="ui times icon"></i> {{ __('Cancel') }}</button>
        </div>

    </form>
</div>