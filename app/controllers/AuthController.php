<?php
/**
 * Authentication Controller
 */
class AuthController extends Controller {

    public function loginForm() {
        if ($this->user) {
            Redirect::to('/dashboard');
        }

        View::render('auth/login', [
            'title' => 'Login',
            'csrf_token' => Security::generateCsrfToken(),
            'error' => Redirect::getFlash('error')
        ]);
    }

    public function login() {
        $this->validateCsrf();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Brute force protection
        if (!Security::checkBruteForce($email)) {
            Redirect::withError('Too many failed login attempts. Please try again later.');
            Redirect::to('/login');
        }

        // Validate input
        $validator = new Validator($_POST);
        if (!$validator->validate(['email' => 'required|email', 'password' => 'required'])) {
            Redirect::withError($validator->getFirstError());
            Redirect::to('/login');
        }

        // Authenticate user
        $user = User::authenticate($email, $password);

        if (!$user) {
            Security::recordFailedAttempt($email);
            Redirect::withError('Invalid email or password');
            Redirect::to('/login');
        }

        // Reset brute force tracking
        Security::resetBruteForce($email);

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        // Log activity
        Logger::activity(
            $user['tenant_id'],
            $user['id'],
            'login',
            null,
            null,
            'User logged in'
        );

        Redirect::withSuccess('Welcome back, ' . $user['first_name'] . '!');
        Redirect::to('/dashboard');
    }

    public function logout() {
        if ($this->user) {
            Logger::activity(
                $this->user['tenant_id'],
                $this->user['id'],
                'logout',
                null,
                null,
                'User logged out'
            );
        }

        session_destroy();
        Redirect::withSuccess('You have been logged out successfully');
        Redirect::to('/login');
    }
}
