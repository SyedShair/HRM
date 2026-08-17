<?php

namespace App\Http\Controllers\Personal;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Classes\table;
use App\Classes\permission;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class PersonalSettingsController extends Controller
{
    public function index() 
    {
        return view('personal.personal-settings-view');
    }
    public function documents()
{
     $user = Auth::user();
    $documents = DB::table('employee_documents')
        ->where('people_id', $user->reference)
        ->get();


    return view('personaldocuments', compact('documents'));
}
    
}

