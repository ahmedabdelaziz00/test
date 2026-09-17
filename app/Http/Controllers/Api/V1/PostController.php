<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Website;
use Illuminate\Http\JsonResponse;
use App\Events\PostPublished;


class PostController extends Controller
{
    public function index(Website $website): JsonResponse
    {
        // Return a list of posts for the specified website
        $posts = $website->posts()->latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $posts,
        ]);
    }

    public function store(Request $request, Website $website): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $post = $website->posts()->create($validated);

        event(new PostPublished($post));

        return response()->json([
            'status' => 'success',
            'data' => $post,
        ], 201);
    }

    public function getPost(Website $website, Post $post): JsonResponse
    {
        if ($post->website_id !== $website->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post does not belong to the specified website',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $post,
        ]);
    }
}
