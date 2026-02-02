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
                    $managerAttendance = \App\Models\Attendance::where('user_id', auth()->id())
                        ->where('date', $today)
                        ->first();
                @endphp

                @if(!$managerAttendance || !$managerAttendance->check_in_time)
                    <li class="nav-item">
                        <button class="btn btn-success btn-sm attendance-btn" data-user="{{ auth()->id() }}"
                            data-action="check-in">
                            <i class="bi bi-box-arrow-in-right"></i> Check In
                        </button>
                    </li>
                @elseif($managerAttendance->check_in_time && !$managerAttendance->check_out_time)
                    <li class="nav-item">
                        <button class="btn btn-warning btn-sm attendance-btn" data-user="{{ auth()->id() }}"
                            data-action="check-out">
                            <i class="bi bi-box-arrow-right"></i> Check Out
                        </button>
                    </li>
                @else
                    <li class="nav-item">
                        <span class="text-success fw-bold">Attendance Completed</span>
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



</header><!-- End Header -->