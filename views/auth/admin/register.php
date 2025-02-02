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
    <title>Registration - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <form method="POST" action="/admin/register" class="login-form" id="registerForm">
            <h2>Register</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Add CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <div class="form-group">
                <input type="text" id="first_name" name="first_name" required 
                    value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : (isset($old['first_name']) ? htmlspecialchars($old['first_name']) : ''); ?>"
                    class="<?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>">
                <label for="first_name">First Name</label>
                <?php if (isset($errors['first_name'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['first_name']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <input type="text" id="last_name" name="last_name" required 
                    value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : (isset($old['last_name']) ? htmlspecialchars($old['last_name']) : ''); ?>"
                    class="<?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>">
                <label for="last_name">Last Name</label>
                <?php if (isset($errors['last_name'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['last_name']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <input type="email" id="email" name="email" required 
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : (isset($old['email']) ? htmlspecialchars($old['email']) : ''); ?>"
                    class="<?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>">
                <label for="email">Email</label>
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <input type="password" id="password" name="password" required
                    class="<?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>">
                <label for="password">Password</label>
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
                <small class="form-text text-muted">
                    Password must be at least 8 characters and contain uppercase, lowercase, and numbers
                </small>
            </div>
            
            <div class="form-group">
                <input type="password" id="password_confirmation" name="password_confirmation" required>
                <label for="password_confirmation">Confirm Password</label>
            </div>
            
            <button type="submit">Register</button>
            
            <div class="links">
                <a href="/admin/login">Already have an account? Login</a>
            </div>
        </form>
    </div>

    <script src="/public/assets/js/register.js"></script>
</body>
</html>