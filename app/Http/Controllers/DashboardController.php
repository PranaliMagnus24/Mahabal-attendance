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
        if ($request->ajax()) {

            $query = User::where('role', 'user');
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
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" value="'.$row->id.'">';
                })
                ->addColumn('date', function ($row) {
                    return $row->created_at->format('d-m-Y');
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-primary edit-btn" data-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editEmployeeModal">Edit</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$row->id.'">Delete</button>
                    ';
                })
                ->rawColumns(['checkbox', 'action'])
                ->make(true);
        }

        // Normal page load
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
}
