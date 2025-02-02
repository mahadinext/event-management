<nav id="sidebar">
    <div class="sidebar-header">
        <!-- <img src="path/to/logo.png" alt="Logo" class="logo"> -->
        <h3>Admin Panel</h3>
    </div>

    <ul class="list-unstyled components">
        <li class="<?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>">
            <a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a>
        </li>
        <li class="<?php echo (strpos($currentPage, 'events') !== false) ? 'active' : ''; ?>">
            <a href="#eventsSubmenu" data-bs-toggle="collapse">
                <i class="fas fa-calendar-alt"></i> Events
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled <?php echo (strpos($currentPage, 'events') !== false) ? 'show' : ''; ?>" id="eventsSubmenu">
                <li><a href="/admin/events">All Events</a></li>
                <li><a href="/admin/events/create">Add New Event</a></li>
            </ul>
        </li>
        <li class="<?php echo (strpos($currentPage, 'event-attendees') !== false) ? 'active' : ''; ?>">
            <a href="/admin/event-attendees">
                <i class="fas fa-users"></i> Event Attendees
            </a>
        </li>
        <!-- <li class="<?php echo ($currentPage === 'users') ? 'active' : ''; ?>">
            <a href="/pages/users.php"><i class="fas fa-users"></i> Users</a>
        </li> -->
        <li>
            <a href="/admin/logout">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </li>
    </ul>
</nav>