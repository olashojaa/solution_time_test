<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    //
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);

        if ($notification) {
            $notification->update(['read_at' => now()]);
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Notification not found.']);
    }
}
