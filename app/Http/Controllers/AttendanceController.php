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
                ->addColumn('selfie', function ($row) {
                    $selfieHtml = '';
                    if ($row->check_in_selfie) {
                        $selfieHtml .= '<img src="'.asset('storage/'.$row->check_in_selfie).'" alt="Check In Selfie" class="img-thumbnail" style="width: 50px; height: 50px; margin-right: 5px;">';
                    }
                    if ($row->check_out_selfie) {
                        $selfieHtml .= '<img src="'.asset('storage/'.$row->check_out_selfie).'" alt="Check Out Selfie" class="img-thumbnail" style="width: 50px; height: 50px;">';
                    }

                    return $selfieHtml ?: 'No Selfie';
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-primary btn-sm show-details" data-id="'.$row->id.'">Show</button>';
                })
                ->rawColumns(['checkbox', 'selfie', 'action'])
                ->make(true);
        }

        return view('attendance-list');
    }

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

    public function show($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);

        return response()->json($attendance);
    }
}
