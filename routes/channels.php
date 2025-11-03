<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('user.{userId}', function ($user, $userId) {
    // Verify user is authenticated and matches the channel user ID
    return $user && (int) $user->id === (int) $userId && $user->is_active;
});

// Course-specific broadcast channel
Broadcast::channel('course.{courseId}', function ($user, $courseId) {
    // Users can listen if they are enrolled, instructor, or admin
    return $user && (
        $user->enrollments()->where('course_id', $courseId)->exists() ||
        $user->courses()->where('id', $courseId)->exists() ||
        $user->isAdmin()
    ) && $user->is_active;
});


