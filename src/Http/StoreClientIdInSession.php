<?php

namespace DevPro\GA4EventTracking\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreClientIdInSession
{
    /**
     * Stores the posted Client ID in the session.
     */
    public function __invoke(Request $request, ClientIdSession $clientIdSession): JsonResponse
    {
        $data = $request->validate(['client_id' => 'required|string|max:255']);

        $clientIdSession->update($data['client_id']);

        return response()->json();
    }
}
