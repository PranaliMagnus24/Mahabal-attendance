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
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
            <div class="card-body mt-3">

                <!-- Attendance Details Modal -->
                <div class="modal fade" id="attendanceDetailsModal" tabindex="-1"
                    aria-labelledby="attendanceDetailsModalLabel" aria-hidden="true">
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
                                            <img id="checkInSelfie" src="" alt="" class="img-fluid"
                                                style="max-width: 200px;">
                                        </div>
                                        <div>
                                            <strong>Check Out Selfie:</strong><br>
                                            <img id="checkOutSelfie" src="" alt="" class="img-fluid"
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

                @php
                    $today = now()->toDateString();
                    $todayAttendance = $attendances->where('date', $today)->sortByDesc('id')->first();
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
                        {{-- If check out is completed, show check in button again for next day --}}
                        <button id="checkInBtn" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Check In
                        </button>
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
                                <th>Action</th>
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
                                                        <button class="btn btn-primary btn-sm show-details"
                                                            data-id="{{ $attendance->id }}">View</button>
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
            const ctx = canvas.getContext('2d');

            const modalElement = document.getElementById('cameraModal');
            const cameraModal = new bootstrap.Modal(modalElement);

            let action = '';
            let stream = null;

            /* --------------------------
               OPEN CAMERA
            ---------------------------*/
            function openCamera() {
                cameraModal.show();

                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(s => {
                        stream = s;
                        video.srcObject = stream;
                    })
                    .catch(() => alert('Camera access denied'));
            }

            if (checkInBtn) {
                checkInBtn.addEventListener('click', () => {
                    action = 'check-in';
                    openCamera();
                });
            }

            if (checkOutBtn) {
                checkOutBtn.addEventListener('click', () => {
                    action = 'check-out';
                    openCamera();
                });
            }

            /* --------------------------
               CAPTURE SELFIE + ADDRESS
            ---------------------------*/
            captureBtn.addEventListener('click', async function () {
                // Show loader
                const originalText = captureBtn.innerHTML;
                captureBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Capturing...';
                captureBtn.disabled = true;

                if (!navigator.geolocation) {
                    alert('Geolocation not supported');
                    return;
                }

                navigator.geolocation.getCurrentPosition(async position => {

                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    // 🔹 Reverse Geocoding (OpenStreetMap)
                    let address = 'Location not found';
                    try {
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
                        );
                        const data = await response.json();
                        address = data.display_name || address;
                    } catch (e) { }

                    // Draw selfie
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    // Background for text
                    ctx.fillStyle = "rgba(0,0,0,0.6)";
                    ctx.fillRect(0, canvas.height - 90, canvas.width, 90);

                    ctx.fillStyle = "#ffffff";
                    ctx.font = "14px Arial";

                    const now = new Date();
                    const dateTime = now.toLocaleString('en-IN', {
                        timeZone: 'Asia/Kolkata'
                    });

                    ctx.fillText(`Date & Time: ${dateTime}`, 10, canvas.height - 55);
                    ctx.fillText(`Location:`, 10, canvas.height - 35);
                    ctx.fillText(address, 10, canvas.height - 15);

                    // Convert to image
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
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred');
                            })
                            .finally(() => {
                                // Reset button
                                captureBtn.innerHTML = originalText;
                                captureBtn.disabled = false;
                            });

                    }, 'image/jpeg');

                }, () => {
                    alert('Location access denied');
                });
            });

            /* --------------------------
               STOP CAMERA
            ---------------------------*/
            // Handle show details button click
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('show-details')) {
                    var attendanceId = e.target.getAttribute('data-id');
                    fetch(`/attendance/show/${attendanceId}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('userName').textContent = data.user.name;
                            document.getElementById('userEmail').textContent = data.user.email;
                            document.getElementById('userPhone').textContent = data.user.phone || 'N/A';
                            document.getElementById('attendanceDate').textContent = data.date;
                            document.getElementById('checkInTime').textContent = data.check_in_time ? new Date(data.check_in_time).toLocaleString() : 'N/A';
                            document.getElementById('checkOutTime').textContent = data.check_out_time ? new Date(data.check_out_time).toLocaleString() : 'N/A';
                            document.getElementById('checkInSelfie').setAttribute('src', data.check_in_selfie ? '/' + data.check_in_selfie : '');
                            document.getElementById('checkOutSelfie').setAttribute('src', data.check_out_selfie ? '/' + data.check_out_selfie : '');
                            new bootstrap.Modal(document.getElementById('attendanceDetailsModal')).show();
                        })
                        .catch(error => {
                            console.error('Error fetching attendance details:', error);
                            alert('Error fetching attendance details.');
                        });
                }
            });

            modalElement.addEventListener('hidden.bs.modal', stopCamera);

            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
            }

        });
    </script>



@endsection
