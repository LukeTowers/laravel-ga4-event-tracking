<?php

namespace LukeTowers\GA4EventTracking\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreClientIdInSession
{
    /**
     * Stores the posted GA4 Client ID or Session ID in the session.
     */
    public function __invoke(Request $request, ClientIdSession $clientIdSession, SessionIdSession $sessionIdSession): JsonResponse
    {
        if ($request->has('client_id')) {
            $data = $request->validate(['client_id' => 'required|string|max:255']);
            $clientIdSession->update($data['client_id']);
        }

        if ($request->has('session_id')) {
            $data = $request->validate(['session_id' => 'required|string|max:255']);
            $sessionIdSession->update($data['session_id']);
        }

        return response()->json();
    }
}
