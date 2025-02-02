<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <!-- Sidebar Toggle Button -->
        <button type="button" id="sidebarCollapse" class="btn">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Right-aligned nav items -->
        <div class="ms-auto d-flex align-items-center">
            <!-- Notifications -->
            <div class="nav-item dropdown me-3 d-none">
                <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                    <h6 class="dropdown-header">Notifications</h6>
                    <?php
                    // You can fetch notifications from database here
                    $notifications = [
                        ['icon' => 'user', 'bg' => 'primary', 'message' => 'New user registered', 'time' => '5 minutes ago'],
                        ['icon' => 'calendar', 'bg' => 'success', 'message' => 'New event created', 'time' => '1 hour ago']
                    ];
                    
                    foreach ($notifications as $notification): ?>
                        <a class="dropdown-item" href="#">
                            <div class="notification-item">
                                <div class="icon bg-<?php echo $notification['bg']; ?>">
                                    <i class="fas fa-<?php echo $notification['icon']; ?>"></i>
                                </div>
                                <div class="content">
                                    <p><?php echo $notification['message']; ?></p>
                                    <small><?php echo $notification['time']; ?></small>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center" href="#">View all notifications</a>
                </div>
            </div>

            <!-- Profile Dropdown -->
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                    <?php if (isset($_SESSION['user_avatar'])): ?>
                        <img src="<?php echo $_SESSION['user_avatar']; ?>" alt="Profile" class="rounded-circle me-2" width="32" height="32">
                    <?php endif; ?>
                    <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin User'; ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <a class="dropdown-item" href="/admin/dashboard">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a class="dropdown-item" href="/admin/events">
                        <i class="fas fa-calendar-alt"></i> Events
                    </a>
                    <a class="dropdown-item" href="/admin/event-attendees">
                        <i class="fas fa-users"></i> Event Attendees
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/admin/logout">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>