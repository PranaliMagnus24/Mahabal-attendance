<!-- ======= Navbar ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->


    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">
            <!-- Manager's Own Attendance Buttons -->
           @if(auth()->check() && auth()->user()->role === 'manager')
    @php
        $today = now()->toDateString();

        // 🔥 check if any OPEN attendance exists today
        $openAttendance = \App\Models\Attendance::where('user_id', auth()->id())
            ->where('date', $today)
            ->whereNotNull('check_in_time')
            ->whereNull('check_out_time')
            ->latest('check_in_time')
            ->first();
    @endphp

    {{-- 🔹 If open session exists → Show Check Out --}}
    @if($openAttendance)
        <li class="nav-item">
            <button class="btn btn-warning btn-sm attendance-btn"
                data-user="{{ auth()->id() }}"
                data-action="check-out">
                <i class="bi bi-box-arrow-right"></i> Check Out
            </button>
        </li>

    {{-- 🔹 Else → Show Check In --}}
    @else
        <li class="nav-item">
            <button class="btn btn-success btn-sm attendance-btn"
                data-user="{{ auth()->id() }}"
                data-action="check-in">
                <i class="bi bi-box-arrow-in-right"></i> Check In
            </button>
        </li>
    @endif
@endif

            <!-----For open work record form modal-->
            <li class="nav-item dropdown">

                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    <span class="badge bg-primary badge-number" id="notification-count">0</span>
                </a><!-- End Notification Icon -->

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications"
                    id="notification-dropdown">
                    <li class="dropdown-header" id="notification-header">
                        New Notification..
                        <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View All</span></a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <div id="notification-list" style="max-height: 300px; overflow-y: auto;">
                        <li class="notification-item">
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-bell-slash"></i>
                                <p>No Notification</p>
                            </div>
                        </li>
                    </div>

                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li class="dropdown-footer">
                        <a href="#">All Notification</a>
                    </li>

                </ul><!-- End Notification Dropdown Items -->

            </li><!-- End Notification Nav -->
            @php
                $getUser = App\Models\User::first();
            @endphp
            <li class="nav-item dropdown pe-3">

                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    {{-- <img
                        src="{{ $getUser->profile_img ? url('upload/profile_images/' . $getUser->profile_img) : url('upload/profile-img.jpg') }}"
                        alt="Profile" class="rounded-circle"> --}}
                    <span class="d-none d-md-block dropdown-toggle ps-2">{{ Auth::user()->name }}</span>
                </a><!-- End Profile Iamge Icon -->

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>{{ Auth::user()->name }}</h6>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a class="dropdown-item d-flex align-items-center"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                href="{{ route('logout') }}">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Logout</span>
                            </a>
                        </form>
                    </li>

                </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->

        </ul>
        <div id="profileOffcanvasContainer"></div>

    </nav><!-- End Icons Navigation -->

    {{-- Camera Modal for Attendance --}}
    <div class="modal fade" id="navbarCameraModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Take Selfie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <video id="navbarVideo" width="100%" autoplay></video>
                    <canvas id="navbarCanvas" width="320" height="240" class="d-none"></canvas>
                </div>

                <div class="modal-footer">
                    <button id="navbarCaptureBtn" class="btn btn-success">
                        Capture
                    </button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </div>

</header><!-- End Header -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const attendanceBtns = document.querySelectorAll('.attendance-btn');
        const captureBtn = document.getElementById('navbarCaptureBtn');
        const video = document.getElementById('navbarVideo');
        const canvas = document.getElementById('navbarCanvas');
        const ctx = canvas.getContext('2d');
        const modalElement = document.getElementById('navbarCameraModal');
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

        /* --------------------------
           HANDLE ATTENDANCE BUTTON CLICKS
        ---------------------------*/
        attendanceBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                action = this.getAttribute('data-action');
                openCamera();
            });
        });

        /* --------------------------
           CAPTURE SELFIE + ADDRESS
        ---------------------------*/
        captureBtn.addEventListener('click', async function () {
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

                    // If user_id is present in data attribute, include it
                    const userBtn = document.querySelector('.attendance-btn');
                    const userId = userBtn.getAttribute('data-user');
                    if (userId) {
                        formData.append('user_id', userId);
                    }

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
                        });

                }, 'image/jpeg');

            }, () => {
                alert('Location access denied');
            });
        });

        /* --------------------------
           STOP CAMERA
        ---------------------------*/
        modalElement.addEventListener('hidden.bs.modal', stopCamera);

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }

    });
</script>
