@extends('admin.layouts.layout')

@section('title', 'Mahabal Attendance')
@section('admin')
@section('pagetitle', 'Dashboard')
    @section('page-css')
        <link rel="stylesheet" href="{{ asset('admin/assets/css/index.css') }}">
    @endsection
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Employee List</h4>
                <div class="d-flex gap-2">
                    <!-- Company Filter Dropdown -->


                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#addWorkRecordModal">
                        <i class="fas fa-plus"></i> Add Employee
                    </button>
                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>

            <div class="card-body mt-3">
                <!-- Custom search box -->
                <div id="customSearchContainer" style="display:none;" class="search-bar-wrapper">
                    <div class="search-bar-work-record">
                        <i class="bi bi-search search-icon"></i>
                        <input id="customSearchInput" type="text" class="search-input" placeholder="Search...">
                        <i id="customSearchClear" class="bi bi-x clear-icon"></i>
                    </div>
                </div>

                <table class="table table-bordered table-striped nowrap mb-0 employeeList" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30px"><input type="checkbox" id="selectAllEmployee"></th>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Employee Name</th>
                            <th>Phone</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div>
<!-- Add Employee Modal -->
<div class="modal fade" id="addWorkRecordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i> Add Employee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('employees.store') }}">
                @csrf

                <div class="modal-body">

                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label">Employee Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        @error('name')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text"
                               name="phone"
                               class="form-control"
                               maxlength="10"
                               inputmode="numeric" value="{{ old('phone') }}">
                        @error('phone')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control">
                        @error('password')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Role -->
                    <input type="hidden" name="role" value="user">

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        Save Employee
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@section('page-js')
    <script src="{{ asset('admin/assets/js/user.js') }}"></script>
@endsection
@endsection
