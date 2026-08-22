<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Receives events from Zoom (recording.completed, meeting.started, meeting.ended).
 * Configure this URL in your Zoom app's "Event Subscriptions" once the site
 * is on a public HTTPS domain. See SETUP-INSTRUCTIONS.md for the exact steps.
 */
class ZoomWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.zoom.webhook_secret');
        $payload = $request->all();
        $event = $payload['event'] ?? null;

        // Zoom's one-time endpoint URL validation handshake.
        if ($event === 'endpoint.url_validation') {
            $plainToken = $payload['payload']['plainToken'];
            $encryptedToken = hash_hmac('sha256', $plainToken, $secret);

            return response()->json([
                'plainToken'     => $plainToken,
                'encryptedToken' => $encryptedToken,
            ]);
        }

        // Verify the signature on every real event.
        $timestamp = $request->header('x-zm-request-timestamp');
        $signature = $request->header('x-zm-signature');
        $message = "v0:{$timestamp}:".$request->getContent();
        $expected = 'v0='.hash_hmac('sha256', $message, $secret);

        if (!hash_equals($expected, (string) $signature)) {
            Log::warning('Zoom webhook signature mismatch');
            return response()->json(['message' => 'invalid signature'], 401);
        }

        if ($event === 'recording.completed') {
            $object = $payload['payload']['object'] ?? [];
            $zoomMeetingId = (string) ($object['id'] ?? '');

            if ($zoomMeetingId) {
                $transcript = collect($object['recording_files'] ?? [])->firstWhere('file_type', 'TRANSCRIPT');

                DB::table('meetings')->where('zoom_meeting_id', $zoomMeetingId)->update([
                    'status'              => 'ended',
                    'recording_url'       => $object['share_url'] ?? null,
                    'recording_password'  => $object['password'] ?? null,
                    'transcript_url'      => $transcript['download_url'] ?? null,
                    'updated_at'          => now(),
                ]);
            }
        }

        if ($event === 'meeting.started') {
            $zoomMeetingId = (string) ($payload['payload']['object']['id'] ?? '');
            if ($zoomMeetingId) {
                DB::table('meetings')->where('zoom_meeting_id', $zoomMeetingId)->update(['status' => 'started']);
            }
        }

        if ($event === 'meeting.ended') {
            $zoomMeetingId = (string) ($payload['payload']['object']['id'] ?? '');
            if ($zoomMeetingId) {
                DB::table('meetings')->where('zoom_meeting_id', $zoomMeetingId)
                    ->where('status', '!=', 'ended')
                    ->update(['status' => 'ended']);
            }
        }

        return response()->json(['message' => 'ok']);
    }
}