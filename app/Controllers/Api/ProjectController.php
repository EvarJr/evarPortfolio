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
        if (!$file || !$file->isValid()) {
            return $this->jsonError('No valid file uploaded.');
        }

        // Validate type
        $allowed = ['image/jpeg','image/png','image/gif','image/webp','video/mp4','video/webm'];
        if (!in_array($file->getMimeType(), $allowed)) {
            return $this->jsonError('Unsupported file type. Use JPG, PNG, GIF, WEBP, MP4 or WEBM.');
        }

        // Max 10MB
        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->jsonError('File too large. Maximum 10MB.');
        }

        // Save to writable/uploads/projects/
        $uploadPath = WRITEPATH . 'uploads/projects/';
        if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

        $newName = 'proj_' . $id . '_' . uniqid() . '.' . $file->getClientExtension();
        $file->move($uploadPath, $newName);

        // Build public URL
        $url = base_url('uploads/projects/' . $newName);

        // Append to existing media_urls
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