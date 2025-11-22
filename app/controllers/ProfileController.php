<?php
/**
 * Profile Controller
 */
class ProfileController extends Controller {

    public function index() {
        $this->requireAuth();

        View::renderWithLayout('profile/index', [
            'title' => 'My Profile',
            'user' => $this->user,
            'csrf_token' => Security::generateCsrfToken(),
            'success' => Redirect::getFlash('success'),
            'error' => Redirect::getFlash('error')
        ]);
    }

    public function update() {
        $this->requireAuth();
        $this->validateCsrf();

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $timezone = $_POST['timezone'] ?? 'UTC';
        $newPassword = $_POST['new_password'] ?? '';

        // Validate
        $validator = new Validator($_POST);
        if (!$validator->validate([
            'first_name' => 'required',
            'last_name' => 'required'
        ])) {
            Redirect::withError($validator->getFirstError());
            Redirect::to('/profile');
        }

        // Update basic info
        User::update($this->user['id'], [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'timezone' => $timezone
        ]);

        // Update password if provided
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                Redirect::withError('Password must be at least 8 characters');
                Redirect::to('/profile');
            }

            User::updatePassword($this->user['id'], $newPassword);
        }

        Logger::activity($this->user['tenant_id'], $this->user['id'], 'profile_updated', null, null, 'Updated profile');

        Redirect::withSuccess('Profile updated successfully');
        Redirect::to('/profile');
    }
}
