<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
    public function list(Request $request)
    {
        if ($request->ajax()) {

            $query = Attendance::with('user')->latest('date');

            // Search by user name
            if ($request->search_value) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->search_value.'%');
                });
            }

            // Date filter
            if ($request->date) {
                $query->whereDate('date', $request->date);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" value="'.$row->id.'">';
                })
                ->addColumn('id', fn ($row) => $row->id)
                ->addColumn('user_name', fn ($row) => $row->user->name)
                ->addColumn('check_in', function ($row) {
                    return $row->check_in_time
                        ? Carbon::parse($row->check_in_time)
                            ->timezone('Asia/Kolkata')
                            ->format('h:i A')
                        : '-';
                })
                ->addColumn('check_out', function ($row) {
                    return $row->check_out_time
                        ? Carbon::parse($row->check_out_time)
                            ->timezone('Asia/Kolkata')
                            ->format('h:i A')
                        : '-';
                })
                ->addColumn('date', fn ($row) => Carbon::parse($row->date)
                    ->timezone('Asia/Kolkata')
                    ->format('d-m-Y')
                )
                ->addColumn('action', function ($row) {
                    return '<a href="'.route('attendance.show', $row->id).'" class="btn btn-primary btn-sm">View</a>';
                })
                ->rawColumns(['checkbox', 'selfie', 'action'])
                ->make(true);
        }

        return view('attendance-list');
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'selfie' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();
        $today = now()->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today]
        );

        if ($attendance->check_in_time) {
            return response()->json(['message' => 'Already checked in today.'], 400);
        }

        $file = $request->file('selfie');
        $filename = time().'_'.$user->id.'_checkin.jpg';
        $file->move(public_path('upload/selfies'), $filename);

        $attendance->update([
            'check_in_time' => now(),
            'check_in_selfie' => 'upload/selfies/'.$filename,
        ]);

        return response()->json(['message' => 'Checked in successfully.']);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'selfie' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (! $attendance || ! $attendance->check_in_time) {
            return response()->json(['message' => 'You need to check in first.'], 400);
        }

        if ($attendance->check_out_time) {
            return response()->json(['message' => 'Already checked out today.'], 400);
        }

        $file = $request->file('selfie');
        $filename = time().'_'.$user->id.'_checkout.jpg';
        $file->move(public_path('upload/selfies'), $filename);

        $attendance->update([
            'check_out_time' => now(),
            'check_out_selfie' => 'upload/selfies/'.$filename,
        ]);

        return response()->json(['message' => 'Checked out successfully.']);
    }

    public function index()
    {
        $user = auth()->user();
        $attendances = Attendance::where('user_id', $user->id)->orderBy('date', 'desc')->get();

        return view('attendance', compact('attendances'));
    }

    public function show($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        return view('show-attendance', compact('attendance'));

    }
}