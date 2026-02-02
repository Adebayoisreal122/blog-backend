<?php

// app/Http/Controllers/PostController.php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
public function index()
{
    $posts = Post::with('user')
        ->withCount('allComments')  // Add this line to count all comments
        ->published()
        ->latest('published_at')
        ->paginate(10);

    return response()->json($posts);
}

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        $post = $request->user()->posts()->create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(6),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'featured_image' => $request->featured_image,
            'published_at' => $request->published_at ?? now(),
        ]);

        return response()->json($post->load('user'), 201);
    }

    public function show($slug)
    {
        $post = Post::with(['user', 'comments.user', 'comments.replies.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($post);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        if ($request->has('title') && $request->title !== $post->title) {
            $post->slug = Str::slug($request->title) . '-' . Str::random(6);
        }

        $post->update($request->only(['title', 'content', 'excerpt', 'featured_image', 'published_at']));

        return response()->json($post->load('user'));
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function userPosts($userId)
    {
        $posts = Post::where('user_id', $userId)
            ->published()
            ->latest('published_at')
            ->paginate(10);

        return response()->json($posts);
    }
}