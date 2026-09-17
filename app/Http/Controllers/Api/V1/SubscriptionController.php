<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Website;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function subscribe(Request $request, Website $website): JsonResponse
    {
        return $this->subscriptionService->subscribe($request, $website);
    }

    public function unsubscribe(Request $request, Website $website): JsonResponse
    {
        return $this->subscriptionService->unsubscribe($request, $website);
    }

    public function subscribers(Website $website): JsonResponse
    {
        return $this->subscriptionService->subscribers($website);
    }
}
