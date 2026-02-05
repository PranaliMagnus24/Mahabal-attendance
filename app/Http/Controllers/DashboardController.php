<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $authRole = auth()->user()->role;

        if ($request->ajax()) {

            $query = User::where('role', '!=', 'admin');
            if ($authRole === 'manager') {
                $query->where('status', 'active');
                }

            if ($request->search_value) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->search_value.'%')
                        ->orWhere('phone', 'like', '%'.$request->search_value.'%');
                });
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addColumn('checkbox', fn ($row) => '<input type="checkbox" value="'.$row->id.'">'
                )
                ->addColumn('status', function ($row) {
                    if ($row->status === 'active') {
                        return '<span class="badge bg-success">Active</span>';
                        }
                        return '<span class="badge bg-danger">Inactive</span>';
                        })


                ->addColumn('role', fn ($row) => ucfirst($row->role)
                )

                ->addColumn('action', function ($row) use ($authRole) {

                    if ($authRole === 'admin') {
                        return '
                        <button class="btn btn-sm btn-primary edit-btn" data-id="'.$row->id.'">Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$row->id.'">Delete</button>
                        <button class="btn btn-sm btn-warning change-password-btn" data-id="'.$row->id.'">
                         Change Password
                        </button>
                    ';
                    }

                    if ($authRole === 'manager') {
                        $today = now()->toDateString();
                        $attendance = \App\Models\Attendance::where('user_id', $row->id)
                            ->where('date', $today)
                            ->latest('id')
                            ->first();

                        if (! $attendance || ! $attendance->check_in_time) {
                            return '<button class="btn btn-sm btn-success attendance-btn"
                                data-user="'.$row->id.'" data-action="check-in">Check In</button>';
                        }

                        if ($attendance->check_in_time && ! $attendance->check_out_time) {
                            return '<button class="btn btn-sm btn-warning attendance-btn"
                                data-user="'.$row->id.'" data-action="check-out">Check Out</button>';
                        }

                        return '<button class="btn btn-sm btn-success attendance-btn"
                            data-user="'.$row->id.'" data-action="check-in">Check In</button>';
                    }

                    return '-';
                })
                ->rawColumns(['checkbox', 'action','status'])
                ->make(true);
        }

        return view('dashboard');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|digits:10|unique:users,phone',
            'password' => 'required|min:8',
            'status' => 'required|in:active,inactive',
        ]);

        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'user', // default
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Employee added successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|digits:10|unique:users,phone,'.$id,
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'status' => $request->status,
        ]);

        return response()->json(['success' => 'Employee updated successfully']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['success' => 'Employee deleted successfully']);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json($user);
    }

    ///Admin can change password of any user
    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:8',
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => 'Password changed successfully',
        ]);
    }
}
