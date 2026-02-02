@extends('admin.layouts.layout')

@section('title', 'Mahabal Attendance')
@section('admin')
@section('pagetitle', 'Attendance Management')
    @section('page-css')
        <link rel="stylesheet" href="{{ asset('admin/assets/css/index.css') }}">
    @endsection
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Attendance List</h4>
                <div class="d-flex gap-2 align-items-center">

    <!-- Date Filter -->
    <input type="date" id="filterDate" class="form-control">

    <!-- User Filter -->
    <select id="filterUser" class="form-select">
        <option value="">Select User</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
        @endforeach
    </select>
    {{-- <button id="exportExcel" class="btn btn-success">
    <i class="bi bi-file-earmark-excel"></i> Export Excel
</button> --}}


    <!-- Reset -->
    <a href="{{ route('attendance.list') }}" class="btn btn-secondary">Reset</a>
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
                <div class="table-responsive">
                <table class="table table-bordered nowrap w-100" id="attendanceTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30px"><input type="checkbox" id="selectAllEmployee"></th>
                            <th>ID</th>
                            <th>Date</th>
                            <th>User Name</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Attendance Details Modal -->
    <div class="modal fade" id="attendanceDetailsModal" tabindex="-1" aria-labelledby="attendanceDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceDetailsModalLabel">Attendance Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>User Information</h6>
                            <p><strong>Name:</strong> <span id="userName"></span></p>
                            <p><strong>Email:</strong> <span id="userEmail"></span></p>
                            <p><strong>Phone:</strong> <span id="userPhone"></span></p>
                            <p><strong>Date:</strong> <span id="attendanceDate"></span></p>
                            <p><strong>Check In Time:</strong> <span id="checkInTime"></span></p>
                            <p><strong>Check Out Time:</strong> <span id="checkOutTime"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Selfies</h6>
                            <div class="mb-3">
                                <strong>Check In Selfie:</strong><br>
                                <img id="checkInSelfie" src="" alt="Check In Selfie" class="img-fluid"
                                    style="max-width: 200px;">
                            </div>
                            <div>
                                <strong>Check Out Selfie:</strong><br>
                                <img id="checkOutSelfie" src="" alt="Check Out Selfie" class="img-fluid"
                                    style="max-width: 200px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @section('page-js')
        <script>
            $(function () {
                let table = $('#attendanceTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('attendance.list') }}",
                        data: function (d) {
                            d.date = $('#filterDate').val();
                        }
                    },
                    columns: [
                        { data: 'checkbox', orderable: false, searchable: false },
                        { data: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'date' },
                        { data: 'user_name' },
                        { data: 'check_in' },
                        { data: 'check_out' },
                        { data: 'action', orderable: false, searchable: false }
                    ]
                });

                $('#filterDate, #filterUser').on('change', function () {
        table.draw();
    });

                // Handle show details button click
                $(document).on('click', '.show-details', function () {
                    var attendanceId = $(this).data('id');
                    $.ajax({
                        url: '{{ route("attendance.show", ":id") }}'.replace(':id', attendanceId),
                        type: 'GET',
                        success: function (data) {
                            $('#userName').text(data.user.name);
                            $('#userEmail').text(data.user.email);
                            $('#userPhone').text(data.user.phone || 'N/A');
                            $('#attendanceDate').text(data.date);
                            $('#checkInTime').text(data.check_in_time ? new Date(data.check_in_time).toLocaleString() : 'N/A');
                            $('#checkOutTime').text(data.check_out_time ? new Date(data.check_out_time).toLocaleString() : 'N/A');
                            $('#checkInSelfie').attr('src', data.check_in_selfie ? '/storage/' + data.check_in_selfie : '');
                            $('#checkOutSelfie').attr('src', data.check_out_selfie ? '/storage/' + data.check_out_selfie : '');
                            $('#attendanceDetailsModal').modal('show');
                        },
                        error: function () {
                            alert('Error fetching attendance details.');
                        }
                    });
                });
            });
        </script>
    @endsection
@endsection
