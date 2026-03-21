<?php
namespace App\Controllers\Api;
use App\Models\ProjectModel;

/**
 * ProjectController — app/Controllers/Api/ProjectController.php
 */
class ProjectController extends BaseApiController
{
    public function add(): \CodeIgniter\HTTP\ResponseInterface
    {
        $d = $this->getJson();
        $m = new ProjectModel();
        $tech = isset($d['tech']) ? ProjectModel::encodeTech((array)$d['tech']) : '[]';
        $id = $m->insert([
            'title'       => $d['title']       ?? 'New Project',
            'description' => $d['description'] ?? '',
            'category'    => $d['category']    ?? 'personal',
            'icon'        => $d['icon']        ?? 'fas fa-code',
            'tech'        => $tech,
            'github_url'  => $d['github_url']  ?? '',
            'demo_url'    => $d['demo_url']    ?? '',
            'media_urls'  => $d['media_urls']  ?? '',
            'is_featured' => isset($d['is_featured']) ? (int)$d['is_featured'] : 1,
            'sort_order'  => $m->nextSortOrder(),
        ]);
        return $this->jsonSuccess(['id' => $id], 'Project added.');
    }

    public function update(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $d = $this->getJson();
        $m = new ProjectModel();
        if (!$m->find($id)) return $this->jsonError('Not found.', 404);
        $tech = isset($d['tech']) ? ProjectModel::encodeTech((array)$d['tech']) : null;
        $data = [
            'title'       => $d['title']       ?? '',
            'description' => $d['description'] ?? '',
            'category'    => $d['category']    ?? 'personal',
            'icon'        => $d['icon']        ?? 'fas fa-code',
            'github_url'  => $d['github_url']  ?? '',
            'demo_url'    => $d['demo_url']    ?? '',
            'media_urls'  => $d['media_urls']  ?? '',
            'is_featured' => isset($d['is_featured']) ? (int)$d['is_featured'] : 1,
        ];
        if ($tech !== null) $data['tech'] = $tech;
        $m->update($id, $data);
        return $this->jsonSuccess([], 'Project updated.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $m = new ProjectModel();
        if (!$m->find($id)) return $this->jsonError('Not found.', 404);

        // Delete uploaded media files
        $proj = $m->find($id);
        if (!empty($proj['media_urls'])) {
            $urls = array_filter(array_map('trim', preg_split('/[\n,]+/', $proj['media_urls'])));
            foreach ($urls as $url) {
                if (strpos($url, '/uploads/projects/') !== false) {
                    $path = FCPATH . ltrim(parse_url($url, PHP_URL_PATH), '/');
                    if (file_exists($path)) unlink($path);
                }
            }
        }

        $m->delete($id);
        return $this->jsonSuccess([], 'Project deleted.');
    }

    public function reorder(): \CodeIgniter\HTTP\ResponseInterface
    {
        $d = $this->getJson();
        $m = new ProjectModel();
        foreach ((array)($d['order'] ?? []) as $i => $id) {
            $m->update((int)$id, ['sort_order' => $i + 1]);
        }
        return $this->jsonSuccess([], 'Order saved.');
    }

    /**
     * Upload media file for a project
     * POST /api/project/upload-media/:id
     */
    public function uploadMedia(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $m = new ProjectModel();
        if (!$m->find($id)) return $this->jsonError('Project not found.', 404);

        $file = $this->request->getFile('media');

        // Debug: return file info if something is wrong
        if (!$file) {
            return $this->jsonError('No file received by server.');
        }

        // Check for upload errors directly (more reliable than isValid())
        if ($file->getError() !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload_max_filesize (' . ini_get('upload_max_filesize') . ')',
                UPLOAD_ERR_FORM_SIZE  => 'File exceeds form MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL    => 'File only partially uploaded',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION  => 'Upload stopped by PHP extension',
            ];
            return $this->jsonError($errors[$file->getError()] ?? 'Upload error code: ' . $file->getError());
        }

        // Validate by extension (more reliable than mime for videos)
        $allowedExts = ['jpg','jpeg','png','gif','webp','mp4','webm'];
        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, $allowedExts)) {
            return $this->jsonError('Unsupported file type: ' . $ext . '. Use JPG, PNG, GIF, WEBP, MP4 or WEBM.');
        }

        // Max 50MB
        if ($file->getSize() > 50 * 1024 * 1024) {
            return $this->jsonError('File too large. Maximum 50MB. File size: ' . round($file->getSize()/1024/1024, 2) . 'MB');
        }

        // Save file
        $uploadPath = WRITEPATH . 'uploads/projects/';
        if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

        $newName = 'proj_' . $id . '_' . uniqid() . '.' . $ext;
        $file->move($uploadPath, $newName);

        $url = base_url('uploads/projects/' . $newName);

        $proj = $m->find($id);
        $existing = trim($proj['media_urls'] ?? '');
        $newUrls  = $existing ? $existing . "\n" . $url : $url;
        $m->update($id, ['media_urls' => $newUrls]);

        return $this->jsonSuccess(['url' => $url, 'media_urls' => $newUrls], 'Media uploaded.');
    }

    /**
     * Delete a specific media file from a project
     * POST /api/project/delete-media/:id
     */
    public function deleteMedia(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $d = $this->getJson();
        $url = $d['url'] ?? '';
        if (!$url) return $this->jsonError('No URL provided.');

        $m = new ProjectModel();
        $proj = $m->find($id);
        if (!$proj) return $this->jsonError('Project not found.', 404);

        // Remove from DB
        $urls = array_values(array_filter(
            array_map('trim', preg_split('/[\n,]+/', $proj['media_urls'] ?? ''))
        ));
        $urls = array_filter($urls, fn($u) => $u !== $url);
        $m->update($id, ['media_urls' => implode("\n", $urls)]);

        // Delete file if it's a local upload
        if (strpos($url, '/uploads/projects/') !== false) {
            $path = FCPATH . ltrim(parse_url($url, PHP_URL_PATH), '/');
            if (file_exists($path)) unlink($path);
        }

        return $this->jsonSuccess(['media_urls' => implode("\n", $urls)], 'Media deleted.');
    }
}