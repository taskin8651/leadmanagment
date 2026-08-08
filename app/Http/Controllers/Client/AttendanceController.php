<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    private function client()
    {
        return Auth::user()->client;
    }

    public function index(Request $request)
    {
        $clientId = $this->client()->id;

        $attendances = Attendance::with('user.roles')
            ->where('client_id', $clientId)
            ->when($request->user_id, fn ($q, $v) => $q->where('user_id', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->date_from, fn ($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($request->date_to, fn ($q, $v) => $q->whereDate('date', '<=', $v))
            ->latest('date')->latest('check_in_at')
            ->paginate(20)->withQueryString();

        $kpis = [
            'today' => Attendance::where('client_id', $clientId)->whereDate('date', today())->count(),
            'present' => Attendance::where('client_id', $clientId)->whereDate('date', today())->where('status', 'present')->count(),
            'late' => Attendance::where('client_id', $clientId)->whereDate('date', today())->where('status', 'late')->count(),
            'active_now' => Attendance::where('client_id', $clientId)->whereDate('date', today())->whereNotNull('check_in_at')->whereNull('check_out_at')->count(),
        ];

        $staff = \App\Models\User::where('client_id', $clientId)->get(['id', 'name']);

        $myToday = Attendance::where('client_id', $clientId)
            ->where('user_id', Auth::id())
            ->whereDate('date', today())
            ->latest('check_in_at')
            ->first();

        return view('client.attendances.index', compact('attendances', 'kpis', 'staff', 'myToday'));
    }

    public function show(Attendance $attendance)
    {
        abort_unless($attendance->client_id === $this->client()->id, 403);
        $attendance->load('user.roles');
        return view('client.attendances.show', compact('attendance'));
    }

    public function checkIn(Request $request)
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $existing = Attendance::where('client_id', $this->client()->id)
            ->where('user_id', Auth::id())
            ->whereDate('date', today())
            ->whereNull('check_out_at')
            ->first();

        if ($existing) {
            return back()->with('error', 'You are already checked in today.');
        }

        Attendance::create([
            'client_id' => $this->client()->id,
            'user_id' => Auth::id(),
            'date' => today(),
            'check_in_at' => now(),
            'check_in_latitude' => $data['latitude'],
            'check_in_longitude' => $data['longitude'],
            'check_in_address' => $data['address'] ?? null,
            'status' => now()->format('H:i') > '10:00' ? 'late' : 'present',
        ]);

        return back()->with('success', 'Checked in successfully.');
    }

    public function checkOut(Request $request)
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $attendance = Attendance::where('client_id', $this->client()->id)
            ->where('user_id', Auth::id())
            ->whereDate('date', today())
            ->whereNull('check_out_at')
            ->latest('check_in_at')
            ->first();

        if (!$attendance) {
            return back()->with('error', 'You have not checked in yet.');
        }

        $attendance->update([
            'check_out_at' => now(),
            'check_out_latitude' => $data['latitude'],
            'check_out_longitude' => $data['longitude'],
            'check_out_address' => $data['address'] ?? null,
        ]);

        return back()->with('success', 'Checked out successfully.');
    }
}
