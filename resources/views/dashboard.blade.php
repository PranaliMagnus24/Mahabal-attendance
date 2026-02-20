@extends('admin.layouts.layout')

@section('title', 'Mahabal Attendance')
@section('admin')
@section('pagetitle', 'User Management')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@section('page-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/index.css') }}">
@endsection

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Employee List</h4>
            <div class="d-flex gap-2">
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#addWorkRecordModal">
                        <i class="fas fa-plus"></i> Add Employee
                    </button>
                @endif
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
            <div class="table-responsive">
                <table class="employeeList table table-bordered nowrap w-100" data-role="{{ auth()->user()->role }}">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30px"><input type="checkbox" id="selectAllEmployee"></th>
                            <th>Employee Name</th>
                            <th>Phone</th>
                            @if(auth()->user()->role === 'admin')
                                <th>Status</th>
                            @endif
                            @if(auth()->user()->role === 'admin')
                                <th>Role</th>
                            @endif
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this employee? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
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
                        <input type="text" name="phone" class="form-control" maxlength="10" inputmode="numeric"
                            value="{{ old('phone') }}">
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

                    <!-- Weekly Off -->
                    <div class="mb-3">
                        <label class="form-label">Weekly Off</label>
                        <select name="weekly_off" class="form-select">
                            <option value="sunday">Sunday</option>
                            <option value="monday">Monday</option>
                            <option value="tuesday">Tuesday</option>
                            <option value="wednesday">Wednesday</option>
                            <option value="thursday">Thursday</option>
                            <option value="friday">Friday</option>
                            <option value="saturday">Saturday</option>
                        </select>
                        @error('weekly_off')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Price</label>
                            <input type="text" name="price" class="form-control" value="{{ old('price') }}">
                            @error('price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="duration" class="form-label">Duration</label>
                            <select name="duration" id="duration" class="form-select">
                                <option value="hour">Hour</option>
                                <option value="day">Day</option>
                            </select>
                        </div>
                    </div>

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

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil"></i> Edit Employee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="editEmployeeForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body">
                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label">Employee Name</label>
                        <input type="text" name="name" id="editName" class="form-control">
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" id="editPhone" class="form-control" maxlength="10"
                            inputmode="numeric">
                    </div>

                    <!-- Weekly Off -->
                    <div class="mb-3">
                        <label class="form-label">Weekly Off</label>
                        <select name="weekly_off" id="editWeeklyOff" class="form-select">
                            <option value="sunday">Sunday</option>
                            <option value="monday">Monday</option>
                            <option value="tuesday">Tuesday</option>
                            <option value="wednesday">Wednesday</option>
                            <option value="thursday">Thursday</option>
                            <option value="friday">Friday</option>
                            <option value="saturday">Saturday</option>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Price</label>
                            <input type="text" name="price" id="editPrice" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="editDuration" class="form-label">Duration</label>
                            <select name="duration" id="editDuration" class="form-select">
                                <option value="hour">Hour</option>
                                <option value="day">Day</option>
                            </select>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="editStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--- Change Password Modal --->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="password_user_id">

                <div class="mb-3">
                    <label>New Password</label>
                    <input type="password" id="new_password" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Confirm Password</label>
                    <input type="password" id="confirm_password" class="form-control">
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="savePasswordBtn">Update Password</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="cameraModal" tabindex="-1">
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
                    <span id="captureBtnText">Capture</span>
                    <span id="captureBtnLoader" class="spinner-border spinner-border-sm ms-2 d-none" role="status"
                        aria-hidden="true"></span>
                </button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

@section('page-js')
<script src="{{ asset('admin/assets/js/user.js') }}"></script>
@endsection
<script>
    document.addEventListener('click', function (e) {

        let target = e.target;
        while (target && !target.classList.contains('attendance-btn')) {
            target = target.parentElement;
        }
        if (!target) return;

        const userId = target.dataset.user || null;
        const action = target.dataset.action;

        // For admin, skip camera modal and directly send request
        if (authRole === 'admin') {
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to ${action} for this user?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    if (userId) {
                        formData.append('user_id', userId);
                    }

                    const url = action === 'check-in'
                        ? "{{ route('attendance.checkIn') }}"
                        : "{{ route('attendance.checkOut') }}";

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: formData
                    })
                        .then(async response => {
                            const text = await response.text();
                            console.log('STATUS:', response.status);
                            console.log('RESPONSE:', text);

                            if (!response.ok) {
                                try {
                                    const errorData = JSON.parse(text);
                                    throw new Error(errorData.message || 'Attendance failed');
                                } catch (e) {
                                    throw new Error(text || 'Attendance failed');
                                }
                            }
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                throw new Error('Invalid response format');
                            }
                        })
                        .then(data => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        })
                        .catch(err => {
                            console.error('ERROR:', err.message);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Attendance failed. Check console.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        });
                }
            });
        } else {
            // For manager and user, show camera modal
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d');
            const modalEl = document.getElementById('cameraModal');
            const modal = new bootstrap.Modal(modalEl);

            let stream = null;

            modal.show();

            navigator.mediaDevices.getUserMedia({ video: true })
                .then(s => {
                    stream = s;
                    video.srcObject = stream;
                })
                .catch(() => {
                    modal.hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Camera Required',
                        text: 'Camera access is required for attendance',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });

            document.getElementById('captureBtn').onclick = function () {
                const captureBtn = this;
                const captureBtnText = document.getElementById('captureBtnText');
                const captureBtnLoader = document.getElementById('captureBtnLoader');

                if (!stream || captureBtn.disabled) return;

                // Show loader and disable button
                captureBtn.disabled = true;
                captureBtnText.textContent = 'Processing...';
                captureBtnLoader.classList.remove('d-none');

                navigator.geolocation.getCurrentPosition(async position => {

                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    let address = 'Location not found';
                    try {
                        const res = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
                        );
                        const data = await res.json();
                        address = data.display_name || address;
                    } catch { }

                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    ctx.fillStyle = "rgba(0,0,0,0.6)";
                    ctx.fillRect(0, canvas.height - 90, canvas.width, 90);

                    ctx.fillStyle = "#fff";
                    ctx.font = "14px Arial";

                    const now = new Date().toLocaleString('en-IN', {
                        timeZone: 'Asia/Kolkata'
                    });

                    ctx.fillText(`Date & Time: ${now}`, 10, canvas.height - 55);
                    ctx.fillText(`Location: ${address}`, 10, canvas.height - 25);

                    canvas.toBlob(blob => {

                        const formData = new FormData();
                        formData.append('selfie', blob, 'selfie.jpg');

                        if (userId) {
                            formData.append('user_id', userId);
                        }

                        // Log FormData contents for debugging
                        console.log('FormData contents:');
                        for (let [key, value] of formData.entries()) {
                            console.log(key, value);
                        }

                        const url = action === 'check-in'
                            ? "{{ route('attendance.checkIn') }}"
                            : "{{ route('attendance.checkOut') }}";

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document
                                    .querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: formData
                        })
                            .then(async response => {
                                const text = await response.text();
                                console.log('STATUS:', response.status);
                                console.log('RESPONSE:', text);

                                if (!response.ok) {
                                    try {
                                        const errorData = JSON.parse(text);
                                        throw new Error(errorData.message || 'Attendance failed');
                                    } catch (e) {
                                        throw new Error(text || 'Attendance failed');
                                    }
                                }
                                try {
                                    return JSON.parse(text);
                                } catch (e) {
                                    throw new Error('Invalid response format');
                                }
                            })
                            .then(data => {
                                modal.hide();
                                if (stream) stream.getTracks().forEach(t => t.stop());

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            })
                            .catch(err => {
                                console.error('ERROR:', err.message);
                                modal.hide();
                                if (stream) stream.getTracks().forEach(t => t.stop());

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Attendance failed. Check console.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            })
                            .finally(() => {
                                // Hide loader and re-enable button
                                captureBtn.disabled = false;
                                captureBtnText.textContent = 'Capture';
                                captureBtnLoader.classList.add('d-none');
                            });

                    }, 'image/jpeg');

                }, () => {
                    modal.hide();
                    if (stream) stream.getTracks().forEach(t => t.stop());

                    Swal.fire({
                        icon: 'error',
                        title: 'Location Required',
                        text: 'Geolocation is required for attendance',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Hide loader and re-enable button
                    captureBtn.disabled = false;
                    captureBtnText.textContent = 'Capture';
                    captureBtnLoader.classList.add('d-none');
                });
            };
        }
    });
</script>
<script>
    const authRole = "{{ auth()->user()->role }}";
</script>



@endsection
