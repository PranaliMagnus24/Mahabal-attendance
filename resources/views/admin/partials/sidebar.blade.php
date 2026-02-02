<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        @if(auth()->check() && auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="bi bi-person"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="{{ route('attendance.list') }}">
                    <i class="bi bi-people"></i>
                    <span>Attendance Management</span>
                </a>
            </li>
        @endif
        @if(auth()->check() && auth()->user()->role === 'user')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('attendance.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Attendance</span>
                </a>
            </li>
        @endif
        <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                @csrf
                <a class="nav-link" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();"
                    href="{{ route('logout') }}" style="cursor: pointer;">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </form>
        </li>
    </ul>
</aside><!-- End Sidebar-->
