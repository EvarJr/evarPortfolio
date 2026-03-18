<?php
namespace App\Controllers\Api;
use App\Models\AdminUserModel;

class AccountController extends BaseApiController
{
    public function changePassword()
    {
        $d   = $this->getJson();
        $cur = $d['current_password'] ?? '';
        $new = $d['new_password']     ?? '';
        $con = $d['confirm_password'] ?? '';

        // ── Validations ──────────────────────────────────────
        if (empty($cur) || empty($new) || empty($con)) {
            return $this->jsonError('All fields are required.');
        }

        if (strlen($new) < 6) {
            return $this->jsonError('New password must be at least 6 characters.');
        }

        if ($new !== $con) {
            return $this->jsonError('New password and confirmation do not match.');
        }

        // ── Verify current password ───────────────────────────
        $model = new AdminUserModel();
        $uid   = (int) (session()->get('admin_id') ?? 1);
        $user  = $model->find($uid);

        if (! $user) {
            return $this->jsonError('User not found.', 404);
        }

        if (! password_verify($cur, $user['password'])) {
            return $this->jsonError('Current password is incorrect.');
        }

        // ── Save new hash ──────────────────────────────────────
        $newHash = password_hash($new, PASSWORD_BCRYPT);
        $model->update($uid, ['password' => $newHash]);

        // ── Update session so user stays logged in ─────────────
        // (session is still valid — no need to log out)
        session()->setFlashdata('success', 'Password changed successfully.');

        return $this->jsonSuccess([], 'Password changed successfully.');
    }
}