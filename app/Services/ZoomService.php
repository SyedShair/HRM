<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * Thin wrapper around Zoom's REST API using a Server-to-Server OAuth app.
 * No user ever has to log in to Zoom — the HRM authenticates as itself.
 */
class ZoomService
{
    protected string $baseUrl = 'https://api.zoom.us/v2';
    protected string $tokenUrl = 'https://zoom.us/oauth/token';

    protected string $accountId;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->accountId    = config('services.zoom.account_id');
        $this->clientId     = config('services.zoom.client_id');
        $this->clientSecret = config('services.zoom.client_secret');
    }

    /**
     * Get (and cache) a Server-to-Server OAuth access token.
     * Tokens last 1 hour — cached for 55 minutes to be safe.
     */
    protected function accessToken(): string
    {
        return Cache::remember('zoom_access_token', 3300, function () {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post($this->tokenUrl, [
                    'grant_type' => 'account_credentials',
                    'account_id' => $this->accountId,
                ]);

            if (!$response->successful()) {
                Log::error('Zoom token request failed', ['body' => $response->body()]);
                throw new Exception('Unable to authenticate with Zoom. Check your Zoom app credentials.');
            }

            return $response->json('access_token');
        });
    }

    protected function client()
    {
        return Http::withToken($this->accessToken())
            ->baseUrl($this->baseUrl)
            ->acceptJson();
    }

    /**
     * Create a scheduled Zoom meeting.
     *
     * @param array $data ['topic','agenda','start_time' (parseable datetime, local tz),'duration','timezone']
     * @return array Zoom's response — includes id, join_url, start_url, password
     */
    public function createMeeting(array $data): array
    {
        $payload = [
            'topic'      => $data['topic'],
            'type'       => 2, // scheduled meeting
            'start_time' => Carbon::parse($data['start_time'])->format('Y-m-d\TH:i:s'),
            'duration'   => $data['duration'] ?? 30,
            'timezone'   => $data['timezone'] ?? 'Europe/London',
            'agenda'     => $data['agenda'] ?? '',
            'settings'   => [
                'join_before_host'       => false,
                'waiting_room'           => true,
                'host_video'             => true,
                'participant_video'      => true,
                'auto_recording'         => 'cloud', // so we can pull the recording back later
                'meeting_authentication' => false,
            ],
        ];

        $response = $this->client()->post('/users/me/meetings', $payload);

        if (!$response->successful()) {
            Log::error('Zoom createMeeting failed', ['body' => $response->body()]);
            throw new Exception('Zoom rejected the meeting request: '.$response->body());
        }

        return $response->json();
    }

    public function updateMeeting(string $zoomMeetingId, array $data): bool
    {
        $payload = array_filter([
            'topic'      => $data['topic'] ?? null,
            'start_time' => isset($data['start_time']) ? Carbon::parse($data['start_time'])->format('Y-m-d\TH:i:s') : null,
            'duration'   => $data['duration'] ?? null,
            'timezone'   => $data['timezone'] ?? null,
            'agenda'     => $data['agenda'] ?? null,
        ], fn ($v) => !is_null($v));

        $response = $this->client()->patch("/meetings/{$zoomMeetingId}", $payload);

        return $response->successful();
    }

    public function deleteMeeting(string $zoomMeetingId): bool
    {
        $response = $this->client()->delete("/meetings/{$zoomMeetingId}");

        // 404 means it's already gone on Zoom's side — treat as success.
        return $response->successful() || $response->status() === 404;
    }

    public function getMeeting(string $zoomMeetingId): ?array
    {
        $response = $this->client()->get("/meetings/{$zoomMeetingId}");

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Pull cloud recordings for a meeting once it has ended.
     * Zoom usually takes a few minutes to process the recording after the call ends.
     */
    public function getRecordings(string $zoomMeetingId): ?array
    {
        $response = $this->client()->get("/meetings/{$zoomMeetingId}/recordings");

        if ($response->status() === 404) {
            return null; // no recording available yet
        }

        if (!$response->successful()) {
            Log::error('Zoom getRecordings failed', ['body' => $response->body()]);
            return null;
        }

        return $response->json();
    }
}