<?php

namespace App\Http\Controllers\Admin;
use DB;
use App\Classes\table;
use App\Classes\permission;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class FieldsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */
    public function company() 
    {
      if (permission::permitted('company')=='fail'){ return redirect()->route('denied'); }

      $data = table::company()->get();

      $documents = table::companydocuments()
        ->whereIn('company_id', $data->pluck('id'))
        ->get()
        ->groupBy('company_id');

      return view('admin.fields.company', compact('data', 'documents'));
    }

    public function addCompany(Request $request)
    {
      if (permission::permitted('company-add')=='fail'){ return redirect()->route('denied'); }

      $v = $request->validate([
        'company' => 'required|alpha_dash_space|max:100',
        'doc_label' => 'nullable|array',
        'doc_label.*' => 'nullable|string|max:255',
        'company_doc' => 'nullable|array',
        'company_doc.*' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:4096',
      ]);

      $company = mb_strtoupper($request->company);

      $docStoredPaths = [];
      try {
        foreach ($request->file('company_doc', []) as $i => $docFile) {
          $docStoredPaths[$i] = $this->storeCompanyDocument($docFile);
        }
      } catch (\RuntimeException $e) {
        return redirect('fields/company')->withInput()->with('error', trans($e->getMessage()));
      }

      $refId = null;

      try {
        DB::transaction(function () use ($company, $request, $docStoredPaths, &$refId) {
          table::company()->insert([
            'company' => $company,
            'address' => $request->address,
            'licenceNo' => $request->licenceNo,
          ]);

          $refId = DB::getPdo()->lastInsertId();

          $labels = $request->input('doc_label', []);
          $docRows = [];

          foreach ($docStoredPaths as $i => $path) {
            if (!$path) {
              continue;
            }

            $docRows[] = [
              'company_id' => $refId,
              'doc_label'  => isset($labels[$i]) && trim((string) $labels[$i]) !== ''
                ? mb_strtoupper(trim((string) $labels[$i]))
                : 'DOCUMENT',
              'doc_file'   => $path,
              'created_at' => now(),
              'updated_at' => now(),
            ];
          }

          if (!empty($docRows)) {
            table::companydocuments()->insert($docRows);
          }
        });
      } catch (\Exception $e) {
        foreach ($docStoredPaths as $path) {
          if ($path) {
            Storage::disk('public')->delete($path);
          }
        }
        return redirect('fields/company')->withInput()->with('error', trans('Something went wrong while saving the company. Please try again.'));
      }

      return redirect('fields/company')->with('success', trans("New company has been added!"));
    }

    public function deleteCompany($id, Request $request)
    {
      if (permission::permitted('company-delete')=='fail'){ return redirect()->route('denied'); }

      $documents = table::companydocuments()->where('company_id', $id)->get();
      foreach ($documents as $doc) {
        if ($doc->doc_file) {
          Storage::disk('public')->delete($doc->doc_file);
        }
      }
      table::companydocuments()->where('company_id', $id)->delete();

      table::company()->where('id', $id)->delete();

      return redirect('fields/company')->with('success', trans("Deleted!"));
    }

    public function editCompany($id)
    {
      if (permission::permitted('company-edit')=='fail'){ return redirect()->route('denied'); }

      $company = table::company()->where('id', $id)->first();

      if (!$company) {
        return redirect('fields/company')->with('error', trans("Company not found."));
      }

      $documents = table::companydocuments()->where('company_id', $company->id)->get();
      $e_id = Crypt::encryptString($company->id);

      return view('admin.edits.edit-company', compact('company', 'documents', 'e_id'));
    }

    public function updateCompany(Request $request)
    {
      if (permission::permitted('company-edit')=='fail'){ return redirect()->route('denied'); }

      $v = $request->validate([
        'company' => 'required|alpha_dash_space|max:100',
        'id' => 'required|max:200',
        'doc_label' => 'nullable|array',
        'doc_label.*' => 'nullable|string|max:255',
        'company_doc' => 'nullable|array',
        'company_doc.*' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:4096',
      ]);

      $id = Crypt::decryptString($request->id);
      $company = mb_strtoupper($request->company);

      $docStoredPaths = [];
      try {
        foreach ($request->file('company_doc', []) as $i => $docFile) {
          $docStoredPaths[$i] = $this->storeCompanyDocument($docFile);
        }
      } catch (\RuntimeException $e) {
        return redirect('fields/company/edit/'.$request->id)->withInput()->with('error', trans($e->getMessage()));
      }

      try {
        DB::transaction(function () use ($id, $company, $request, $docStoredPaths) {
          table::company()->where('id', $id)->update([
            'company' => $company,
            'address' => $request->address,
            'licenceNo' => $request->licenceNo,
          ]);

          $labels = $request->input('doc_label', []);
          $docRows = [];

          foreach ($docStoredPaths as $i => $path) {
            if (!$path) {
              continue;
            }

            $docRows[] = [
              'company_id' => $id,
              'doc_label'  => isset($labels[$i]) && trim((string) $labels[$i]) !== ''
                ? mb_strtoupper(trim((string) $labels[$i]))
                : 'DOCUMENT',
              'doc_file'   => $path,
              'created_at' => now(),
              'updated_at' => now(),
            ];
          }

          if (!empty($docRows)) {
            table::companydocuments()->insert($docRows);
          }
        });
      } catch (\Exception $e) {
        foreach ($docStoredPaths as $path) {
          if ($path) {
            Storage::disk('public')->delete($path);
          }
        }
        return redirect('fields/company/edit/'.$request->id)->withInput()->with('error', trans('Something went wrong while updating the company. Please try again.'));
      }

      return redirect('fields/company')->with('success', trans("Company has been updated!"));
    }

    public function deleteCompanyDocument($id, Request $request)
    {
      if (permission::permitted('company-delete')=='fail'){ return redirect()->route('denied'); }

      $doc = table::companydocuments()->where('id', $id)->first();

      if ($doc) {
        if ($doc->doc_file) {
          Storage::disk('public')->delete($doc->doc_file);
        }
        table::companydocuments()->where('id', $id)->delete();
      }

      return redirect('fields/company')->with('success', trans("Document deleted!"));
    }

    private function storeCompanyDocument($file)
    {
      if (!$file) {
        return null;
      }

      $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
      $filename = Str::uuid()->toString() . '.' . $extension;

      return $file->storeAs('company-documents', $filename, 'public');
    }

    /*
    |--------------------------------------------------------------------------
    | Department
    |--------------------------------------------------------------------------
    */
    public function department() 
    {
      if (permission::permitted('departments')=='fail'){ return redirect()->route('denied'); }

      $data = table::department()->get();
      $company = table::company()->get();

      $companiesById = $company->keyBy('id');
      foreach ($data as $dept) {
          $dept->company_name = optional($companiesById->get($dept->company_id))->company;
      }

      return view('admin.fields.department', compact('data', 'company'));
    }

    public function addDepartment(Request $request)
    {
      if (permission::permitted('departments-add')=='fail'){ return redirect()->route('denied'); }

      $v = $request->validate([
        'department' => 'required|alpha_dash_space|max:100',
        'company' => 'required|integer|exists:tbl_form_company,id',
      ]);

      $department = mb_strtoupper($request->department);
      $company_id = $request->company;

      table::department()->insert([
        ['department' => $department, 'company_id' => $company_id],
      ]);

      return redirect('fields/department')->with('success', trans("New department has been added!"));
    }

    public function deleteDepartment($id, Request $request)
    {
      if (permission::permitted('departments-delete')=='fail'){ return redirect()->route('denied'); }

      table::department()->where('id', $id)->delete();

      return redirect('fields/department')->with('success', trans("Deleted!"));
    }

    public function editDepartment($id)
    {
      if (permission::permitted('departments-edit')=='fail'){ return redirect()->route('denied'); }

      $department = table::department()->where('id', $id)->first();

      if (!$department) {
        return redirect('fields/department')->with('error', trans("Department not found."));
      }

      $company = table::company()->get();
      $e_id = Crypt::encryptString($department->id);

      return view('admin.edits.edit-department', compact('department', 'company', 'e_id'));
    }

    public function updateDepartment(Request $request)
    {
      if (permission::permitted('departments-edit')=='fail'){ return redirect()->route('denied'); }

      $v = $request->validate([
        'department' => 'required|alpha_dash_space|max:100',
        'company' => 'required|integer|exists:tbl_form_company,id',
        'id' => 'required|max:200',
      ]);

      $id = Crypt::decryptString($request->id);
      $department = mb_strtoupper($request->department);
      $company_id = $request->company;

      table::department()->where('id', $id)->update([
        'department' => $department,
        'company_id' => $company_id,
      ]);

      return redirect('fields/department')->with('success', trans("Department has been updated!"));
    }

    /**
     * AJAX endpoint: list a company's departments as JSON, used by the
     * Company -> Department cascades on the New Employee and Job Title
     * forms. Expects ?company_id=<id> (the numeric company id, NOT its name).
     */
    public function departmentsByCompany(Request $request)
    {
        $companyId = $request->query('company_id');

        if (!$companyId || !is_numeric($companyId)) {
            return response()->json([]);
        }

        $company = table::company()->where('id', $companyId)->first();

        if (!$company) {
            return response()->json([]);
        }

        $departments = table::department()
            ->where('company_id', $company->id)
            ->orderBy('department')
            ->get(['id', 'department']);

        return response()->json($departments);
    }

    /*
    |--------------------------------------------------------------------------
    | Job Title or Position
    |--------------------------------------------------------------------------
    */
    public function jobtitle() 
    {
      if (permission::permitted('jobtitles')=='fail'){ return redirect()->route('denied'); }

      $data = table::jobtitle()->get();
      $d = table::department()->get();
      $company = table::company()->get();

      // Attach display-only company_name and department_name to each job
      // title row so the listing table doesn't need nested loops in Blade.
      $companiesById = $company->keyBy('id');
      $departmentsById = $d->keyBy('id');

      foreach ($data as $job) {
          $dept = $departmentsById->get($job->dept_code);
          $job->department_name = $dept->department ?? null;
          $job->company_name = $dept ? optional($companiesById->get($dept->company_id))->company : null;
      }

      return view('admin.fields.jobtitle', compact('data', 'd', 'company'));
    }

    public function addJobtitle(Request $request)
    {
      if (permission::permitted('jobtitles-add')=='fail'){ return redirect()->route('denied'); }

      $v = $request->validate([
        'jobtitle' => 'required|alpha_dash_space|max:100',
        'company_id' => 'required|integer|exists:tbl_form_company,id',
        'department' => 'required|integer|exists:tbl_form_department,id',
      ]);

      // Guard against a tampered/stale department that doesn't actually
      // belong to the selected company - same integrity check used on
      // update, so add and update can never disagree.
      $department = table::department()
        ->where('id', $request->department)
        ->where('company_id', $request->company_id)
        ->first();

      if (!$department) {
        return redirect('fields/jobtitle')->withInput()->with('error', trans('The selected department does not belong to the selected company.'));
      }

      $jobtitle = mb_strtoupper(trim($request->jobtitle));

      table::jobtitle()->insert([
        [
          'jobtitle' => $jobtitle,
          'dept_code' => $department->id,
          'company_id' => $request->company_id,
          
        ],
      ]);

      return redirect('fields/jobtitle')->with('success', trans("New job title has been added!"));
    }

    public function editJobtitle($id)
    {
        if (permission::permitted('jobtitles-edit') == 'fail') {
            return redirect()->route('denied');
        }

        $data = table::jobtitle()->where('id', $id)->first();

        if (!$data) {
            return redirect('fields/jobtitle')->with('error', trans("Job title not found."));
        }

        $company = table::company()->get();

        $selectedDeptCode = $data->dept_code;
        $selectedCompanyId = null;

        $department = table::department()->where('id', $data->dept_code)->first();
        if ($department) {
            $selectedCompanyId = $department->company_id;
        }

        return view('admin.edits.edit-jobtitle', compact('data', 'company', 'selectedCompanyId', 'selectedDeptCode'));
    }

    public function updateJobtitle($id, Request $request)
    {
        if (permission::permitted('jobtitles-edit') == 'fail') {
            return redirect()->route('denied');
        }

        $request->validate([
            'company_id' => 'required|integer|exists:tbl_form_company,id',
            'dept_code' => 'required|integer|exists:tbl_form_department,id',
            'jobtitle' => 'required|string|max:100',
        ]);

        $department = table::department()
            ->where('id', $request->dept_code)
            ->where('company_id', $request->company_id)
            ->first();

        if (!$department) {
            return redirect()->back()->withInput()->withErrors([
                'dept_code' => 'The selected department does not belong to the selected company.',
            ]);
        }

        $jobtitle = mb_strtoupper(trim($request->jobtitle));

        // Keep company_id in sync with dept_code - previously this could
        // drift out of sync if only dept_code was updated while company_id
        // stayed pointed at whatever it was on creation.
        table::jobtitle()->where('id', $id)->update([
            'jobtitle' => $jobtitle,
            'dept_code' => $department->id,
            'company_id' => $department->company_id,
        ]);

        return redirect('fields/jobtitle')->with('success', trans("Job title has been updated!"));
    }

    public function deleteJobtitle($id, Request $request)
    {
      if (permission::permitted('jobtitles-delete')=='fail'){ return redirect()->route('denied'); }

      table::jobtitle()->where('id', $id)->delete();

      return redirect('fields/jobtitle')->with('success', trans("Deleted!"));
    }

    /*
    |--------------------------------------------------------------------------
    | Leave Type
    |--------------------------------------------------------------------------
    */
    public function leavetype() 
    {
        if (permission::permitted('leavetypes')=='fail'){ return redirect()->route('denied'); }

        $data = table::leavetypes()->get();

        return view('admin.fields.leavetype', compact('data'));
    }

    public function addLeavetype(Request $request)
    {
      if (permission::permitted('leavetypes-add')=='fail'){ return redirect()->route('denied'); }

      $v = $request->validate([
        'leavetype' => 'required|alpha_dash_space|max:100',
        'credits' => 'required|digits_between:0,365|max:3',
        'term' => 'required|max:100',
      ]);

      $leavetype = mb_strtoupper($request->leavetype);
      $credits = $request->credits;
      $term = $request->term;

      table::leavetypes()->insert([
        ['leavetype' => $leavetype, 'limit' => $credits, 'percalendar' => $term]
      ]);

      return redirect('fields/leavetype')->with('success', trans("New leave type has been added!"));
    }

    public function deleteLeavetype($id, Request $request)
    {
      if (permission::permitted('leavetypes-delete')=='fail'){ return redirect()->route('denied'); }
      
      table::leavetypes()->where('id', $id)->delete();

      return redirect('fields/leavetype')->with('success', trans("Deleted!"));
    }

    /*
    |--------------------------------------------------------------------------
    | Leave Groups
    |--------------------------------------------------------------------------
    */
    public function leaveGroups() 
    {
      if (permission::permitted('leavegroups')=='fail'){ return redirect()->route('denied'); }

      $lt = table::leavetypes()->get();
      $lg = table::leavegroup()->get();

      return view('admin.fields.leave-groups', compact('lt', 'lg'));
    }

    public function addLeaveGroups(Request $request) 
    {
      if (permission::permitted('leavegroup-add')=='fail'){ return redirect()->route('denied'); }

      $v = $request->validate([
        'leavegroup' => 'required|alpha_dash_space|max:100',
        'description' => 'required|alpha_dash_space|max:155',
        'status' => 'required|boolean|max:1',
        'leaveprivileges' => 'required|max:255',
      ]);

      $leavegroup = strtoupper($request->leavegroup); 
      $description = strtoupper($request->description);
      $status = $request->status;
      $leaveprivileges = implode(',', $request->leaveprivileges);

      table::leavegroup()->insert([
        ["leavegroup" => $leavegroup, "description" => $description, "leaveprivileges" => $leaveprivileges, "status" => $status]
      ]);

      return redirect('fields/leavetype/leave-groups')->with('success', trans("New leave group has been added!"));
    }

    public function editLeaveGroups($id) 
    {
      if (permission::permitted('leavegroup-edit')=='fail'){ return redirect()->route('denied'); }

      $lt = table::leavetypes()->get();
      $lg = table::leavegroup()->where("id", $id)->first();
      $e_id = ($lg->id == null) ? 0 : Crypt::encryptString($lg->id) ;

      return view('admin.edits.edit-leavegroups', compact('lg', 'lt', 'e_id'));
    }

    public function updateLeaveGroups(Request $request) 
    {
      if (permission::permitted('leavegroup-edit')=='fail'){ return redirect()->route('denied'); }

      $v = $request->validate([
        'leavegroup' => 'required|alpha_dash_space|max:100',
        'description' => 'required|alpha_dash_space|max:155',
        'status' => 'required|boolean|max:1',
        'leaveprivileges' => 'required|max:255',
        'id' => 'required|max:200'
      ]);

      $leavegroup = strtoupper($request->leavegroup); 
      $description = strtoupper($request->description);
      $status = $request->status;
      $leaveprivileges = implode(',', $request->leaveprivileges);
      $id = Crypt::decryptString($request->id);

      table::leavegroup()->where('id', $id)->update([
          "leavegroup" => $leavegroup,
          "description" => $description,
          "leaveprivileges" => $leaveprivileges,
          "status" => $status
      ]);

      return redirect('fields/leavetype/leave-groups')->with('success', trans("Leave group has been updated!"));
    }

    public function deleteLeaveGroups($id,Request $request) 
    {
      if (permission::permitted('leavegroup-delete')=='fail'){ return redirect()->route('denied'); }

      table::leavegroup()->where('id', $id)->delete();

      return redirect('fields/leavetype/leave-groups')->with('success', trans("Deleted!"));
    }
}