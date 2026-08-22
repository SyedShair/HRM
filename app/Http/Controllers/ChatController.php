<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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

        // Single grouped query instead of one COUNT query per user in a
        // loop (N+1) - this previously issued as many queries as there
        // were users on every page load, which scales linearly (and
        // badly) with headcount. Now it's always exactly one query
        // regardless of how many users exist.
        $unreadCounts = DB::table('messages')
            ->where('receiver_id', $authUser->id)
            ->where('is_read', 0)
            ->groupBy('sender_id')
            ->selectRaw('sender_id, COUNT(*) as cnt')
            ->pluck('cnt', 'sender_id');

        foreach ($users as $user) {
            $user->unread_count = $unreadCounts[$user->id] ?? 0;
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
    // 1. Mark messages as read
    Message::where('sender_id', $id)
        ->where('receiver_id', Auth::id())
        ->where('is_read', 0)
        ->update([
            'is_read' => 1
        ]);

    // 2. Fetch messages - capped to the most recent 300 instead of the
    // entire thread. This endpoint isn't only used for the initial
    // load: it's also what the 20s full-reconcile timer in the chatbox
    // calls, for as long as the chat window stays open. Without a cap,
    // an active long-running conversation growing into the thousands
    // means resending the ENTIRE history's JSON payload and re-rendering
    // the whole DOM every 20 seconds, forever - increasingly wasteful
    // the older/busier a conversation gets. 300 comfortably covers
    // typical recent context; a proper "load earlier messages" scroll-
    // up pagination would be the right next step if full-history
    // browsing is ever needed, but that's a feature addition beyond
    // this efficiency pass.
    $messages = Message::where(function($query) use ($id) {

        $query->where('sender_id', Auth::id())
              ->where('receiver_id', $id);

    })->orWhere(function($query) use ($id) {

        $query->where('sender_id', $id)
              ->where('receiver_id', Auth::id());

    })->orderBy('id', 'DESC')->limit(300)->get()->sortBy('id')->values();

    // 3. Attach a ready-to-use public URL for any attached file
    // (fixes "image not show" — frontend no longer has to guess the disk/path)
    $messages->transform(function ($msg) {
        $msg->file_url = $msg->file
            ? Storage::disk('public')->url($msg->file)
            : null;
        return $msg;
    });

    return response()->json($messages);
}

    /**
     * Lightweight combined poll used by the open chatbox: returns only
     * messages newer than $afterId (not the full thread every time)
     * plus the current typing status, in ONE request instead of two
     * separate ones (loadMessages + checkTyping used to poll
     * independently on separate 1s/2s timers, hammering the server on
     * every open chat window regardless of whether anything changed).
     * This is what the frontend hits every few seconds while a chat is
     * open.
     *
     * fetchMessages() above is unchanged and still used for the
     * initial full load and the periodic full reconcile, since a
     * delta-only poll (WHERE id > afterId) cannot see edits or deletes
     * on already-fetched messages - those don't get a new id.
     */
    public function poll($id, Request $request)
    {
        $afterId = (int) $request->query('after_id', 0);
        $authId = Auth::id();

        // IMPORTANT: the OR-grouped "either direction of this
        // conversation" condition must be wrapped in its OWN outer
        // closure before the id filter is added. SQL's AND binds
        // tighter than OR, so previously - with the two orWhere()
        // branches sitting directly on $query and `->where('id', '>',
        // $afterId)` chained straight after them - the generated SQL
        // was effectively:
        //     sender=auth AND receiver=other
        //     OR (sender=other AND receiver=auth AND id > afterId)
        // instead of the intended:
        //     (sender=auth AND receiver=other OR sender=other AND receiver=auth)
        //     AND id > afterId
        // which meant EVERY message the current user had ever sent to
        // the other person matched on EVERY poll, completely ignoring
        // after_id - the frontend then re-appended those same messages
        // into the chat window every 3 seconds, which is exactly the
        // "one message loads repeatedly" symptom. Wrapping the whole
        // OR block in one outer where() closure means the id filter
        // added afterward is ANDed onto the WHOLE group, not just the
        // second half of it.
        $query = Message::where(function ($outer) use ($id, $authId) {
            $outer->where(function ($q) use ($id, $authId) {
                $q->where('sender_id', $authId)->where('receiver_id', $id);
            })->orWhere(function ($q) use ($id, $authId) {
                $q->where('sender_id', $id)->where('receiver_id', $authId);
            });
        });

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        }

        $messages = $query->orderBy('id', 'ASC')->limit(500)->get();

        // Only issue the read-status UPDATE when there's actually
        // something new from the other person to mark read - not on
        // every poll cycle.
        if ($messages->where('sender_id', $id)->where('is_read', 0)->isNotEmpty()) {
            Message::where('sender_id', $id)
                ->where('receiver_id', $authId)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);
        }

        $messages->transform(function ($msg) {
            $msg->file_url = $msg->file
                ? Storage::disk('public')->url($msg->file)
                : null;
            return $msg;
        });

        $typingSenderId = Cache::get("typing_$id");
        $typing = ($typingSenderId == $id) ? 1 : 0;
        $typingName = $typing ? (optional(User::find($id))->name ?? '') : '';

        return response()->json([
            'messages'    => $messages,
            'typing'      => $typing,
            'typing_name' => $typingName,
        ]);
    }

