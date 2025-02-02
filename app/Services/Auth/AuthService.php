<?php

namespace App\Services\Auth;

use App\Core\Service;
use App\Constants\UserConstants;
use Exception;
use App\Core\Logger;

class AuthService extends Service {
    private $conn;
    
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 900; // 15 minutes in seconds
    
    public function __construct($conn) 
    {
        $this->conn = $conn;
    }
    
    /*
    * Attempt to login a user
    * @param string $email
    * @param string $password
    * @param string $userType
    * @return array
    */
    public function attempt($email, $password, $userType) 
    {
        try {
            // Check for too many failed attempts
            if ($this->isIpBlocked() || $this->isAccountBlocked($email)) {
                throw new Exception('Too many login attempts. Please try again later.');
            }

            // Validate input
            if (empty($email) || empty($password)) {
                throw new Exception('Email and password are required');
            }
            
            // Get user by email
            $query = "SELECT id, name, email, password, status, user_type 
                    FROM users 
                    WHERE email = ?
                    AND user_type = ?
                    AND deleted_at IS NULL";
                     
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception('Database error occurred');
            }
            
            mysqli_stmt_bind_param($stmt, "si", $email, $userType);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Database error occurred');
            }
            
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            
            if (!$user) {
                $this->recordFailedAttempt($email);
                throw new Exception('Invalid email or password');
            }
            
            // Check if user is active
            if ($user['status'] !== UserConstants::STATUS_ACTIVE) {
                throw new Exception('Your account is not active');
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->recordFailedAttempt($email);
                // Log failed attempt
                Logger::error('Failed login attempt', [
                    'email' => $email,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);
                throw new Exception('Invalid email or password');
            }

            // Clear failed attempts on successful login
            $this->clearFailedAttempts($email);
            
            // Update password hash if using old algorithm
            $this->updatePasswordHashIfNeeded($user['id'], $password);
            
            return $user;
        } catch (Exception $e) {
            $this->logError('Login attempt failed', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            throw $e;
        }
    }
    
