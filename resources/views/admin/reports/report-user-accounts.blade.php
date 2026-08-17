@extends('layouts.default')

    @section('meta')
        <title>Reports | Workday Time Clock</title>
        <meta name="description" content="Workday reports, view reports, and export or download reports.">
    @endsection

    @section('styles')
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
            <h2 class="page-title">{{ __("User Accounts Report") }}
                <a href="{{ url('reports/pdf/accounts') }}" target="_blank" class="ui basic red button mini offsettop5 float-right"><i class="ui icon file pdf"></i>{{ __("Download PDF") }}</a>
                <a href="{{ url('export/report/accounts') }}" class="ui basic button mini offsettop5 btn-export float-right"><i class="ui icon download"></i>{{ __("Export to CSV") }}</a>
                <a href="{{ url('reports') }}" class="ui basic blue button mini offsettop5 float-right"><i class="ui icon chevron left"></i>{{ __("Return") }}</a>
            </h2> 
        </div>

        <div class="row">
            <div class="box box-success">
                <div class="box-body">
                    <table width="100%" class="table table-striped table-hover" id="dataTables-example" data-order='[[ 0, "asc" ]]'>
                        <thead>
                            <tr>
                                <th>{{ __("Employee Name") }}</th>
                                <th>{{ __("Email") }}</th>
                                <th>{{ __("Account Type") }}</th>
                                <th>{{ __("Status") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($userAccs)
                                @foreach ($userAccs as $v)
                                    <tr>
                                        <td>{{ $v->name }}</td>
                                        <td>{{ $v->email }}</td>
                                        <td>@if( $v->acc_type == 2) Admin @else Employee @endif</td>
                                        <td>@if($v->status == 1) Active @endif @if($v->status == 0) Disabled @endif</td>
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
    $('#dataTables-example').DataTable({responsive: true,pageLength: 15,lengthChange: false,searching: true,ordering: true});
    </script>
    @endsection