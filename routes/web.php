<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Converted for Laravel 12: all 'Controller@method' strings replaced with
| [Controller::class, 'method'] array syntax, and every controller is
| imported at the top with a `use` statement.
|
*/

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ClockController;
use App\Http\Controllers\DailyShiftController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\HomeController;

use App\Http\Controllers\Admin\SchedulesController;
use App\Http\Controllers\Admin\DashboardController;

use App\Http\Controllers\Admin\EmployeesController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\LeavesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\FieldsController;
use App\Http\Controllers\Admin\ExportsController;
use App\Http\Controllers\Admin\ImportsController;

use App\Http\Controllers\Personal\PersonalDashboardController;
use App\Http\Controllers\Personal\PersonalProfileController;
use App\Http\Controllers\Personal\PersonalAttendanceController;
use App\Http\Controllers\Personal\PersonalSchedulesController;
use App\Http\Controllers\Personal\PersonalLeavesController;
use App\Http\Controllers\Personal\PersonalSettingsController;
use App\Http\Controllers\Personal\PersonalAccountController;
use App\Http\Controllers\PayrollController;

use App\Http\Controllers\Auth\LoginController;

Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');

    return "OK";
});

Route::get('/mail-test', function () {
    Mail::raw("Test mail working", function ($message) {
        $message->to("shairsoftinnovations@gmail.com")
            ->subject("Test Mail");
    });

    return "Mail sent";
});

Route::get('/employee/{id}/print-pdf', [EmployeeDocumentController::class, 'printPdf'])->name('employee.print.pdf');

Route::get('schedules/pdf/{id}', [SchedulesController::class, 'pdf']);

Route::get('/chat', [ChatController::class, 'index'])->name('chat');
Route::get('/chat/user/{id}', [ChatController::class, 'chat']);
Route::get('/chat/messages/{id}', [ChatController::class, 'fetchMessages']);
Route::post('/chat/send', [ChatController::class, 'sendMessage']);
Route::post('/chat/read', [ChatController::class, 'markAsRead']);
Route::get('/chat/unread-count', [ChatController::class, 'unreadCount']);
Route::post('/chat/message/update/{id}', [ChatController::class, 'updateMessage']);
Route::post('/chat/message/delete/{id}', [ChatController::class, 'deleteMessage']);
Route::post('/chat/typing', [ChatController::class, 'typing']);
Route::get('/chat/typing/{id}', [ChatController::class, 'getTyping']);

// Add near your other authenticated routes in routes/web.php:



