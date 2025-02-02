<?php
    define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));
    require_once(ROOT_PATH . '/app/constants/UserConstants.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Event Management'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .navbar .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .navbar .dropdown-item {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        .navbar .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
        }
        
        .navbar .dropdown-item i {
            width: 20px;
            text-align: center;
        }
        
        .navbar .nav-link {
            padding: 0.5rem 1rem;
        }
        
        .navbar .nav-link.active {
            font-weight: 500;
        }
        
        @media (max-width: 991.98px) {
            .navbar .dropdown-menu {
                border: none;
                box-shadow: none;
                padding-left: 1rem;
            }
            
            .navbar .dropdown-item {
                padding: 0.5rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">Event Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage === 'event-dashboard') ? 'active' : ''; ?>" 
                        href="/">Events</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == App\Constants\UserConstants::ROLE_TYPE_ADMIN): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>" 
                                href="/admin/dashboard">
                                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/admin/logout">
                                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/attendee/logout">
                                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" 
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="loginDropdown">
                                <li>
                                    <a class="dropdown-item" href="/admin/login">
                                        <i class="fas fa-user-shield me-2"></i> Login as Admin
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/attendee/login">
                                        <i class="fas fa-user me-2"></i> Login as Attendee
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="registerDropdown" 
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-plus me-1"></i> Sign Up
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="registerDropdown">
                                <li>
                                    <a class="dropdown-item" href="/admin/register">
                                        <i class="fas fa-user-shield me-2"></i> Register as Admin
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/attendee/register">
                                        <i class="fas fa-user me-2"></i> Register as Attendee
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <?php echo $content; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000",
            "extendedTimeOut": "1000"
        };
    </script>
</body>
</html>