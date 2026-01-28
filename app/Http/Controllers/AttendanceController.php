<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $request->validate([
            'selfie' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if (! $attendance) {
            $attendance = new Attendance;
            $attendance->user_id = $user->id;
            $attendance->date = $today;
        }

        if ($attendance->check_in_time) {
            return response()->json(['message' => 'Already checked in today.'], 400);
        }

        $path = $request->file('selfie')->store('attendances', 'public');
        $attendance->check_in_time = now();
        $attendance->check_in_selfie = $path;
        $attendance->save();

        return response()->json(['message' => 'Checked in successfully.']);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'selfie' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if (! $attendance || ! $attendance->check_in_time) {
            return response()->json(['message' => 'You need to check in first.'], 400);
        }

        if ($attendance->check_out_time) {
            return response()->json(['message' => 'Already checked out today.'], 400);
        }

        $path = $request->file('selfie')->store('attendances', 'public');
        $attendance->check_out_time = now();
        $attendance->check_out_selfie = $path;
        $attendance->save();

        return response()->json(['message' => 'Checked out successfully.']);
    }

    public function index()
    {
        $user = auth()->user();
        $attendances = Attendance::where('user_id', $user->id)->orderBy('date', 'desc')->get();

        return view('attendance', compact('attendances'));
    }
}
