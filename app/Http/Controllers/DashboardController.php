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
        // ✅ AJAX request → DataTable response
        if ($request->ajax()) {

            $query = User::where('role', 'user');

            // 🔍 Custom Search
            if ($request->search_value) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search_value . '%')
                      ->orWhere('phone', 'like', '%' . $request->search_value . '%');
                });
            }

            // 🎯 Status Filter
            if ($request->status) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" value="'.$row->id.'">';
                })
                ->addColumn('date', function ($row) {
                    return $row->created_at->format('d-m-Y');
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-primary">Edit</button>
                        <button class="btn btn-sm btn-danger">Delete</button>
                    ';
                })
                ->rawColumns(['checkbox','action'])
                ->make(true);
        }

        // ✅ Normal page load
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
}
