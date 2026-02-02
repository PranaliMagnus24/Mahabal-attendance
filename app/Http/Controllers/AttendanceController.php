<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;

class AttendanceController extends Controller
{
    public function list(Request $request)
{
    if ($request->ajax()) {

        $query = Attendance::with('user')
            ->latest('date');

        // ✅ Filter by USER
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // ✅ Filter by DATE
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('checkbox', fn ($row) =>
                '<input type="checkbox" value="'.$row->id.'">'
            )
            ->addColumn('user_name', fn ($row) => $row->user->name)
            ->addColumn('check_in', fn ($row) =>
                $row->check_in_time
                    ? Carbon::parse($row->check_in_time)
                        ->timezone('Asia/Kolkata')
                        ->format('h:i A')
                    : '-'
            )
            ->addColumn('check_out', fn ($row) =>
                $row->check_out_time
                    ? Carbon::parse($row->check_out_time)
                        ->timezone('Asia/Kolkata')
                        ->format('h:i A')
                    : '-'
            )
            ->addColumn('date', fn ($row) =>
                Carbon::parse($row->date)
                    ->timezone('Asia/Kolkata')
                    ->format('d-m-Y')
            )
            ->addColumn('action', fn ($row) =>
                '<button class="btn btn-primary btn-sm show-details" data-id="'.$row->id.'">View</button>'
            )
            ->rawColumns(['checkbox', 'action'])
            ->make(true);
    }

    // 👇 Users for dropdown (role = user)
    $users = User::where('role', 'user')->select('id','name')->get();

    return view('attendance-list', compact('users'));
}


   public function checkIn(Request $request)
{
    try {
        $request->validate([
            'selfie' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'user_id' => 'nullable|exists:users,id'
        ]);

        // Logged-in user
        $authUser = auth()->user();

        // Determine target user
        $userId = $request->user_id ?? $authUser->id;

        // Manager authorization
        if ($request->user_id && $authUser->role !== 'manager') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $today = now()->toDateString();

        // Get today's attendance
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        // If already checked in
        if ($attendance && $attendance->check_in_time) {
            return response()->json(['message' => 'Already checked in today.'], 400);
        }

        // If record does not exist → create
        if (! $attendance) {
            $attendance = Attendance::create([
                'user_id'     => $userId,
                'date'        => $today,
                'attended_by' => $authUser->id,
            ]);
        }

        $updateData = [
            'check_in_time' => now(),
            'attended_by'   => $authUser->id,
        ];

        if ($request->hasFile('selfie')) {
            $filename = time().'_'.$userId.'_checkin.jpg';
            $request->file('selfie')->move(public_path('upload/selfies'), $filename);
            $updateData['check_in_selfie'] = 'upload/selfies/'.$filename;
        }

        $attendance->update($updateData);

        return response()->json(['message' => 'Checked in successfully.']);

    } catch (\Exception $e) {
        \Log::error('CheckIn error: '.$e->getMessage());
        return response()->json(['message' => 'Something went wrong'], 500);
    }
}

    public function checkOut(Request $request)
    {
        try {
            $request->validate([
                'selfie' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Determine which user to check out
            $userId = $request->user_id ? $request->user_id : auth()->id();

            // If manager is trying to check out another user, verify role
            if ($request->user_id && auth()->user()->role !== 'manager') {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $today = now()->toDateString();

            $attendance = Attendance::where('user_id', $userId)
                ->where('date', $today)
                ->first();

            if (! $attendance || ! $attendance->check_in_time) {
                return response()->json(['message' => 'You need to check in first.'], 400);
            }

            if ($attendance->check_out_time) {
                return response()->json(['message' => 'Already checked out today.'], 400);
            }

            $updateData = [
                'check_out_time' => now(),
                'attended_by' => auth()->id(),
            ];

            if ($request->hasFile('selfie')) {
                $file = $request->file('selfie');
                $filename = time().'_'.$userId.'_checkout.jpg';
                $file->move(public_path('upload/selfies'), $filename);
                $updateData['check_out_selfie'] = 'upload/selfies/'.$filename;
            }

            $attendance->update($updateData);

            return response()->json(['message' => 'Checked out successfully.']);
        } catch (\Exception $e) {
            \Log::error('CheckOut error: '.$e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json(['message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    public function index()
    {
        $user = auth()->user();
        $attendances = Attendance::where('user_id', $user->id)->orderBy('date', 'desc')->get();

        return view('attendance', compact('attendances'));
    }

    public function show($id)
    {
        $attendance = Attendance::with('user', 'attendedBy')->findOrFail($id);

        return view('show-attendance', compact('attendance'));

    }
}
