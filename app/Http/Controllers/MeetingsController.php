<?php

namespace App\Http\Controllers;

use App\Classes\permission;
use App\Classes\table;
use App\Http\Controllers\Controller;
use App\Services\ZoomService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class MeetingsController extends Controller
{
    protected ZoomService $zoom;

    public function __construct(ZoomService $zoom)
    {
        $this->zoom = $zoom;
    }

    public function index(Request $request)
    {
        if (permission::permitted('meetings') == 'fail') { return redirect()->route('denied'); }

        $filter = $request->query('filter', 'upcoming'); // upcoming | past | cancelled | all

        $query = DB::table('meetings')->where('archive', 0);

        if ($filter === 'upcoming') {
            $query->where('start_time', '>=', now())->whereIn('status', ['scheduled', 'started']);
        } elseif ($filter === 'past') {
            $query->where(function ($q) {
                $q->where('start_time', '<', now())->orWhere('status', 'ended');
            });
        } elseif ($filter === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        $meetings = $query->orderBy('start_time', $filter === 'past' ? 'desc' : 'asc')->get();

        foreach ($meetings as $meeting) {
            $meeting->participant_count = DB::table('meeting_participants')
                ->where('meeting_id', $meeting->id)->count();
        }

        return view('admin.meetings.index', compact('meetings', 'filter'));
    }

    public function create()
    {
        if (permission::permitted('meetings-add') == 'fail') { return redirect()->route('denied'); }

        $employees = table::people()->get();

        return view('admin.meetings.create', compact('employees'));
    }

    public function store(Request $request)
    {
        if (permission::permitted('meetings-add') == 'fail') { return redirect()->route('denied'); }

        $request->validate([
            'topic'                      => 'required|max:150',
            'category'                   => 'required|in:interview,internal,client,other',
            'agenda'                     => 'nullable|max:2000',
            'start_date'                 => 'required|date',
            'start_time'                 => 'required',
            'duration'                   => 'required|integer|min:5|max:600',
            'timezone'                   => 'required|max:60',
            'host_employee_id'           => 'nullable|integer',
            'participant_name.*'         => 'nullable|max:150',
            'participant_email.*'        => 'nullable|email',
            'participant_employee_id.*'  => 'nullable|integer',
            'participant_role.*'         => 'nullable|in:interviewer,candidate,attendee',
        ]);

        $startDateTime = Carbon::parse($request->start_date.' '.$request->start_time);

        try {
            $zoomResponse = $this->zoom->createMeeting([
                'topic'      => $request->topic,
                'agenda'     => $request->agenda,
                'start_time' => $startDateTime,
                'duration'   => $request->duration,
                'timezone'   => $request->timezone,
            ]);
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Could not create the Zoom meeting: '.$e->getMessage());
        }

        $meetingId = DB::table('meetings')->insertGetId([
            'zoom_meeting_id'   => $zoomResponse['id'] ?? null,
            'topic'             => $request->topic,
            'agenda'            => $request->agenda,
            'category'          => $request->category,
            'host_employee_id'  => $request->host_employee_id ?: null,
            'start_time'        => $startDateTime,
            'duration'          => $request->duration,
            'timezone'          => $request->timezone,
            'join_url'          => $zoomResponse['join_url'] ?? null,
            'start_url'         => $zoomResponse['start_url'] ?? null,
            'password'          => $zoomResponse['password'] ?? null,
            'status'            => 'scheduled',
            'created_by'        => auth()->id(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $names  = $request->input('participant_name', []);
        $emails = $request->input('participant_email', []);
        $empIds = $request->input('participant_employee_id', []);
        $roles  = $request->input('participant_role', []);

        foreach ($names as $i => $name) {
            if (empty($name) && empty($emails[$i])) { continue; }

            DB::table('meeting_participants')->insert([
                'meeting_id'  => $meetingId,
                'employee_id' => $empIds[$i] ?: null,
                'name'        => $name ?: 'Guest',
                'email'       => $emails[$i] ?? '',
                'role'        => $roles[$i] ?? 'attendee',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return redirect('meetings/'.$meetingId)->with('success', 'Meeting scheduled and Zoom invite created.');
    }

    public function show($id)
    {
        if (permission::permitted('meetings') == 'fail') { return redirect()->route('denied'); }

        $meeting = DB::table('meetings')->where('id', $id)->first();
        if (!$meeting) { return redirect('meetings')->with('error', 'Meeting not found'); }

        $participants = DB::table('meeting_participants')->where('meeting_id', $id)->get();

        $host = $meeting->host_employee_id
            ? table::people()->where('id', $meeting->host_employee_id)->first()
            : null;

        return view('admin.meetings.show', compact('meeting', 'participants', 'host'));
    }

    /**
     * Manual "Check for Recording" button — pulls from Zoom on demand.
     * The webhook (ZoomWebhookController) does this automatically when configured;
     * this is the fallback for setups without a public webhook URL yet.
     */
    public function syncRecording($id)
    {
        if (permission::permitted('meetings') == 'fail') { return redirect()->route('denied'); }

        $meeting = DB::table('meetings')->where('id', $id)->first();
        if (!$meeting || !$meeting->zoom_meeting_id) {
            return back()->with('error', 'No Zoom meeting linked to this record.');
        }

        $recordings = $this->zoom->getRecordings($meeting->zoom_meeting_id);

        if (!$recordings) {
            return back()->with('error', 'No recording available yet. Zoom can take a few minutes to process it after the meeting ends.');
        }

        $transcript = collect($recordings['recording_files'] ?? [])->firstWhere('file_type', 'TRANSCRIPT');

        DB::table('meetings')->where('id', $id)->update([
            'status'              => 'ended',
            'recording_url'       => $recordings['share_url'] ?? null,
            'recording_password'  => $recordings['password'] ?? null,
            'transcript_url'      => $transcript['download_url'] ?? null,
            'updated_at'          => now(),
        ]);

        return back()->with('success', 'Recording details updated from Zoom.');
    }

    public function cancel($id)
    {
        if (permission::permitted('meetings-delete') == 'fail') { return redirect()->route('denied'); }

        $meeting = DB::table('meetings')->where('id', $id)->first();
        if (!$meeting) { return back()->with('error', 'Meeting not found'); }

        if ($meeting->zoom_meeting_id) {
            try { $this->zoom->deleteMeeting($meeting->zoom_meeting_id); } catch (Exception $e) { /* already gone or unreachable — proceed */ }
        }

        DB::table('meetings')->where('id', $id)->update(['status' => 'cancelled', 'updated_at' => now()]);

        return redirect('meetings')->with('success', 'Meeting cancelled.');
    }

    public function updateNotes(Request $request, $id)
    {
        if (permission::permitted('meetings-edit') == 'fail') { return redirect()->route('denied'); }

        $request->validate(['notes' => 'nullable|max:5000']);

        DB::table('meetings')->where('id', $id)->update([
            'notes'      => $request->notes,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Notes saved.');
    }
}