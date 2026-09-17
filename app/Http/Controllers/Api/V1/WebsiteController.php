<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use Illuminate\Http\JsonResponse;

class WebsiteController extends Controller
{
    public function index(): JsonResponse
    {
        // Return a list of all websites
        $websites = Website::all();
        return response()->json([
            'status' => 'success',
            'data' => $websites,
        ]);
    }
}