Route::group(['middleware' => 'auth'], function () {
Route::get('/payroll',                 [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/generate',       [PayrollController::class, 'generate'])->name('payroll.generate');
    Route::get('/payroll/{id}',            [PayrollController::class, 'show'])->name('payroll.show');
    Route::post('/payroll/{id}/status',    [PayrollController::class, 'updateStatus'])->name('payroll.status');
    Route::delete('/payroll/{id}',         [PayrollController::class, 'destroy'])->name('payroll.destroy');
    Route::group(['middleware' => 'checkstatus'], function () {

        Route::group(['middleware' => 'admin'], function () {

            Route::get('/contract/print/{id}', [ContractController::class, 'printContract'])->name('contract.print');

            Route::get('/employee/{id}/documents', [EmployeeDocumentController::class, 'index']);
            Route::post('/employee/document/upload', [EmployeeDocumentController::class, 'store']);
            Route::delete('/employee/document/{id}', [EmployeeDocumentController::class, 'destroy']);

            /*
            |--------------------------------------------------------------------------
            | Universal SmartClock
            |--------------------------------------------------------------------------
            */
            Route::get('clock', [ClockController::class, 'clock']);
            Route::post('attendance/add', [ClockController::class, 'add']);

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */
            Route::get('/', [DashboardController::class, 'index']);
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
          Route::get('dashboard/data', [DashboardController::class, 'ajaxData'])
    ->name('dashboard.ajaxData');
            /*
            |--------------------------------------------------------------------------
            | Employees
            |--------------------------------------------------------------------------
            */
            Route::get('employees', [EmployeesController::class, 'index'])->name('employees');
            Route::get('employees/new', [EmployeesController::class, 'new']);
            Route::post('employee/add', [EmployeesController::class, 'add']);
            Route::get('employees/filter', [EmployeesController::class, 'filterByCompany'])->name('employees.filter');
            /*
            |--------------------------------------------------------------------------
            | Employee Profile
            |--------------------------------------------------------------------------
            */
            Route::get('profile/view/{id}', [ProfileController::class, 'view']);
            Route::get('profile/delete/{id}', [ProfileController::class, 'delete']);
            Route::post('profile/delete/employee', [ProfileController::class, 'clear']);
            Route::get('profile/archive/{id}', [ProfileController::class, 'archive']);

            // Profile Info
            Route::get('profile/edit/{id}', [ProfileController::class, 'editPerson']);
            Route::post('profile/update', [ProfileController::class, 'updatePerson']);
            Route::get('/employee/{id}/print-profile', [ProfileController::class, 'printProfile'])->name('profile.print');
            /*
            |--------------------------------------------------------------------------
            | Daily salary
            |--------------------------------------------------------------------------
            */
            Route::get('dailysalary', [DailyShiftController::class, 'index']);
            Route::post('/salary/update-range', [DailyShiftController::class, 'updateRange']);

            /*
            |--------------------------------------------------------------------------
            | Employee Attendance
            |--------------------------------------------------------------------------
            */
            Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance');
            Route::get('attendance/edit/{id}', [AttendanceController::class, 'edit']);
            Route::get('attendance/delete/{id}', [AttendanceController::class, 'delete']);
            Route::post('attendance/update', [AttendanceController::class, 'update']);
            Route::post('attendance/add-entry', [AttendanceController::class, 'addEntry']);
            Route::get('attendance/filter', [AttendanceController::class, 'getFilter']);
         Route::post('attendance/mark-range', [AttendanceController::class, 'markRange'])
    ->name('attendance.mark-range');
            /*
            |--------------------------------------------------------------------------
            | Employee Schedules
            |--------------------------------------------------------------------------
            */
            Route::get('schedules', [SchedulesController::class, 'index'])->name('schedule');
            Route::post('schedules/add', [SchedulesController::class, 'add']);
            Route::get('schedules/edit/{id}', [SchedulesController::class, 'edit']);
            Route::post('schedules/update', [SchedulesController::class, 'update']);
            Route::get('schedules/delete/{id}', [SchedulesController::class, 'delete']);
            Route::get('schedules/archive/{id}', [SchedulesController::class, 'archive']);
            Route::post('schedules/weekly', [SchedulesController::class, 'storeWeekly']);

            Route::get('/schedules/weekly/{id}', [SchedulesController::class, 'getWeekly']);
            Route::get('/today-shifts', [SchedulesController::class, 'todayShifts'])->name('today.shifts');

            Route::get('/staff-rota', [SchedulesController::class, 'rota']);
            Route::get('/rota/pdf', [SchedulesController::class, 'rotaPdf'])->name('rota.pdf');

            // Monthly Rota - calendar-grid view built from the same
            // recurring weekly_shifts pattern as the weekly rota above.
            Route::get('/monthly-rota', [SchedulesController::class, 'monthlyRota'])->name('monthly.rota');
            Route::get('/monthly-rota/pdf', [SchedulesController::class, 'monthlyRotaPdf'])->name('monthly.rota.pdf');

            /*
            |--------------------------------------------------------------------------
            | Employee Leaves
            |--------------------------------------------------------------------------
            */
            Route::get('leaves', [LeavesController::class, 'index'])->name('leave');
            Route::get('leaves/edit/{id}', [LeavesController::class, 'edit']);
            Route::get('leaves/delete/{id}', [LeavesController::class, 'delete']);
            Route::post('leaves/update', [LeavesController::class, 'update']);

            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */
            Route::get('users', [UsersController::class, 'index'])->name('users');
            Route::get('users/enable/{id}', [UsersController::class, 'enable']);
            Route::get('users/disable/{id}', [UsersController::class, 'disable']);
            Route::get('users/edit/{id}', [UsersController::class, 'edit']);
            Route::get('users/delete/{id}', [UsersController::class, 'delete']);
            Route::post('users/register', [UsersController::class, 'register']);
            Route::post('users/update/user', [UsersController::class, 'update']);

            /*
            |--------------------------------------------------------------------------
            | Employee Users & Roles
            |--------------------------------------------------------------------------
            */
            Route::get('users/roles', [RolesController::class, 'index'])->name('roles');
            Route::post('users/roles/add', [RolesController::class, 'add']);
            Route::get('user/roles/get', [RolesController::class, 'get']);
            Route::post('users/roles/update', [RolesController::class, 'update']);
            Route::get('users/roles/delete/{id}', [RolesController::class, 'delete']);
            Route::get('users/roles/permissions/edit/{id}', [RolesController::class, 'editperm']);
            Route::post('users/roles/permissions/update', [RolesController::class, 'updateperm']);

            /*
            |--------------------------------------------------------------------------
            | Update User
            |--------------------------------------------------------------------------
            */
            Route::get('update-profile', [ProfileController::class, 'viewProfile'])->name('updateProfile');
            Route::get('update-password', [ProfileController::class, 'viewPassword'])->name('updatePassword');
            Route::post('user/update-profile', [ProfileController::class, 'updateUser']);
            Route::post('user/update-password', [ProfileController::class, 'updatePassword']);

            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */
            Route::get('reports', [ReportsController::class, 'index'])->name('reports');
            Route::get('reports/employee-list', [ReportsController::class, 'empList']);
            Route::get('reports/employee-attendance', [ReportsController::class, 'empAtten']);
            Route::get('reports/individual-attendance', [ReportsController::class, 'indiAtten']);
            Route::get('reports/employee-leaves', [ReportsController::class, 'empLeaves']);
            Route::get('reports/individual-leaves', [ReportsController::class, 'indiLeaves']);
            Route::get('reports/employee-schedule', [ReportsController::class, 'empSched']);
            Route::get('reports/organization-profile', [ReportsController::class, 'orgProfile']);
            Route::get('reports/employee-birthdays', [ReportsController::class, 'empBday']);
            Route::get('reports/user-accounts', [ReportsController::class, 'userAccs']);
            Route::get('get/employee-attendance', [ReportsController::class, 'getEmpAtten']);
            Route::get('get/employee-leaves', [ReportsController::class, 'getEmpLeav']);
            Route::get('get/employee-schedules', [ReportsController::class, 'getEmpSched']);

            // Report PDF exports - each mirrors the filter logic of its
            // matching get/employee-* AJAX endpoint above, so the PDF
            // always matches whatever is currently filtered on screen.
            Route::get('reports/pdf/attendance', [ReportsController::class, 'attendancePdf']);
            Route::get('reports/pdf/leaves', [ReportsController::class, 'leavesPdf']);
            Route::get('reports/pdf/schedule', [ReportsController::class, 'schedulePdf']);
            Route::get('reports/pdf/employees', [ReportsController::class, 'employeeListPdf']);
            Route::get('reports/pdf/birthdays', [ReportsController::class, 'birthdaysPdf']);
            Route::get('reports/pdf/accounts', [ReportsController::class, 'userAccountsPdf']);

            /*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */
            Route::get('settings', [SettingsController::class, 'index'])->name('settings');
            Route::post('settings/update', [SettingsController::class, 'update']);
            Route::get('settings/get/app/info', [SettingsController::class, 'appInfo']);

            /*
            |--------------------------------------------------------------------------
            | Application Shortcuts
            |--------------------------------------------------------------------------
            */
            // Company
            Route::get('fields/company/', [FieldsController::class, 'company'])->name('company');
            Route::post('fields/company/add', [FieldsController::class, 'addCompany']);
            Route::get('fields/company/delete/{id}', [FieldsController::class, 'deleteCompany']);
            Route::get('fields/company/document/delete/{id}', [FieldsController::class, 'deleteCompanyDocument']);
            Route::get('fields/company/edit/{id}', [FieldsController::class, 'editCompany']);
            Route::post('fields/company/update', [FieldsController::class, 'updateCompany']);
            // Department
            Route::get('fields/department', [FieldsController::class, 'department'])->name('department');
            Route::post('fields/department/add', [FieldsController::class, 'addDepartment']);
            Route::get('fields/department/delete/{id}', [FieldsController::class, 'deleteDepartment']);
            Route::get('fields/department/edit/{id}', [FieldsController::class, 'editDepartment']);
            Route::post('fields/department/update', [FieldsController::class, 'updateDepartment']);
            Route::get('employees/departments-by-company', [FieldsController::class, 'departmentsByCompany']);
            // Job Title
            Route::get('fields/jobtitle', [FieldsController::class, 'jobtitle'])->name('jobtitle');
            Route::post('fields/jobtitle/add', [FieldsController::class, 'addJobtitle']);
            Route::get('fields/jobtitle/delete/{id}', [FieldsController::class, 'deleteJobtitle']);
            Route::get('fields/jobtitle/{id}/edit', [FieldsController::class, 'editJobtitle']);
            Route::post('fields/jobtitle/{id}/update', [FieldsController::class, 'updateJobtitle']);
            // Leave Types
            Route::get('fields/leavetype', [FieldsController::class, 'leavetype'])->name('leavetype');
            Route::post('fields/leavetype/add', [FieldsController::class, 'addLeavetype']);
            Route::get('fields/leavetype/delete/{id}', [FieldsController::class, 'deleteLeavetype']);
            Route::get('fields/leavetype/leave-groups', [FieldsController::class, 'leaveGroups'])->name('leavegroup');
            Route::post('fields/leavetype/leave-groups/add', [FieldsController::class, 'addLeaveGroups']);
            Route::get('fields/leavetype/leave-groups/edit/{id}', [FieldsController::class, 'editLeaveGroups']);
            Route::post('fields/leavetype/leave-groups/update', [FieldsController::class, 'updateLeaveGroups']);
            Route::get('fields/leavetype/leave-groups/delete/{id}', [FieldsController::class, 'deleteLeaveGroups']);

            /*
            |--------------------------------------------------------------------------
            | Exports : Employee data
            |--------------------------------------------------------------------------
            */
            // export
            Route::get('export/fields/company', [ExportsController::class, 'company']);
            Route::get('export/fields/department', [ExportsController::class, 'department']);
            Route::get('export/fields/jobtitle', [ExportsController::class, 'jobtitle']);
            Route::get('export/fields/leavetypes', [ExportsController::class, 'leavetypes']);

            // import
            Route::post('import/fields/company', [ImportsController::class, 'importCompany']);
            Route::post('import/fields/department', [ImportsController::class, 'importDepartment']);
            Route::post('import/fields/jobtitle', [ImportsController::class, 'importJobtitle']);
            Route::post('import/fields/leavetypes', [ImportsController::class, 'importLeavetypes']);

            // import options
            Route::post('import/options', [ImportsController::class, 'opt']);

            // reports export
            Route::get('export/report/employees', [ExportsController::class, 'employeeList']);
            Route::post('export/report/attendance', [ExportsController::class, 'attendanceReport']);
            Route::post('export/report/leaves', [ExportsController::class, 'leavesReport']);
            Route::get('export/report/birthdays', [ExportsController::class, 'birthdaysReport']);
            Route::get('export/report/accounts', [ExportsController::class, 'accountReport']);
            Route::post('export/report/schedule', [ExportsController::class, 'scheduleReport']);
        });

        Route::group(['middleware' => 'employee'], function () {
            /*
            |--------------------------------------------------------------------------
            | Employee Frontend : Dashboard, Profile, Attendance, Schedules, Leaves, Settings
            |--------------------------------------------------------------------------
            */
            // dashboard
            Route::get('personal/dashboard', [PersonalDashboardController::class, 'index']);

            // profile
            Route::get('personal/profile/view', [PersonalProfileController::class, 'index'])->name('myProfile');
            Route::get('personal/profile/edit/', [PersonalProfileController::class, 'profileEdit']);
            Route::post('personal/profile/update', [PersonalProfileController::class, 'profileUpdate']);

            // attendance
            Route::get('personal/attendance/view', [PersonalAttendanceController::class, 'index']);
            Route::get('get/personal/attendance', [PersonalAttendanceController::class, 'getPA']);
            Route::get('personal/clock/attendance',[ClockController::class,'personalclock'])->name('clock.personal');
            Route::get('/personal/schedules/lookup', [ClockController::class, 'scheduleLookup']);
            Route::post('personal/attendance/add', [ClockController::class, 'add'])->name('personal.attendance.add');

            // schedules
            Route::get('personal/schedules/view', [PersonalSchedulesController::class, 'index']);
            Route::get('get/personal/schedules', [PersonalSchedulesController::class, 'getPS']);

            // leaves
            Route::get('personal/leaves/view', [PersonalLeavesController::class, 'index'])->name('viewPersonalLeave');
            Route::get('personal/leaves/edit/{id}', [PersonalLeavesController::class, 'edit']);
            Route::post('personal/leaves/update', [PersonalLeavesController::class, 'update']);
            Route::post('personal/leaves/request', [PersonalLeavesController::class, 'requestL']);
            Route::get('personal/leaves/delete/{id}', [PersonalLeavesController::class, 'delete']);
            Route::get('get/personal/leaves', [PersonalLeavesController::class, 'getPL']);
            Route::get('view/personal/leave', [PersonalLeavesController::class, 'viewPL']);

            // settings
            Route::get('personal/settings', [PersonalSettingsController::class, 'index']);
            Route::get('/personal/documents', [PersonalSettingsController::class, 'documents'])->name('personal.documents');

            // user
            Route::get('personal/update-user', [PersonalAccountController::class, 'viewUser'])->name('changeUser');
            Route::get('personal/update-password', [PersonalAccountController::class, 'viewPassword'])->name('changePass');
            Route::post('personal/update/user', [PersonalAccountController::class, 'updateUser']);
            Route::post('personal/update/password', [PersonalAccountController::class, 'updatePassword']);
        });
    });
});

Route::get('/verify-otp', [LoginController::class, 'showOtpForm'])->name('otp.form');
Route::post('/verify-otp', [LoginController::class, 'verifyOtp'])->name('otp.verify');

Auth::routes();

Route::get('lang/{locale}', [LanguageController::class, 'lang']);

Route::get('logout', [LoginController::class, 'logout'])->name('logout');
Route::view('permission-denied', 'errors.permission-denied')->name('denied');
Route::view('account-disabled', 'errors.account-disabled')->name('disabled');
Route::view('account-not-found', 'errors.account-not-found')->name('notfound');

Route::get('/home', [HomeController::class, 'index'])->name('home');