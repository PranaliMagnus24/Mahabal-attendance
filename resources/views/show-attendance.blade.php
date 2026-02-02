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
                <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Details</h4>
                {{-- <div class="d-flex gap-2">
                    <a href="{{ route('attendance.list') }}" class="btn btn-secondary">Back</a>
                </div> --}}
            </div>
            <div class="card-body mt-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Employee Details</h4>
                            </div>
                            <div class="card-body mt-3">
                                <p><strong>Name:</strong> {{ $attendance->user->name }}</p>
                                @if($attendance && $attendance->user)
                                    <p><strong>Email:</strong> {{ $attendance->user->email }}</p>
                                @endif
                                <p><strong>Phone:</strong> {{ $attendance->user->phone }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Attendance Details</h4>
                            </div>
                            <div class="card-body mt-3">
                                <p><strong>Date:</strong>
                                    {{ \Carbon\Carbon::parse($attendance->date)->timezone('Asia/Kolkata')->format('j M Y') }}
                                </p>
                                <p><strong>Check In Time:</strong>
                                    {{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->timezone('Asia/Kolkata')->format('j M Y h:i A') : '-'
                                        }}
                                </p>
                                <p><strong>Check Out Time:</strong>
                                    {{ $attendance->check_out_time
        ? \Carbon\Carbon::parse($attendance->check_out_time)->timezone('Asia/Kolkata')->format('j M Y h:i A')
        : '-'
                                        }}
                                </p>
                                <p><strong>Attended By:</strong>
                                    {{ $attendance->attendedBy ? $attendance->attendedBy->name : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Check In Selfie</h4>
                            </div>
                            <div class="card-body mt-3">
                                <p>
                                    @if($attendance->check_in_selfie)
                                        <img src="{{ asset($attendance->check_in_selfie) }}" class="img-thumbnail mb-2"
                                            width="200">
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Check Out Selfie</h4>
                            </div>
                            <div class="card-body mt-3">
                                <p>
                                    @if($attendance->check_out_selfie)
                                        <img src="{{ asset($attendance->check_out_selfie) }}" class="img-thumbnail" width="200">
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection