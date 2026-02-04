@extends('admin.layouts.layout')

@section('title', 'Mahabal Attendance')
@section('admin')
@section('pagetitle', 'Attendance Management')
    @section('page-css')
        <link rel="stylesheet" href="{{ asset('admin/assets/css/index.css') }}">
    @endsection
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Attendance List</h4>
                    <div class="d-flex gap-2">
                        <!-- Export button -->
                        <button id="exportExcel" class="btn btn-success d-none d-sm-inline-block" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Export Excel">
                            <i class="bi bi-save"></i>
                        </button>

                        <!-- Reset button for desktop - moved to top right -->
                        <a href="{{ route('attendance.list') }}"
                            class="btn btn-secondary d-none d-sm-inline-block">Reset</a>
                    </div>
                </div>

                <!-- Filters container with responsive layout -->
                <div class="d-flex flex-wrap gap-3 align-items-end">
                    <!-- Start Date -->
                    <div class="d-flex flex-column flex-1 min-w-[150px]">
                        <input type="date" id="startDate" class="form-control">
                        <label for="startDate" class="form-text text-muted text-center">
                            Start Date
                        </label>
                    </div>
                    <!-- End Date -->
                    <div class="d-flex flex-column flex-1 min-w-[150px]">
                        <input type="date" id="endDate" class="form-control">
                        <label for="endDate" class="form-text text-muted text-center">
                            End Date
                        </label>
                    </div>
                    <!-- User Filter -->
                    <div class="d-flex flex-column flex-1 min-w-[150px]">
                        <select id="filterUser" class="form-select">
                            <option value="">Select User</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <label class="form-text text-muted text-center">
                            User
                        </label>
                    </div>
                    <!-- Export and Reset buttons for mobile - full width -->
                    <div class="d-flex flex-column flex-1 min-w-[150px]">
                        <button id="exportExcelMobile" class="btn btn-success w-100 d-sm-none mb-2">
                            <i class="bi bi-save"></i>
                        </button>
                        <a href="{{ route('attendance.list') }}" class="btn btn-secondary w-100 d-sm-none">Reset</a>
                    </div>
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
                // Date range validation
                $('#startDate').on('change', function () {
                    const startDate = $(this).val();
                    $('#endDate').attr('min', startDate);

                    // If end date is before start date, clear it
                    const endDate = $('#endDate').val();
                    if (endDate && endDate < startDate) {
                        $('#endDate').val('');
                    }
                });

                let table = $('#attendanceTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('attendance.list') }}",
                        data: function (d) {
                            d.start_date = $('#startDate').val();
                            d.end_date = $('#endDate').val();
                            d.user_id = $('#filterUser').val();
                        }
                    },
                    columns: [
                        { data: 'checkbox', orderable: false, searchable: false },
                        { data: 'date' },
                        { data: 'user_name' },
                        { data: 'check_in' },
                        { data: 'check_out' },
                        { data: 'action', orderable: false, searchable: false }
                    ]
                });

                $('#startDate, #endDate, #filterUser').on('change', function () {
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
                            $('#checkInSelfie').attr('src', data.check_in_selfie ? '/' + data.check_in_selfie : '');
                            $('#checkOutSelfie').attr('src', data.check_out_selfie ? '/' + data.check_out_selfie : '');
                            $('#attendanceDetailsModal').modal('show');
                        },
                        error: function () {
                            alert('Error fetching attendance details.');
                        }
                    });
                });

                // Export to Excel function
                function exportToExcel(data, filename = 'attendance-list') {
                    // Create worksheet data
                    const worksheetData = [];

                    // Add header row
                    worksheetData.push(['Date', 'User Name', 'Check In', 'Check Out']);

                    // Add data rows
                    data.forEach(row => {
                        worksheetData.push([
                            row.date,
                            row.user_name,
                            row.check_in,
                            row.check_out
                        ]);
                    });

                    // Create CSV content
                    const csvContent = worksheetData.map(row =>
                        row.map(cell => `"${cell}"`).join(',')
                    ).join('\n');

                    // Create and download file
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', `${filename}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }

                // Export button click event (desktop and mobile)
                $('#exportExcel, #exportExcelMobile').on('click', function () {
                    // Show loading indicator
                    $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Exporting...');
                    $(this).prop('disabled', true);

                    // Get filter parameters
                    const params = {
                        start_date: $('#startDate').val(),
                        end_date: $('#endDate').val(),
                        user_id: $('#filterUser').val()
                    };

                    // Fetch data for export
                    $.ajax({
                        url: '{{ route("attendance.export") }}',
                        type: 'GET',
                        data: params,
                        success: function (response) {
                            if (response.data.length > 0) {
                                // Generate filename with date range if applicable
                                let filename = 'attendance-list';
                                if (params.start_date && params.end_date) {
                                    filename = `attendance-${params.start_date}_to_${params.end_date}`;
                                } else if (params.start_date) {
                                    filename = `attendance-from-${params.start_date}`;
                                } else if (params.end_date) {
                                    filename = `attendance-to-${params.end_date}`;
                                }

                                // Export to Excel
                                exportToExcel(response.data, filename);
                            } else {
                                alert('No data to export');
                            }

                            // Reset both buttons
                            $('#exportExcel').html('<i class="bi bi-save"></i>');
                            $('#exportExcel').prop('disabled', false);
                            $('#exportExcelMobile').html('<i class="bi bi-file-earmark-excel"></i> <i class="bi bi-save"></i>');
                            $('#exportExcelMobile').prop('disabled', false);
                        },
                        error: function () {
                            alert('Error exporting data');
                            // Reset both buttons
                            $('#exportExcel').html('<i class="bi bi-save"></i>');
                            $('#exportExcel').prop('disabled', false);
                            $('#exportExcelMobile').html('<i class="bi bi-file-earmark-excel"></i> <i class="bi bi-save"></i>');
                            $('#exportExcelMobile').prop('disabled', false);
                        }
                    });
                });
            });
        </script>
    @endsection
@endsection