<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Get user profile by ID
     */
    public function show($id)
    {
        $user = User::withCount(['posts', 'followers', 'following'])
            ->findOrFail($id);

        return response()->json($user);
    }

    /**
     * Update authenticated user's profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|string',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
        ]);

        $user->update($request->only(['name', 'bio', 'avatar', 'email']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Password is incorrect'
            ], 422);
        }

        // Delete user's tokens
        $user->tokens()->delete();

        // Delete user
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }

    /**
     * Get user's followers
     */
    public function followers($id)
    {
        $user = User::findOrFail($id);
        $followers = $user->followers()->paginate(20);

        return response()->json($followers);
    }

    /**
     * Get user's following
     */
    public function following($id)
    {
        $user = User::findOrFail($id);
        $following = $user->following()->paginate(20);

        return response()->json($following);
    }

    /**
     * Get user's posts
     */
    public function posts($id)
    {
        $posts = \App\Models\Post::where('user_id', $id)
            ->with('user')
            ->withCount('allComments')
            ->published()
            ->latest('published_at')
            ->paginate(10);

        return response()->json($posts);
    }

    /**
     * Get user's drafts (only own drafts)
     */
    public function drafts(Request $request)
    {
        $posts = $request->user()->posts()
            ->with('user')
            ->withCount('allComments')
            ->whereNull('published_at')
            ->latest('created_at')
            ->paginate(10);

        return response()->json($posts);
    }

    /**
     * Get user statistics
     */
    public function stats($id)
    {
        $user = User::findOrFail($id);

        $stats = [
            'posts_count' => $user->posts()->published()->count(),
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
            'total_views' => 0, // You can implement views tracking later
        ];

        return response()->json($stats);
    }
}