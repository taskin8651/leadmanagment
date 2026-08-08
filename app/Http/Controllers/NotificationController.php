<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function read(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? url()->previous());
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return back();
    }
}
