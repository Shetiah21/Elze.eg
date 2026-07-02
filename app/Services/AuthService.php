<?php

namespace App\Services;

use App\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Core\Session;
use Exception;

class AuthService
{
    private UserRepositoryInterface $userRepository;
    private Session $session;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->session = Session::getInstance();
    }

    /**
     * Register a new user account, hash the password, generate a 6-digit OTP, and dispatch a mock email.
     */
    public function register(string $name, string $email, string $password): bool
    {
        // 1. Check if email already exists
        if ($this->userRepository->findByEmail($email) !== null) {
            throw new Exception("This email address is already registered.");
        }

        // 2. Create User model instance
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->role = 'user';
        $user->status = 'active';
        
        // 3. Generate verification OTP
        $otp = $this->generateOtpCode();
        $user->otp_code = $otp;
        $user->otp_expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // 4. Save to database
        if ($this->userRepository->save($user)) {
            // 5. Send mock verification email
            $this->logMockEmail($email, "Elze.eg Verification Code", "Hi {$name},\n\nWelcome to Elze.eg. Please verify your account using the 6-digit code: {$otp}\nThis code will expire in 15 minutes.");
            return true;
        }

        return false;
    }

    /**
     * Validate verification OTP and mark email as verified
     */
    public function verifyOtp(string $email, string $otp): bool
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw new Exception("Account not found.");
        }

        if ($user->otp_code !== $otp) {
            throw new Exception("Invalid verification code.");
        }

        if (strtotime($user->otp_expires_at) < time()) {
            throw new Exception("Verification code has expired. Please request a new one.");
        }

        // Set verified and clear OTP keys
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->otp_code = null;
        $user->otp_expires_at = null;

        return $this->userRepository->save($user);
    }

    /**
     * Resend verification OTP code
     */
    public function resendOtp(string $email): bool
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw new Exception("Account not found.");
        }

        $otp = $this->generateOtpCode();
        $user->otp_code = $otp;
        $user->otp_expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        if ($this->userRepository->save($user)) {
            $this->logMockEmail($email, "Elze.eg New Verification Code", "Hi {$user->name},\n\nYour new verification code is: {$otp}\nThis code will expire in 15 minutes.");
            return true;
        }
        return false;
    }

    /**
     * Authenticate user credentials, check active status/email verification, and save sessions.
     */
    public function login(string $email, string $password, bool $rememberMe = false): bool
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user->password)) {
            throw new Exception("Invalid email or password.");
        }

        if ($user->status === 'blocked') {
            throw new Exception("Your account has been suspended. Please contact customer support.");
        }

        if ($user->email_verified_at === null) {
            // Force user to verify before logging in
            throw new Exception("UNVERIFIED");
        }

        // Set session
        $this->session->set('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ]);

        // Remember Me Cookie Handler
        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            $user->remember_token = $token;
            if ($this->userRepository->save($user)) {
                // Set cookie for 30 days
                if (!headers_sent()) {
                    setcookie('elze_remember', $token, time() + (86400 * 30), '/', '', false, true);
                }
            }
        }

        return true;
    }

    /**
     * Terminate user session and clear cookies
     */
    public function logout(): void
    {
        $userSession = $this->session->get('user');
        if ($userSession) {
            $user = $this->userRepository->findById((int)$userSession['id']);
            if ($user) {
                $user->remember_token = null;
                $this->userRepository->save($user);
            }
        }

        // Clear session and cookies
        $this->session->remove('user');
        $this->session->destroy();

        if (isset($_COOKIE['elze_remember']) && !headers_sent()) {
            setcookie('elze_remember', '', time() - 3600, '/');
        }
    }

    /**
     * Trigger OTP generation for password reset
     */
    public function sendForgotPasswordOtp(string $email): bool
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            // Silently return true or throw an error depending on preference. Let's throw a clean exception.
            throw new Exception("Account not found with this email.");
        }

        $otp = $this->generateOtpCode();
        $user->otp_code = $otp;
        $user->otp_expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        if ($this->userRepository->save($user)) {
            $this->logMockEmail($email, "Elze.eg Password Reset OTP", "Hi {$user->name},\n\nWe received a request to reset your password. Use the 6-digit code below to proceed:\n\n{$otp}\n\nIf you did not request this, you can ignore this email.");
            return true;
        }
        return false;
    }

    /**
     * Reset password using active OTP validation
     */
    public function resetPasswordWithOtp(string $email, string $otp, string $newPassword): bool
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw new Exception("Account not found.");
        }

        if ($user->otp_code !== $otp) {
            throw new Exception("Invalid password reset code.");
        }

        if (strtotime($user->otp_expires_at) < time()) {
            throw new Exception("Reset code has expired.");
        }

        $user->password = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->remember_token = null; // Invalidate current remember-me tokens for safety

        return $this->userRepository->save($user);
    }

    /**
     * Check if a remember-me cookie is present and log the user in automatically
     */
    public function checkRememberMe(): bool
    {
        if ($this->session->has('user')) {
            return true;
        }

        $cookieToken = $_COOKIE['elze_remember'] ?? null;
        if ($cookieToken) {
            $user = $this->userRepository->findByRememberToken($cookieToken);
            if ($user && $user->status === 'active' && $user->email_verified_at !== null) {
                $this->session->set('user', [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ]);
                return true;
            }
        }
        return false;
    }

    /**
     * Generate 6-digit numeric OTP code
     */
    private function generateOtpCode(): string
    {
        return (string)rand(100000, 999999);
    }

    /**
     * Log verification codes to a local file for mock testing
     */
    private function logMockEmail(string $to, string $subject, string $body): void
    {
        $logDir = dirname(dirname(__DIR__)) . '/storage/logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/mail.log';
        $timestamp = date('Y-m-d H:i:s');
        $divider = str_repeat('-', 50) . "\n";
        
        $logContent = "Timestamp: {$timestamp}\nTo: {$to}\nSubject: {$subject}\nMessage:\n{$body}\n" . $divider;
        
        file_put_contents($logFile, $logContent, FILE_APPEND);
    }
}
