<?php

// app/Http/Controllers/FollowController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Cannot follow yourself'], 400);
        }

        if ($request->user()->isFollowing($user->id)) {
            return response()->json(['message' => 'Already following this user'], 400);
        }

        $request->user()->following()->attach($user->id);

        return response()->json(['message' => 'Successfully followed user']);
    }

    public function unfollow(Request $request, User $user)
    {
        if (!$request->user()->isFollowing($user->id)) {
            return response()->json(['message' => 'Not following this user'], 400);
        }

        $request->user()->following()->detach($user->id);

        return response()->json(['message' => 'Successfully unfollowed user']);
    }

    public function followers(User $user)
    {
        $followers = $user->followers()->paginate(20);

        return response()->json($followers);
    }

    public function following(User $user)
    {
        $following = $user->following()->paginate(20);

        return response()->json($following);
    }
}
