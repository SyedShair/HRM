@extends('layouts.default')
 @php
            // Branding: pulled from the Settings page (App name / logo).
            // Falls back to existing static defaults if nothing has been
            // configured yet, so this is safe even before anyone touches
            // the new fields.
            $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
            $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
            $appLogo = !empty($appSettings->app_logo)
                ? asset('storage/'.$appSettings->app_logo)
                : asset('/assets/images/img/logo.png');
        @endphp
    @section('meta')
        <title>Users | {{ $appName }}</title>
        <meta name="description" content="Workday users, view all users, add, edit, delete users">
    @endsection 
    @section('content')
    @include('admin.modals.modal-add-user')
    <div class="container-fluid">
        <div class="row">
            <h2 class="page-title">{{ __("Users") }}
            <button class="ui positive button mini offsettop5 btn-add float-right"><i class="ui icon plus"></i>{{ __("Add") }}</button>
            <a href="{{ url('users/roles') }}" class="ui blue button mini offsettop5 float-right"><i class="ui icon user"></i>{{ __("Roles") }}</a>
            </h2>
        </div>
        <div class="row">
            <div class="box box-success">
                <div class="box-body">
                    @if ($errors->any())
                    <div class="ui error message">
                        <i class="close icon"></i>
                        <div class="header">{{ __("There were some errors with your submission") }}</div>
                        <ul class="list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <table width="100%" class="table table-striped table-hover" id="dataTables-example" data-order='[[ 0, "asc" ]]'>
                        <thead>
                            <tr>
                                <th>{{ __("Name") }}</th>
                                <th>{{ __("Email") }}</th>
                                <th>{{ __("Role") }}</th>
                                <th>{{ __("Type") }}</th>
                                <th>{{ __("Status") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                           @isset($users_roles)
                            @foreach ($users_roles as $val)
                            <tr>
                                <td>{{ $val->name }}</td>
                                <td>{{ $val->email }}</td>
                                <td>{{ $val->role_name }}</td>
                                <td> @if($val->acc_type == 2) Admin @else Employee @endif </td>
                                <td>
                                    <span>
                                    @if($val->status == '1') 
                                        Enabled
                                    @else
                                        Disabled
                                    @endif
                                    </span>
                                </td>
                                <td class="align-right">
                                    <a href="{{ url('/users/edit/'.$val->id) }}" class="ui circular basic icon button tiny"><i class="icon edit outline"></i></a>
                                    <a href="{{ url('/users/delete/'.$val->id) }}" class="ui circular basic icon button tiny"><i class="icon trash alternate outline"></i></a>
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
    @endsection
    @section('scripts')
    <script type="text/javascript">
    // Wrapped in $(document).ready() so this always runs after Semantic UI /
    // jQuery / the base layout are fully parsed and ready — matches how the
    // rest of the app's page scripts behave, and is safe regardless of
    // where @yield('scripts') ends up in the layout.
    $(document).ready(function () {
        $('#dataTables-example').DataTable({
            responsive: true,
            pageLength: 15,
            lengthChange: false,
            searching: true,
            ordering: true
        });

        // NOTE: this dropdown must carry the "no-global-init" class (see the
        // modal markup) so the layout's global
        //   $('.ui.dropdown').not('.no-global-init').dropdown({...})
        // skips it. Without that class, the layout's generic re-init runs
        // AFTER this one (it's registered earlier in the document, and
        // jQuery fires $(document).ready() callbacks in registration order)
        // and silently overwrites this onChange handler — which is why the
        // Email field could stop populating even though this code looks
        // correct on its own.
        $('.ui.dropdown.getemail').dropdown({
            onChange: function (value, text, $selectedItem) {
                $('select[name="name"] option').each(function () {
                    if ($(this).val() == value) {
                        var e = $(this).attr('data-e');
                        var r = $(this).attr('data-ref');
                        $('input[name="email"]').val(e);
                        $('input[name="ref"]').val(r);
                    }
                });
            }
        });
    });
    </script>
    @endsection