    /*
    * Create a remember token for a user
    * @param int $userId
    * @return string
    */
    public function createRememberToken($userId) 
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        
        $query = "INSERT INTO personal_access_tokens 
                 (tokenable_type, tokenable_id, name, token, abilities) 
                 VALUES ('User', ?, 'remember_token', ?, '[\"remember\"]')";
                 
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "is", $userId, $hashedToken);
        mysqli_stmt_execute($stmt);
        
        return $token;
    }
    
    /*
    * Remove a remember token for a user
    * @param string $token
    * @return bool
    */
    public function removeRememberToken($token) 
    {
        try {
            $query = "DELETE FROM remember_tokens WHERE token = ?";
            $stmt = mysqli_prepare($this->conn, $query);
            
            if (!$stmt) {
                throw new Exception("Database prepare failed: " . mysqli_error($this->conn));
            }
            
            mysqli_stmt_bind_param($stmt, "s", $token);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Database execute failed: " . mysqli_stmt_error($stmt));
            }
            
            return true;
        } catch (Exception $e) {
            Logger::error("Error removing remember token: " . $e->getMessage());
            throw $e;
        }
    }
    
    /*
    * Log a user login
    * @param int $userId
    * @return void
    */
    public function logLogin($userId) 
    {
        $query = "INSERT INTO user_login_history 
                 (user_id, ip_address, user_agent) 
                 VALUES (?, ?, ?)";
                 
        $stmt = mysqli_prepare($this->conn, $query);
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        mysqli_stmt_bind_param($stmt, "iss", $userId, $ip, $userAgent);
        mysqli_stmt_execute($stmt);
    }
    
    /*
    * Log a user logout
    * @param int $userId
    * @return void
    */
    public function logLogout($userId) 
    {
        try {
            $query = "INSERT INTO auth_logs (user_id, action, ip_address) VALUES (?, 'logout', ?)";
            $stmt = mysqli_prepare($this->conn, $query);
            
            if (!$stmt) {
                throw new Exception("Database prepare failed: " . mysqli_error($this->conn));
            }
            
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            mysqli_stmt_bind_param($stmt, "is", $userId, $ipAddress);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Database execute failed: " . mysqli_stmt_error($stmt));
            }
            
            return true;
        } catch (Exception $e) {
            Logger::error("Error logging logout: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Register a new user
    * @param array $data
    * @return array
    */
    public function register($data) 
    {
        try {
            $query = "INSERT INTO users (name, email, password, user_type, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
                    
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "sssi", 
                $data['name'],
                $data['email'],
                $data['password'],
                $data['user_type']
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Registration failed. Please try again.');
            }
            
            return [
                'id' => mysqli_insert_id($this->conn),
                'name' => $data['name'],
                'email' => $data['email'],
                'user_type' => $data['user_type']
            ];
        } catch (Exception $e) {
            // Log validation or registration error
            $this->logError('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'id' => null,
                'name' => null,
                'email' => null,
                'user_type' => null
            ];
        }
    }

    /*
    * Set secure session parameters
    * @return void
    */
    public function setSecureSessionParams(): void 
    {
        try {
            $secure = isset($_SERVER['HTTPS']); // true if HTTPS
            $httponly = true;
            $samesite = 'Lax';
            $path = '/';
            
            session_set_cookie_params([
                'lifetime' => 7200, // 2 hours
                'path' => $path,
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ]);
        } catch (Exception $e) {
            Logger::error("AuthService@setSecureSessionParams Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Set security headers
    * @return void
    */
    public function setSecurityHeaders(): void 
    {
        try {
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('X-Content-Type-Options: nosniff');
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            header('Content-Security-Policy: default-src \'self\'');
            header('Referrer-Policy: strict-origin-when-cross-origin');
        } catch (Exception $e) {
            Logger::error("AuthService@setSecurityHeaders Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Generate a CSRF token
    * @return string
    */
    public function generateCSRFToken(): string 
    {
        try {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        } catch (Exception $e) {
            Logger::error("AuthService@generateCSRFToken Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Rotate CSRF token
    * @return string
    */
    public function rotateCSRFToken(): string 
    {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            return $_SESSION['csrf_token'];
        } catch (Exception $e) {
            Logger::error("AuthService@rotateCSRFToken Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Validate CSRF token
    * @return bool
    */
    public function validateCSRFToken(): bool 
    {
        try {
            return isset($_POST['csrf_token']) && 
                   isset($_SESSION['csrf_token']) && 
                   hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
        } catch (Exception $e) {
            Logger::error("AuthService@validateCSRFToken Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Check if IP is blocked
    * @return bool
    */
    private function isIpBlocked(): bool 
    {
        try {
            $query = "SELECT COUNT(*) as attempts FROM failed_login_attempts 
                     WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $_SERVER['REMOTE_ADDR']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            return $row['attempts'] >= self::MAX_LOGIN_ATTEMPTS;
        } catch (Exception $e) {
            Logger::error("AuthService@isIpBlocked Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Check if account is blocked
    * @param string $email
    * @return bool
    */
    private function isAccountBlocked($email): bool 
    {
        try {
            $query = "SELECT COUNT(*) as attempts FROM failed_login_attempts 
                     WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
            return $row['attempts'] >= self::MAX_LOGIN_ATTEMPTS;
        } catch (Exception $e) {
            Logger::error("AuthService@isAccountBlocked Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Record a failed login attempt
    * @param string $email
    * @return void
    */
    private function recordFailedAttempt($email): void 
    {
        try {
            $query = "INSERT INTO failed_login_attempts (email, ip_address, created_at) 
                     VALUES (?, ?, NOW())";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "ss", $email, $_SERVER['REMOTE_ADDR']);
            mysqli_stmt_execute($stmt);
        } catch (Exception $e) {
            Logger::error("AuthService@recordFailedAttempt Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Clear failed login attempts
    * @param string $email
    * @return void
    */
    private function clearFailedAttempts($email): void 
    {
        try {
            $query = "DELETE FROM failed_login_attempts WHERE email = ?";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
        } catch (Exception $e) {
            Logger::error("AuthService@clearFailedAttempts Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Update password hash if needed
    * @param int $userId
    * @param string $password
    * @return void
    */
    private function updatePasswordHashIfNeeded($userId, $password): void 
    {
        try {
            // Check if password needs rehashing with updated cost
            if (password_needs_rehash($password, PASSWORD_BCRYPT, ['cost' => 12])) {
                $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                
                $query = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = mysqli_prepare($this->conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $newHash, $userId);
                mysqli_stmt_execute($stmt);
            }
        } catch (Exception $e) {
            Logger::error("AuthService@updatePasswordHashIfNeeded Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Validate login data
    * @param array $data
    * @return array
    */
    public function validateLoginData($data): array 
    {
        try {
            $errors = [];
            $email = trim($data['email'] ?? '');

            // Store email for form repopulation
            $_SESSION['old_email'] = $email;

            // Email validation
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address';
            }
            
            // Password validation
            if (empty($data['password'])) {
                $errors['password'] = 'Password is required';
            }
        
            return [
                'errors' => $errors,
                'email' => $email,
                'password' => $data['password'] ?? ''
            ];
        } catch (Exception $e) {
            Logger::error("AuthService@validateLoginData Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Validate registration data
    * @param array $data
    * @return array
    */
    public function validateRegistrationData($data): array 
    {
        try {
            $errors = [];
            
            // Name validation
            if (empty($data['first_name']) || strlen($data['first_name']) < 2) {
                $errors['first_name'] = 'First name must be at least 2 characters';
            }
            
            if (empty($data['last_name']) || strlen($data['last_name']) < 2) {
                $errors['last_name'] = 'Last name must be at least 2 characters';
            }
            
            // Email validation
            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address';
            } else {
                // Check if email exists
                if ($this->isEmailTaken($data['email'])) {
                    $errors['email'] = 'This email is already registered';
                }
            }
            
            // Password validation
            $this->validatePassword($data, $errors);
        
            return ['errors' => $errors];
        } catch (Exception $e) {
            Logger::error("AuthService@validateRegistrationData Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Validate password
    * @param array $data
    * @param array &$errors
    * @return void
    */
    private function validatePassword($data, &$errors): void 
    {
        try {
            if (empty($data['password'])) {
                $errors['password'] = 'Password is required';
                return;
            }

            $password = $data['password'];
            $validationRules = [
                ['pattern' => '/.{8,}/', 'message' => 'Password must be at least 8 characters'],
                ['pattern' => '/[A-Z]/', 'message' => 'Password must contain at least one uppercase letter'],
                ['pattern' => '/[a-z]/', 'message' => 'Password must contain at least one lowercase letter'],
                ['pattern' => '/[0-9]/', 'message' => 'Password must contain at least one number']
            ];

            foreach ($validationRules as $rule) {
                if (!preg_match($rule['pattern'], $password)) {
                    $errors['password'] = $rule['message'];
                    return;
                }
            }

            if ($password !== ($data['password_confirmation'] ?? '')) {
                $errors['password'] = 'Passwords do not match';
            }
        } catch (Exception $e) {
            Logger::error("AuthService@validatePassword Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Check if email is taken
    * @param string $email
    * @return bool
    */
    private function isEmailTaken($email): bool 
    {
        try {
            $query = "SELECT id FROM users WHERE email = ?";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            return (bool)mysqli_fetch_assoc($result);
        } catch (Exception $e) {
            Logger::error("AuthService@isEmailTaken Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Handle successful login
    * @param array $user
    * @param bool $remember
    * @return bool
    */
    public function handleSuccessfulLogin($user, $remember = false): bool 
    {
        try {
            // Clear session data
            $this->clearSessionData();
            
            // Regenerate session ID
            session_regenerate_id(true);
            
            // Set secure parameters
            $this->setSecureSessionParams();
            $this->setSecurityHeaders();
            
            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['user_name'] = $user['name'];
            
            // Handle remember me
            if ($remember) {
                $token = $this->createRememberToken($user['id']);
                setcookie('remember_token', $token, time() + (86400 * 30), "/");
            }
            
            // Log login and rotate CSRF token
            $this->logLogin($user['id']);
            $this->rotateCSRFToken();

            return true;
        } catch (Exception $e) {
            Logger::error("AuthService@handleSuccessfulLogin Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Clear login session cookies
    * @return void
    */
    public function clearLoginSessionCookies(): void 
    {
        try {
            // Clear remember token if exists
            if (isset($_COOKIE['remember_token'])) {
                $this->removeRememberToken($_COOKIE['remember_token']);
                setcookie('remember_token', '', time() - 3600, '/');
            }
            
            // Log the logout
            if (isset($_SESSION['user_id'])) {
                $this->logLogout($_SESSION['user_id']);
            }
            
            // Clear all session data
            $_SESSION = array();
            
            // Destroy the session cookie
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
            
            // Destroy the session
            session_destroy();
            
            Logger::info("AuthService@clearLoginSessionCookies Logout successful");
        } catch (Exception $e) {
            Logger::error("AuthService@clearLoginSessionCookies Error: " . $e->getMessage());
            throw $e;
        }
    }

    /*
    * Clear session data
    * @return void
    */
    public function clearSessionData(): void 
    {
        unset($_SESSION['errors']);
        unset($_SESSION['error']);
        unset($_SESSION['old_email']);
    }
}