<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Exception;

class AuthController extends Controller
{
    private AuthService $authService;
    private Session $session;

    public function __construct()
    {
        $userRepository = new UserRepository();
        $this->authService = new AuthService($userRepository);
        $this->session = Session::getInstance();
    }

    /**
     * Display or process user login
     */
    public function login(): void
    {
        // If already logged in, redirect to dashboard
        if ($this->session->has('user')) {
            $this->redirect('/dashboard');
        }

        if ($this->isPost()) {
            $data = $this->getPostData();
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $remember = isset($data['remember_me']);

            // Validate CSRF
            if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
                $this->session->setFlash('error', 'CSRF validation failed. Please try again.');
                $this->redirect('/login');
            }

            try {
                $this->authService->login($email, $password, $remember);
                
                // Merge guest cart items into the database upon login
                $user = $this->session->get('user');
                if ($user) {
                    $cartService = new \App\Services\CartService();
                    $cartService->mergeSessionCartIntoDb((int)$user['id']);
                }

                $this->session->setFlash('success', 'Welcome back to Elze.eg!');
                $this->redirect('/dashboard');
            } catch (Exception $e) {
                if ($e->getMessage() === 'UNVERIFIED') {
                    // Send to OTP page
                    $this->session->set('pending_verify_email', $email);
                    $this->session->setFlash('error', 'Please verify your email address to log in.');
                    $this->redirect('/verify-otp');
                } else {
                    $this->session->setFlash('error', $e->getMessage());
                    $this->redirect('/login');
                }
            }
        }

        $this->render('auth/login', [
            'title' => 'Login | Elze.eg',
            'csrf_token' => $this->session->getCsrfToken()
        ]);
    }

    /**
     * Display or process registration
     */
    public function register(): void
    {
        if ($this->session->has('user')) {
            $this->redirect('/dashboard');
        }

        if ($this->isPost()) {
            $data = $this->getPostData();
            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $confirmPassword = $data['confirm_password'] ?? '';

            // Validate CSRF
            if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
                $this->session->setFlash('error', 'CSRF validation failed. Please try again.');
                $this->redirect('/register');
            }

            // Server-side form validations
            if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
                $this->session->setFlash('error', 'Please fill out all required fields.');
                $this->redirect('/register');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->session->setFlash('error', 'Please enter a valid email address.');
                $this->redirect('/register');
            }

            if (strlen($password) < 6) {
                $this->session->setFlash('error', 'Password must be at least 6 characters long.');
                $this->redirect('/register');
            }

            if ($password !== $confirmPassword) {
                $this->session->setFlash('error', 'Passwords do not match.');
                $this->redirect('/register');
            }

            try {
                if ($this->authService->register($name, $email, $password)) {
                    $this->session->set('pending_verify_email', $email);
                    $this->session->setFlash('success', 'Registration successful! Enter the 6-digit code sent to your email.');
                    $this->redirect('/verify-otp');
                }
            } catch (Exception $e) {
                $this->session->setFlash('error', $e->getMessage());
                $this->redirect('/register');
            }
        }

        $this->render('auth/register', [
            'title' => 'Register | Elze.eg',
            'csrf_token' => $this->session->getCsrfToken()
        ]);
    }

    /**
     * Display or process OTP Verification
     */
    public function verifyOtp(): void
    {
        $email = $this->session->get('pending_verify_email');
        
        if (!$email) {
            $this->session->setFlash('error', 'No pending verification request found.');
            $this->redirect('/login');
        }

        if ($this->isPost()) {
            $data = $this->getPostData();
            $otp = $data['otp'] ?? '';

            if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
                $this->session->setFlash('error', 'CSRF validation failed.');
                $this->redirect('/verify-otp');
            }

            try {
                if ($this->authService->verifyOtp($email, $otp)) {
                    $this->session->remove('pending_verify_email');
                    $this->session->setFlash('success', 'Email verified successfully! You can now log in.');
                    $this->redirect('/login');
                }
            } catch (Exception $e) {
                $this->session->setFlash('error', $e->getMessage());
                $this->redirect('/verify-otp');
            }
        }

        $this->render('auth/verify_otp', [
            'title' => 'Verify Email | Elze.eg',
            'email' => $email,
            'csrf_token' => $this->session->getCsrfToken()
        ]);
    }

    /**
     * Resend verification OTP code
     */
    public function resendOtp(): void
    {
        $email = $this->session->get('pending_verify_email');
        if (!$email) {
            $this->session->setFlash('error', 'Request session expired.');
            $this->redirect('/login');
        }

        try {
            $this->authService->resendOtp($email);
            $this->session->setFlash('success', 'A new verification code has been generated and logged.');
        } catch (Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
        }
        $this->redirect('/verify-otp');
    }

    /**
     * Handle Forgot Password email request
     */
    public function forgotPassword(): void
    {
        if ($this->isPost()) {
            $data = $this->getPostData();
            $email = $data['email'] ?? '';

            if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
                $this->session->setFlash('error', 'CSRF validation failed.');
                $this->redirect('/forgot-password');
            }

            try {
                $this->authService->sendForgotPasswordOtp($email);
                $this->session->set('reset_email', $email);
                $this->session->setFlash('success', 'A password reset code has been sent.');
                $this->redirect('/reset-password');
            } catch (Exception $e) {
                $this->session->setFlash('error', $e->getMessage());
                $this->redirect('/forgot-password');
            }
        }

        $this->render('auth/forgot_password', [
            'title' => 'Forgot Password | Elze.eg',
            'csrf_token' => $this->session->getCsrfToken()
        ]);
    }

    /**
     * Handle Reset Password page using OTP validation
     */
    public function resetPassword(): void
    {
        $email = $this->session->get('reset_email');
        if (!$email) {
            $this->session->setFlash('error', 'Session expired. Please request a new code.');
            $this->redirect('/forgot-password');
        }

        if ($this->isPost()) {
            $data = $this->getPostData();
            $otp = $data['otp'] ?? '';
            $password = $data['password'] ?? '';
            $confirmPassword = $data['confirm_password'] ?? '';

            if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
                $this->session->setFlash('error', 'CSRF validation failed.');
                $this->redirect('/reset-password');
            }

            if ($password !== $confirmPassword) {
                $this->session->setFlash('error', 'Passwords do not match.');
                $this->redirect('/reset-password');
            }

            try {
                $this->authService->resetPasswordWithOtp($email, $otp, $password);
                $this->session->remove('reset_email');
                $this->session->setFlash('success', 'Your password has been reset successfully. You can now log in.');
                $this->redirect('/login');
            } catch (Exception $e) {
                $this->session->setFlash('error', $e->getMessage());
                $this->redirect('/reset-password');
            }
        }

        $this->render('auth/reset_password', [
            'title' => 'Reset Password | Elze.eg',
            'email' => $email,
            'csrf_token' => $this->session->getCsrfToken()
        ]);
    }

    /**
     * Log user out
     */
    public function logout(): void
    {
        $this->authService->logout();
        $this->session->setFlash('success', 'Logged out successfully.');
        $this->redirect('/');
    }
}
