<?php

namespace App\Controllers\Api;

use App\Models\AboutModel;

/**
 * AboutController — app/Controllers/Api/AboutController.php
 */
class AboutController extends BaseApiController
{
    public function update()
    {
        $d = $this->getJson();
        (new AboutModel())->updateAbout([
            'tagline'           => $d['tagline']           ?? '',
            'bio'               => $d['bio']               ?? '',
            'photo_position'    => $d['photo_position']    ?? '50% 50%', 
            'cv_label'          => $d['cv_label']          ?? 'Download CV',
            'btn_contact_label' => $d['btn_contact_label'] ?? 'Contact',
            'btn_contact_email' => $d['btn_contact_email'] ?? '',
            'github'            => $d['github']            ?? '',
            'linkedin_url'      => $d['linkedin_url']      ?? '',
            'twitter'           => $d['twitter']           ?? '',
            'facebook'          => $d['facebook']          ?? '',
        ]);
        return $this->jsonSuccess([], 'About info updated.');
    }

    public function uploadPhoto()
    {
        $file = $this->request->getFile('photo');

        if (! $file || ! $file->isValid()) {
            return $this->jsonError('No valid file uploaded.');
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (! in_array($file->getMimeType(), $allowed)) {
            return $this->jsonError('Only JPG, PNG, WEBP or GIF allowed.');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->jsonError('File too large. Max 5MB.');
        }

        $newName   = 'profile_' . time() . '.' . $file->getClientExtension();
        $uploadDir = FCPATH . 'uploads/';

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Remove old photo
        $model = new AboutModel();
        $about = $model->getAbout();
        if (! empty($about['photo'])) {
            $old = FCPATH . $about['photo'];
            if (file_exists($old)) @unlink($old);
        }

        $file->move($uploadDir, $newName);
        $model->updateAbout(['photo' => 'uploads/' . $newName]);

        return $this->jsonSuccess(['photo' => 'uploads/' . $newName], 'Photo uploaded.');
    }
}
