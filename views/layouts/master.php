<?php
    // session_start();

    // Define the root path
    define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));
    // require_once(ROOT_PATH . '/app/Constants/UserConstants.php');
    $public_paths = ['/admin/login', '/admin/register', '/'];
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Check if the current page requires authentication
    if ((!isset($_SESSION['user_id']) || 
        (!isset($_SESSION['user_type']) || (isset($_SESSION['user_type']) && $_SESSION['user_type'] != App\Constants\UserConstants::ROLE_TYPE_ADMIN))) && 
        !in_array($current_path, $public_paths)) {
        $_SESSION['intended_url'] = $current_path;
        header('Location: /');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once(ROOT_PATH . '/views/includes/head-css.php'); ?>
</head>
<body>
    <div class="wrapper">
        <?php require_once(ROOT_PATH . '/views/includes/sidebar.php'); ?>
        
        <div id="content">
            <?php require_once(ROOT_PATH . '/views/includes/navbar.php'); ?>

            <!-- Main Content Area -->
            <div class="container-fluid p-4">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                        <?php 
                            echo $_SESSION['message'];
                            unset($_SESSION['message']);
                            unset($_SESSION['message_type']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['admin_event_crud_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        <?php 
                            echo $_SESSION['admin_event_crud_success']; unset($_SESSION['admin_event_crud_success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['admin_event_crud_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                        <?php echo $_SESSION['admin_event_crud_error']; unset($_SESSION['admin_event_crud_error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php echo $content; ?>
            </div>
        </div>
    </div>

    <?php require_once(ROOT_PATH . '/views/includes/vendor-scripts.php'); ?>
</body>
</html>