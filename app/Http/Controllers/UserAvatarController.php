<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserAvatarController extends Controller
{
    /**
     * Update the avatar for the user.
     * Corresponds to "File Uploads" and "File Visibility".
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|file|image|max:2048',
        ]);

        $user = $request->user();
        $file = $request->file('avatar');

        // Delete old avatar if it exists (assuming user has an avatar_path field)
        // Corresponds to "Deleting Files"
        if ($user && isset($user->avatar_path) && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Store the file with public visibility using the 'public' disk
        // Corresponds to "File Visibility" ($file->storePublicly)
        $path = $file->storePublicly('avatars', 'public');

        // Retrieve Other Uploaded File Information
        $info = [
            'path' => $path,
            'name' => $file->hashName(),
            'original_name' => $file->getClientOriginalName(),
            'extension' => $file->extension(),
            'url' => Storage::disk('public')->url($path),
            'visibility' => Storage::disk('public')->getVisibility($path),
        ];

        // In a real scenario, you would update the user model here:
        // $user->avatar_path = $path;
        // $user->save();

        return response()->json($info);
    }

    /**
     * Update avatar with a specific filename based on User ID.
     * Corresponds to "Specifying a File Name" (storeAs).
     */
    public function updateNamed(Request $request): JsonResponse
    {
        $request->validate(['avatar' => 'required|file|image']);

        $user = $request->user();
        $id = $user ? $user->id : 'guest';
        $extension = $request->file('avatar')->extension();

        // Store as: avatars/{user_id}.{extension}
        $path = $request->file('avatar')->storeAs(
            'avatars',
            "{$id}.{$extension}",
            'public'
        );

        return response()->json(['path' => $path]);
    }
}