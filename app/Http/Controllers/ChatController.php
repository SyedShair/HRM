<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\User;
use App\Message;
use Auth;
use DB;
class ChatController extends Controller
{
    public function index()
    {
        $authUser = Auth::user();

        if ($authUser->role_id == 1) {

            $users = User::where('role_id', 2)->get();

        } else {

            $users = User::where('role_id', 1)->get();
        }
// ADD UNREAD COUNT
    foreach ($users as $user) {

        $user->unread_count = DB::table('messages')
            ->where('sender_id', $user->id)
            ->where('receiver_id', $authUser->id)
            ->where('is_read', 0)
            ->count();
    }
        return view('chat.index', compact('users'));
    }

   public function chat($id)
{
    $receiver = User::findOrFail($id);

    $authUser = Auth::user();

    if ($authUser->role_id == 1) {

        $users = User::where('role_id', 2)->get();

    } else {

        $users = User::where('role_id', 1)->get();
    }

    return view('chat.chatbox', compact(
        'receiver',
        'users'
    ));
}
public function fetchMessages($id)
{
    // 1. Mark messages as read (IMPORTANT FIX)
    Message::where('sender_id', $id)
        ->where('receiver_id', Auth::id())
        ->where('is_read', 0)
        ->update([
            'is_read' => 1
        ]);

    // 2. Fetch messages
    $messages = Message::where(function($query) use ($id) {

        $query->where('sender_id', Auth::id())
              ->where('receiver_id', $id);

    })->orWhere(function($query) use ($id) {

        $query->where('sender_id', $id)
              ->where('receiver_id', Auth::id());

    })->orderBy('id', 'ASC')->get();

    return response()->json($messages);
}
    // public function fetchMessages($id)
    // {
    //     $messages = Message::where(function($query) use ($id) {

    //         $query->where('sender_id', Auth::id())
    //               ->where('receiver_id', $id);

    //     })->orWhere(function($query) use ($id) {

    //         $query->where('sender_id', $id)
    //               ->where('receiver_id', Auth::id());

    //     })->orderBy('id', 'ASC')->get();

    //     return response()->json($messages);
    // }

   public function sendMessage(Request $request)
{
   $file = $request->file('file');

$filePath = null;
$fileType = null;

if ($file) {

    // Clean filename
    $original  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $extension = strtolower($file->getClientOriginalExtension());

    $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $original);

    $newFileName = time() . '_' . $cleanName . '.' . $extension;

    // ✅ SINGLE CHAT FOLDER (NO EMPLOYEE NAME)
    $uploadPath = public_path('uploads/chat_files');

    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    // Move file
    $file->move($uploadPath, $newFileName);

    // DB path
    $filePath = 'uploads/chat_files/' . $newFileName;

    // File type detect
    if ($extension === 'pdf') {
        $fileType = 'pdf';
    } elseif (in_array($extension, ['doc', 'docx'])) {
        $fileType = 'doc';
    } elseif (in_array($extension, ['jpg','jpeg','png','gif','webp'])) {
        $fileType = 'image';
    } else {
        $fileType = 'file';
    }
}
    // =========================
    // SAVE MESSAGE
    // =========================
    Message::create([
        'sender_id' => Auth::id(),
        'receiver_id' => $request->receiver_id,
        'message' => $request->message ?? null,
        'file' => $filePath,   // NEW COLUMN
        'is_read' => 0
    ]);

    return response()->json([
        'status' => true
    ]);
}
    public function markAsRead(Request $request)
{
    Message::where('sender_id', $request->sender_id)
        ->where('receiver_id', Auth::id())
        ->update([
            'is_read' => 1
        ]);

    return response()->json(['success' => true]);
}
public function updateMessage(Request $request, $id)
{
    DB::table('messages')
        ->where('id', $id)
        ->where('sender_id', Auth::id())
        ->update([
            'message' => $request->message,
            'updated_at' => now()
        ]);

    return response()->json([
        'success' => true
    ]);
}

public function deleteMessage($id)
{
    DB::table('messages')
        ->where('id', $id)
        ->where('sender_id', Auth::id())
        ->delete();

    return response()->json([
        'success' => true
    ]);
}
public function unreadCount()
{
    $messages = \DB::table('messages')
        ->join('users', 'users.id', '=', 'messages.sender_id')
        ->where('messages.receiver_id', Auth::id())
        ->where('messages.is_read', 0)
        ->select('users.name')
        ->get();

    $count = $messages->count();

    $senders = $messages->pluck('name')->unique()->values();

    return response()->json([
        'count' => $count,
        'senders' => $senders
    ]);
    
}

public function typing(Request $request)
{
    $senderId = auth()->id();
    $receiverId = $request->receiver_id;
    if ($request->typing == 1) {

        // KEY = receiver sees who is typing
        Cache::put("typing_$senderId", $senderId, 2); // 2 sec auto expire

    } else {

        Cache::forget("typing_$receiverId");
    }

    return response()->json(['success' => true]);
}

public function getTyping($id)
{
    $senderId = Cache::get("typing_$id");

    if (!$senderId) {
        return response()->json([
            'typing' => 0,
            'name' => ''
        ]);
    }

    $user = \App\User::find($senderId);

    return response()->json([
        'typing' => 1,
        'name' => $user->name ?? ''
    ]);
}

public function send(Request $request)
{
    $message = new ChatMessage();
    $message->sender_id = auth()->id();
    $message->receiver_id = $request->receiver_id;
    $message->message = $request->message;

    if ($request->hasFile('file')) {

        $file = $request->file('file');

        $name = time().'_'.$file->getClientOriginalName();

        $file->move(public_path('chat_files'), $name);

        $message->file = $name;
    }

    $message->save();

    return response()->json(['success' => true]);
}
}