public function sendMessage(Request $request)
{
    $filePath = null;
    $fileType = null;

    if ($request->hasFile('file')) {

        $file = $request->file('file');

        // Clean filename
        $original  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension());

        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $original);

        $newFileName = time() . '_' . $cleanName . '.' . $extension;

        // Store in: storage/app/public/uploads/chat_files
        $filePath = $file->storeAs(
            'uploads/chat_files',
            $newFileName,
            'public'
        );

        // File type
        if ($extension === 'pdf') {
            $fileType = 'pdf';
        } elseif (in_array($extension, ['doc', 'docx'])) {
            $fileType = 'doc';
        } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $fileType = 'image';
        } else {
            $fileType = 'file';
        }
    }

    // Save message
    Message::create([
        'sender_id'   => Auth::id(),
        'receiver_id' => $request->receiver_id,
        'message'     => $request->message ?? null,
        'file'        => $filePath,
        'is_read'     => 0
    ]);

    return response()->json([
        'status' => true,
        'file' => $filePath,
        'file_url' => $filePath ? Storage::disk('public')->url($filePath) : null,
        'file_type' => $fileType
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
    // Was previously pulling back one full row (joined to users) PER
    // UNREAD MESSAGE, just to compute a count() and a unique() list of
    // names in PHP afterward. This endpoint is polled globally every
    // 15 seconds from the layout, on every single page in the app - so
    // that cost was being paid constantly, and grows with unread volume
    // for no benefit. Split into two lightweight aggregate queries
    // instead (a COUNT and a DISTINCT name list), neither of which
    // pulls one row per message.
    //
    // NOTE: the layout's loadUnreadMessages() (the only caller of this
    // endpoint currently in this codebase) only ever reads
    // response.count - 'senders' isn't used anywhere I can see. Kept
    // it in the response for backward compatibility in case something
    // else depends on it, just made cheap to compute either way.
    $count = DB::table('messages')
        ->where('receiver_id', Auth::id())
        ->where('is_read', 0)
        ->count();

    $senders = DB::table('messages')
        ->join('users', 'users.id', '=', 'messages.sender_id')
        ->where('messages.receiver_id', Auth::id())
        ->where('messages.is_read', 0)
        ->distinct()
        ->pluck('users.name');

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

        // FIX: this was forgetting "typing_$receiverId" - the OTHER
        // person's id - which is a different cache key entirely from
        // the one actually set above ("typing_$senderId", keyed by
        // whoever is doing the typing). getTyping()/poll() both read
        // back "typing_{the other person's id}" from the CURRENT
        // viewer's perspective, which is this same $senderId here - so
        // the key must match on both branches. Previously "stop typing"
        // never actually cleared anything; the indicator only ever went
        // away via the 2-second cache expiry, not immediately when the
        // person stopped typing.
        Cache::forget("typing_$senderId");
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
}