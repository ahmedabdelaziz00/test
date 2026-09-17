<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionService
{
    public function subscribe(Request $request, Website $website): JsonResponse
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            ['name'  => $validated['name']],
        );

        $alreadySubscribed = Subscription::where('user_id', $user->id)
            ->where('website_id', $website->id)
            ->exists();

        if ($alreadySubscribed) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User is already subscribed to this website',
            ], 409);
        }

        Subscription::create([
            'user_id'    => $user->id,
            'website_id' => $website->id,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Subscribed successfully',
        ], 201);
    }

    public function unsubscribe(Request $request, Website $website): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $subscription = Subscription::where('user_id', $user->id)
            ->where('website_id', $website->id)
            ->first();

        if (!$subscription) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User is not subscribed to this website',
            ], 404);
        }

        $subscription->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Unsubscribed successfully',
        ]);
    }

    public function subscribers(Website $website): JsonResponse
    {
        $subscribers = $website->subscriptions()->with('user:id,name,email')->get()
            ->pluck('user');

        return response()->json([
            'status' => 'success',
            'data'   => $subscribers,
        ]);
    }
}
