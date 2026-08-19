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
    <title>Daily Salaries | {{ $appName }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

@endsection

@section('content')
<div class="container-fluid">
    <h2 class="page-title uppercase">Daily Salaries</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Pay Salary Button -->
    <div class="mb-4">
        <button class="btn btn-success" type="button" data-bs-toggle="offcanvas" data-bs-target="#paySalarySidebar">
            Pay Salary
        </button>
    </div>

    <!-- Left Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="paySalarySidebar" aria-labelledby="paySalarySidebarLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="paySalarySidebarLabel">Update Salary Status</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <form action="{{ url('/salary/update-range') }}" method="POST">
            @csrf
            <div class="offcanvas-body">
                <div class="mb-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">Select Employee</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->lastname }}, {{ $employee->firstname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">From</label>
                    <input type="date" name="from_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">To</label>
                    <input type="date" name="to_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="Paid">Paid</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
            </div>
            <div class="offcanvas-footer p-3 border-top">
                <button type="submit" class="btn btn-primary">Update Salary</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ url('/dailysalary') }}" class="form-inline mb-3">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="form-group mr-2">
            <label class="mr-2">Employee Filter:</label>
            <select name="employee_id" class="form-control">
                <option value="">All</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" {{ $request->employee_id == $employee->id ? 'selected' : '' }}>
                        {{ $employee->lastname }}, {{ $employee->firstname }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group mr-2">
            <label class="mr-2">From:</label>
            <input type="date" name="from_date" class="form-control" value="{{ $request->from_date }}">
        </div>
        <div class="form-group mr-2">
            <label class="mr-2">To:</label>
            <input type="date" name="to_date" class="form-control" value="{{ $request->to_date }}">
        </div>
        <button type="submit" class="btn btn-info">Filter</button>
    </form>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $status === 'Pending' ? 'active' : '' }}"
               href="{{ url('dailysalary?status=Pending') }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'Paid' ? 'active' : '' }}"
               href="{{ url('dailysalary?status=Paid') }}">Paid</a>
        </li>
    </ul>

    <!-- Salary Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="dataTables-example">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Total Hours</th>
                    <th>Rate</th>
                    <th>Daily Salary</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php $i=1; @endphp
                @forelse ($salaries as $salary)
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $salary->lastname }}, {{ $salary->firstname }}</td>
                        <td>{{ $salary->date }}</td>
                        <td>{{ $salary->total_hours }}</td>
                        <td>£{{ number_format($salary->rate, 2) }}</td>
                        <td>£{{ number_format($salary->daily_salary, 2) }}</td>
                        <td>
                            <span class="badge badge-{{ $salary->status === 'Paid' ? 'success' : 'warning' }}">
                                {{ $salary->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No data found.</td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($salaries) > 0)
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right font-weight-bold">Total:</td>
                    <td class="font-weight-bold">£{{ number_format($total, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection

@section('scripts')
<!-- Bootstrap 5 Offcanvas JS (already included in bootstrap.bundle.min.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>                    $('#dataTables-example').DataTable({responsive: true,pageLength: 15,lengthChange: false,searching: true,ordering: true});
</script>
@endsection
