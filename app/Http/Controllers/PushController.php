<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint'                  => ['required', 'url'],
            'keys.p256dh'               => ['required', 'string'],
            'keys.auth'                 => ['required', 'string'],
        ]);

        PushSubscription::updateOrCreate(
            ['user_id' => auth()->id(), 'endpoint' => $data['endpoint']],
            ['public_key' => $data['keys']['p256dh'], 'auth_token' => $data['keys']['auth']]
        );

        return response()->json(['ok' => true]);
    }
}
