<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
    public function list(Request $request)
    {
        if ($request->ajax()) {

            $query = Attendance::with('user')
                ->whereHas('user', function ($q) {
                    $q->whereNull('deleted_at');
                })
                ->latest('date');

            // Filter by USER
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Filter by DATE RANGE
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            } elseif ($request->filled('start_date')) {
                $query->whereDate('date', '>=', $request->start_date);
            } elseif ($request->filled('end_date')) {
                $query->whereDate('date', '<=', $request->end_date);
            }

            return DataTables::of($query)
                ->addColumn('checkbox', fn ($row) => '<input type="checkbox" value="'.$row->id.'">'
                )
                ->addColumn('user_name', fn ($row) => $row->user ? $row->user->name : 'Deleted User')
                ->addColumn('check_in', fn ($row) => $row->check_in_time
                        ? Carbon::parse($row->check_in_time)
                            ->timezone('Asia/Kolkata')
                            ->format('h:i A')
                        : '-'
                )
                ->addColumn('check_out', fn ($row) => $row->check_out_time
                        ? Carbon::parse($row->check_out_time)
                            ->timezone('Asia/Kolkata')
                            ->format('h:i A')
                        : '-'
                )
                ->addColumn('date', fn ($row) => Carbon::parse($row->date)
                    ->timezone('Asia/Kolkata')
                    ->format('d-m-Y')
                )
                ->addColumn('action', fn ($row) => '<button class="btn btn-primary btn-sm show-details" data-id="'.$row->id.'">View</button>'
                )
                ->rawColumns(['checkbox', 'action'])
                ->make(true);
        }


        $users = User::whereIn('role', ['user', 'manager'])
            ->whereNull('deleted_at')
            ->select('id', 'name')
            ->get();

        return view('attendance-list', compact('users'));
    }

    public function checkIn(Request $request)
    {
        \Log::info('CheckIn Request Received');
        \Log::info('Request data: '.print_r($request->all(), true));
        try {
            $authUser = auth()->user();

            // Validate selfie only if not admin
            $rules = [
                'user_id' => 'nullable|exists:users,id',
            ];
            if ($authUser->role !== 'admin') {
                $rules['selfie'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
            }

            $request->validate($rules);

            // Determine target user
            $userId = $request->user_id ?? $authUser->id;
            if ($request->user_id && ! in_array($authUser->role, ['admin', 'manager'])) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $today = now()->toDateString();

            $attendance = Attendance::where('user_id', $userId)
                ->where('date', $today)
                ->latest('id')
                ->first();

            if ($attendance && $attendance->check_in_time && $attendance->check_out_time) {
                $attendance = Attendance::create([
                    'user_id' => $userId,
                    'date' => $today,
                    'attended_by' => $authUser->id,
                ]);
            } elseif (! $attendance) {
                $attendance = Attendance::create([
                    'user_id' => $userId,
                    'date' => $today,
                    'attended_by' => $authUser->id,
                ]);
            }

            $updateData = [
                'check_in_time' => now(),
                'attended_by' => $authUser->id,
            ];

            if ($request->hasFile('selfie')) {
                $filename = time().'_'.$userId.'_checkin.jpg';
                $request->file('selfie')->move(public_path('upload/selfies'), $filename);
                $updateData['check_in_selfie'] = 'upload/selfies/'.$filename;
            }

            $attendance->update($updateData);

            return response()->json(['message' => 'Checked in successfully.']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('CheckIn validation error: '.print_r($e->errors(), true));

            return response()->json(['message' => 'Validation failed: '.implode(', ', $e->errors()), 422]);
        } catch (\Exception $e) {
            \Log::error('CheckIn error: '.$e->getMessage());
            \Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function checkOut(Request $request)
    {
        \Log::info('CheckOut Request Received');
        \Log::info('Request data: '.print_r($request->all(), true));
        try {
            $authUser = auth()->user();

            // Validate selfie only if not admin
            $rules = [];
            if ($authUser->role !== 'admin') {
                $rules['selfie'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
            }

            $request->validate($rules);

            // Determine which user to check out
            $userId = $request->user_id ? $request->user_id : auth()->id();
            if ($request->user_id && ! in_array($authUser->role, ['admin', 'manager'])) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            $today = now()->toDateString();

            $attendance = Attendance::where('user_id', $userId)
                ->where('date', $today)
                ->whereNotNull('check_in_time')
                ->whereNull('check_out_time')
                ->latest('check_in_time')
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('CheckOut validation error: '.print_r($e->errors(), true));

            return response()->json(['message' => 'Validation failed: '.implode(', ', $e->errors()), 422]);
        } catch (\Exception $e) {
            \Log::error('CheckOut error: '.$e->getMessage());
            \Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json(['message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    public function index()
    {
        $user = auth()->user();
        $attendances = Attendance::where('user_id', $user->id)->orderBy('date', 'desc')->get();

        return view('attendance', compact('attendances'));
    }

    //Show attendance modal
    public function show($id)
    {
        $attendance = Attendance::with('user', 'attendedBy')->findOrFail($id);

        return response()->json($attendance);
    }

    //Export attendance
    public function export(Request $request)
    {
        $query = Attendance::with('user')
            ->whereHas('user', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->latest('date');

        // Filter by USER
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by DATE RANGE
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $attendances = $query->get();

        return response()->json([
            'data' => $attendances->map(function ($attendance) {
                return [
                    'date' => Carbon::parse($attendance->date)
                        ->timezone('Asia/Kolkata')
                        ->format('d-m-Y'),
                    'user_name' => $attendance->user ? $attendance->user->name : 'Deleted User',
                    'check_in' => $attendance->check_in_time
                        ? Carbon::parse($attendance->check_in_time)
                            ->timezone('Asia/Kolkata')
                            ->format('h:i A')
                        : '-',
                    'check_out' => $attendance->check_out_time
                        ? Carbon::parse($attendance->check_out_time)
                            ->timezone('Asia/Kolkata')
                            ->format('h:i A')
                        : '-',
                ];
            }),
        ]);
    }

    //Generate report
    public function generateReport(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $userId = $request->user_id;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $user = User::find($userId);

        // Get all dates in the range
        $allDates = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $allDates[] = $currentDate->toDateString();
            $currentDate->addDay();
        }

        // Get attendances for the user and date range
        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy('date');

        // Calculate report data
        $totalWorkingDays = 0;
        $totalWorkingHours = 0;
        $absentDates = [];
        $dailyRecords = [];

        foreach ($allDates as $date) {
            if (isset($attendances[$date])) {
                $attendance = $attendances[$date];
                $dailyRecords[] = [
                    'date' => $date,
                    'check_in' => $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->timezone('Asia/Kolkata')->format('h:i A') : '-',
                    'check_out' => $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->timezone('Asia/Kolkata')->format('h:i A') : '-',
                    'hours' => $this->calculateWorkingHours($attendance->check_in_time, $attendance->check_out_time),
                ];

                if ($attendance->check_in_time && $attendance->check_out_time) {
                    $totalWorkingDays++;
                    $totalWorkingHours += $this->calculateWorkingHoursInMinutes($attendance->check_in_time, $attendance->check_out_time);
                }
            } else {
                $dailyRecords[] = [
                    'date' => $date,
                    'check_in' => '-',
                    'check_out' => '-',
                    'hours' => '-',
                ];
                $absentDates[] = $date;
            }
        }

        // Convert total working hours from minutes to hours with decimal places
        $totalWorkingHours = round($totalWorkingHours / 60, 2);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'date_range' => [
                'start' => $startDate->format('d-m-Y'),
                'end' => $endDate->format('d-m-Y'),
            ],
            'summary' => [
                'total_days' => count($allDates),
                'working_days' => $totalWorkingDays,
                'absent_days' => count($absentDates),
                'total_working_hours' => $totalWorkingHours,
            ],
            'absent_dates' => array_map(function ($date) {
                return Carbon::parse($date)->format('d-m-Y');
            }, $absentDates),
            'daily_records' => array_map(function ($record) {
                $record['date'] = Carbon::parse($record['date'])->format('d-m-Y');

                return $record;
            }, $dailyRecords),
        ]);
    }

    // Calculate working hours
    private function calculateWorkingHours($checkIn, $checkOut)
    {
        if (! $checkIn || ! $checkOut) {
            return '-';
        }

        $checkInTime = Carbon::parse($checkIn);
        $checkOutTime = Carbon::parse($checkOut);

        $diff = $checkInTime->diff($checkOutTime);

        return sprintf('%02d:%02d', $diff->h, $diff->i);
    }

    //  Calculate working hours in minutes
    private function calculateWorkingHoursInMinutes($checkIn, $checkOut)
    {
        if (! $checkIn || ! $checkOut) {
            return 0;
        }

        $checkInTime = Carbon::parse($checkIn);
        $checkOutTime = Carbon::parse($checkOut);

        return $checkInTime->diffInMinutes($checkOutTime);
    }
}