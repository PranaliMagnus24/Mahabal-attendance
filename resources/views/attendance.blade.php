@extends('admin.layouts.layout')

@section('title', 'Mahabal Attendance')
@section('admin')
@section('pagetitle', 'Attendance')
    @section('page-css')
        <link rel="stylesheet" href="{{ asset('admin/assets/css/index.css') }}">
    @endsection
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Employee List</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
            <div class="card-body mt-3">

                @php
                    $today = now()->toDateString();
                    $todayAttendance = $attendances->where('date', $today)->first();
                @endphp

                {{-- Check In / Check Out Buttons --}}
                <div class="mb-4">
                    @if(!$todayAttendance || !$todayAttendance->check_in_time)
                        <button id="checkInBtn" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Check In
                        </button>
                    @elseif(!$todayAttendance->check_out_time)
                        <button id="checkOutBtn" class="btn btn-danger">
                            <i class="bi bi-box-arrow-right"></i> Check Out
                        </button>
                    @else
                        <span class="badge bg-success">You have completed today’s attendance</span>
                    @endif
                </div>

                {{-- Attendance History --}}
                <h5 class="mb-3">Attendance History</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Check In Selfie</th>
                                <th>Check Out Selfie</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $attendance)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d-m-Y') }}</td>

                                                    <td>
                                                        {{ $attendance->check_in_time
                                ? \Carbon\Carbon::parse($attendance->check_in_time)
                                    ->timezone('Asia/Kolkata')
                                    ->format('h:i A')
                                : '-' }}
                                                    </td>

                                                    <td>
                                                        {{ $attendance->check_out_time
                                ? \Carbon\Carbon::parse($attendance->check_out_time)
                                    ->timezone('Asia/Kolkata')
                                    ->format('h:i A')
                                : '-' }}
                                                    </td>

                                                    <td>
                                                        @if($attendance->check_in_selfie)
                                                            <img src="{{ asset('storage/' . $attendance->check_in_selfie) }}" class="img-thumbnail"
                                                                width="70">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>
                                                        @if($attendance->check_out_selfie)
                                                            <img src="{{ asset('storage/' . $attendance->check_out_selfie) }}" class="img-thumbnail"
                                                                width="70">
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No attendance records found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Take Selfie</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body text-center">
                            <video id="video" width="100%" autoplay></video>
                            <canvas id="canvas" width="320" height="240" class="d-none"></canvas>
                        </div>

                        <div class="modal-footer">
                            <button id="captureBtn" class="btn btn-success">
                                Capture
                            </button>
                            <button class="btn btn-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>

                    </div>
                </div>
            </div>


        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const checkInBtn = document.getElementById('checkInBtn');
            const checkOutBtn = document.getElementById('checkOutBtn');
            const captureBtn = document.getElementById('captureBtn');

            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');

            const modalElement = document.getElementById('cameraModal');
            const cameraModal = new bootstrap.Modal(modalElement);

            let action = '';

            // ===============================
            // OPEN MODAL
            // ===============================
            if (checkInBtn) {
                checkInBtn.addEventListener('click', function () {
                    action = 'check-in';
                    openCamera();
                });
            }

            if (checkOutBtn) {
                checkOutBtn.addEventListener('click', function () {
                    action = 'check-out';
                    openCamera();
                });
            }

            function openCamera() {
                cameraModal.show();

                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => {
                        video.srcObject = stream;
                        window.stream = stream;
                    })
                    .catch(err => {
                        alert('Camera access denied');
                        console.error(err);
                    });
            }

            // ===============================
            // CAPTURE IMAGE
            // ===============================
            captureBtn.addEventListener('click', function () {

                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                canvas.toBlob(blob => {

                    const formData = new FormData();
                    formData.append('selfie', blob, 'selfie.jpg');
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch('/' + action, {
                        method: 'POST',
                        body: formData
                    })
                        .then(res => res.json())
                        .then(data => {
                            cameraModal.hide();
                            stopCamera();

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        })
                        .catch(err => {
                            stopCamera();
                            console.error(err); 
                        });

                }, 'image/jpeg');
            });

            // ===============================
            // STOP CAMERA ON MODAL CLOSE
            // ===============================
            modalElement.addEventListener('hidden.bs.modal', function () {
                stopCamera();
            });

            function stopCamera() {
                if (window.stream) {
                    window.stream.getTracks().forEach(track => track.stop());
                    window.stream = null;
                }
            }

        });
    </script>

@endsection
