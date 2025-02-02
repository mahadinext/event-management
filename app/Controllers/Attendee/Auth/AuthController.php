<?php

namespace App\Controllers\Attendee\Auth;
use App\Core\Controller;
use App\Core\Logger;
use App\Services\Auth\AuthService;
use App\Constants\UserConstants;
use Exception;

class AuthController extends Controller {
    private $authService;
    
    public function __construct() 
    {
        parent::__construct();
        $this->authService = new AuthService($this->conn);
    }
    
    /*
    * Show login form
    * @return void
    */
    public function showLoginForm() 
    {
        try {
            if (isset($_SESSION['user_id'])) {
                return $this->redirect('/');
            }

            return $this->view('auth/attendee/login', [
                'csrf_token' => $this->authService->generateCSRFToken()
            ]);
        } catch (Exception $e) {
            Logger::error('AuthController@showLoginForm Critical error during login', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->redirect('/');
        }
    }
    
    /*
    * Login
    * @return void
    */
    public function login() 
    {
        try {
            if (isset($_SESSION['user_id'])) {
                return $this->redirect('/');
            }

            // Clear any old form data if this is a fresh GET request
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $this->authService->clearSessionData();

                // Initial GET request
                return $this->redirect('/attendee/login');
            }
    
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!$this->authService->validateCSRFToken()) {
                    Logger::error("AuthController@login CSRF token validation failed", [
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'user_agent' => $_SERVER['HTTP_USER_AGENT']
                    ]);
                    throw new Exception('Invalid request');
                }

                $validation = $this->authService->validateLoginData($_POST);
                
                // If basic validation passes, attempt login
                if (empty($validation['errors'])) {
                    try {
                        $user = $this->authService->attempt(
                            $_POST['email'], 
                            $_POST['password'], 
                            UserConstants::ROLE_TYPE_ATTENDEE
                        );
                    
                        $isLoginSuccess = $this->authService->handleSuccessfulLogin($user, isset($_POST['remember']));

                        if ($isLoginSuccess) {
                            $redirect = $_SESSION['intended_url'] ?? '/';
                            unset($_SESSION['intended_url']);
                            
                            return $this->redirect($redirect);
                        }

                        $errors['email'] = 'Invalid credentials';
                        $errors['password'] = ' ';
                    } catch (Exception $e) {
                        Logger::error('AuthController@login Login failed', [
                            'email' => $_POST['email'],
                            'error' => $e->getMessage()
                        ]);
                        $errors['email'] = $e->getMessage();
                        $errors['password'] = ' '; // Space to trigger invalid state
                        $error = $e->getMessage();
                    }
                }
            
                // Store errors in session
                $_SESSION['errors'] = $validation['errors'];
                $_SESSION['error'] = $validation['errors']['email'] ?? ($validation['errors']['password'] ?? 'Login failed');
                
                return $this->view('auth/attendee/login', [
                    'errors' => $_SESSION['errors'] ?? [],
                    'error' => $_SESSION['error'] ?? null,
                    'email' => $_SESSION['old_email'] ?? '',
                    'csrf_token' => $this->authService->generateCSRFToken()
                ]);
            }
        } catch (Exception $e) {
            Logger::error('AuthController@login Critical error during login', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $_SESSION['error'] = 'An unexpected error occurred. Please try again.';
            return $this->redirect('/attendee/login');
        }
    }
    
    /*
    * Logout
    * @return void
    */
    public function logout() 
    {
        try {
            Logger::info("Logout initiated for user: " . ($_SESSION['user_id'] ?? 'unknown'));
            
            $this->authService->clearLoginSessionCookies();
            
            Logger::info("AuthController@logout Logout successful");
            
            // Redirect to initial page
            return $this->redirect('/');
        } catch (Exception $e) {
            Logger::error("AuthController@logout Logout error: " . $e->getMessage());
            return $this->redirect('/');
        }
    }
    
    /*
    * Show registration form
    * @return void
    */
    public function showRegistrationForm() 
    {
        try {
            if (isset($_SESSION['user_id'])) {
                return $this->redirect('/');
            }

            return $this->view('auth/attendee/register', [
                'csrf_token' => $this->authService->generateCSRFToken()
            ]);
        } catch (Exception $e) {
            Logger::error('AuthController@showRegistrationForm Critical error during registration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->redirect('/');
        }
    }

    /*
    * Register
    * @return void
    */
    public function register() 
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Log registration attempt
                $this->logInfo('Registration attempt', [
                    'email' => $_POST['email'] ?? 'not provided',
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);

                if (!$this->authService->validateCSRFToken()) {
                    throw new Exception('Invalid request');
                }

                $validation = $this->authService->validateRegistrationData($_POST);
            
                if (empty($validation['errors'])) {
                    $user = $this->authService->register([
                        'name' => $_POST['first_name'] . ' ' . $_POST['last_name'],
                        'email' => $_POST['email'],
                        'password' => password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 11]),
                        'user_type' => UserConstants::ROLE_TYPE_ATTENDEE
                    ]);

                    if ($user && !empty($user['id'])) {
                        $this->authService->handleSuccessfulLogin($user);
                        $this->setFlashMessage('Registration successful!');
                        return $this->redirect('/');
                    }
                }
            
                return $this->view('auth/attendee/register', [
                    'errors' => $validation['errors'],
                    'old' => $_POST,
                    'csrf_token' => $this->authService->generateCSRFToken()
                ]);
            }
            
            return $this->view('auth/attendee/register', [
                'errors' => $_SESSION['errors'] ?? [],
                'error' => $_SESSION['error'] ?? null,
                'email' => $_SESSION['old_email'] ?? '',
                'csrf_token' => $this->authService->generateCSRFToken()
            ]);
        } catch (Exception $e) {
            // Log critical errors
            Logger::error('AuthController@register Critical error during registration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->view('errors/500', [
                'message' => 'An unexpected error occurred. Please try again later.'
            ]);
        }
    }
}