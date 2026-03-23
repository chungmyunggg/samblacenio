<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Logic to run when a user is created
        Log::info("User created: {$user->id} - {$user->name}");
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Logic to run when a user is updated
        // Example: Check if specific fields changed
        if ($user->isDirty('email')) {
            Log::info("User email changed for ID: {$user->id}");
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Logic to run when a user is deleted
        Log::info("User deleted: {$user->id}");
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        Log::info("User restored: {$user->id}");
    }

    /**
     * Handle the User "forceDeleted" event.
     */
    public function forceDeleted(User $user): void
    {
        Log::info("User force deleted: {$user->id}");
    }
}