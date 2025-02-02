<?php
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
        header('Location: /');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendee Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="/public/assets/css/login.css" rel="stylesheet">
</head>
<body>
    <div class="stars">
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
    </div>
    
    <div class="login-container">
        <form method="POST" action="/attendee/login" class="login-form">
            <h2>Login</h2>
            
            <!-- General Error Alert -->
            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <input type="email" id="email" name="email" required 
                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : (isset($old['email']) ? htmlspecialchars($old['email']) : ''); ?>"
                class="<?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>">
                <label for="email">Email</label>
                <!-- <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                <?php endif; ?> -->
            </div>
            
            <!-- Add CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <div class="form-group">
                <input type="password" id="password" name="password" required
                class="<?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>">
                <label for="password">Password</label>
                <!-- <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                <?php endif; ?> -->
            </div>
            
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            
            <button type="submit">Login</button>
            
            <div class="links">
                <!-- <a href="/auth/forgot-password.php">Forgot Password?</a> -->
                <a href="/attendee/register">Sign Up</a>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };

        // // Show toastr messages if they exist
        // <?php if (!empty($error)): ?>
        //     toastr.error('<?php echo addslashes($error); ?>', 'Error');
        // <?php endif; ?>

        // // Show toastr messages for field-specific errors
        // <?php if (!empty($errors)): ?>
        //     <?php foreach ($errors as $field => $message): ?>
        //         <?php if ($field !== 'system' && !empty($message)): ?>
        //             toastr.error('<?php echo addslashes($message); ?>', '<?php echo ucfirst($field); ?> Error');
        //         <?php endif; ?>
        //     <?php endforeach; ?>
        // <?php endif; ?>
    </script>
    <script src="/public/assets/js/login.js"></script>
</body>
</html>