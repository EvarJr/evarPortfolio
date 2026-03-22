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
            'media_urls'  => '',
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

        // ★ FIX: Never include media_urls here.
        // Media is managed exclusively via uploadMedia() and deleteMedia().
        // Including it here would overwrite the DB with a stale JS value,
        // losing any uploads done in the current edit session.
        $data = [
            'title'       => $d['title']       ?? '',
            'description' => $d['description'] ?? '',
            'category'    => $d['category']    ?? 'personal',
            'icon'        => $d['icon']        ?? 'fas fa-code',
            'github_url'  => $d['github_url']  ?? '',
            'demo_url'    => $d['demo_url']    ?? '',
            'is_featured' => isset($d['is_featured']) ? (int)$d['is_featured'] : 1,
        ];
        if ($tech !== null) $data['tech'] = $tech;
        $m->update($id, $data);
        return $this->jsonSuccess([], 'Project updated.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $m = new ProjectModel();
        $proj = $m->find($id);
        if (!$proj) return $this->jsonError('Not found.', 404);

        // Delete all media from Cloudinary before removing the project
        if (!empty($proj['media_urls'])) {
            $urls = array_values(array_filter(
                array_map('trim', preg_split('/[\n,]+/', $proj['media_urls']))
            ));
            foreach ($urls as $url) {
                $this->deleteFromCloudinary($url);
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
     * Add a YouTube URL to a project's media_urls
     * POST /api/project/add-youtube/:id
     */
    public function addYoutube(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $d = $this->getJson();
        $url = trim($d['url'] ?? '');
        if (!$url) return $this->jsonError('No URL provided.');
        if (!str_contains($url, 'youtube') && !str_contains($url, 'youtu.be')) {
            return $this->jsonError('Not a valid YouTube URL.');
        }

        $m = new ProjectModel();
        $proj = $m->find($id);
        if (!$proj) return $this->jsonError('Project not found.', 404);

        $existing = array_values(array_filter(
            array_map('trim', preg_split('/[\n,]+/', $proj['media_urls'] ?? ''))
        ));
        if (!in_array($url, $existing)) $existing[] = $url;
        $newUrls = implode("\n", $existing);
        $m->update($id, ['media_urls' => $newUrls]);

        return $this->jsonSuccess(['media_urls' => $newUrls], 'YouTube link added.');
    }

    /**
     * Upload media file for a project via Cloudinary
     * POST /api/project/upload-media/:id
     */
    public function uploadMedia(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $m = new ProjectModel();
        if (!$m->find($id)) return $this->jsonError('Project not found.', 404);

        $file = $this->request->getFile('media');
        if (!$file) return $this->jsonError('No file received by server.');

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

        $allowedExts = ['jpg','jpeg','png','gif','webp','mp4','webm','mov','avi'];
        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, $allowedExts)) {
            return $this->jsonError('Unsupported file type: ' . $ext . '. Use JPG, PNG, GIF, WEBP, MP4 or WEBM.');
        }
        if ($file->getSize() > 100 * 1024 * 1024) {
            return $this->jsonError('File too large. Maximum 100MB. Size: ' . round($file->getSize()/1024/1024, 2) . 'MB');
        }

        $cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? env('CLOUDINARY_CLOUD_NAME') ?? '';
        $apiKey    = $_ENV['CLOUDINARY_API_KEY']    ?? env('CLOUDINARY_API_KEY')    ?? '';
        $apiSecret = $_ENV['CLOUDINARY_API_SECRET'] ?? env('CLOUDINARY_API_SECRET') ?? '';

        if (!$cloudName || !$apiKey || !$apiSecret) {
            return $this->jsonError('Cloudinary not configured. Add CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET to Railway environment variables.');
        }

        $isVideo      = in_array($ext, ['mp4','webm','mov','avi']);
        $resourceType = $isVideo ? 'video' : 'image';
        $folder       = 'evarportfolio/projects';
        $timestamp    = time();

        // Signature: params sorted alphabetically, raw string (no URL encoding), secret appended
        $sigString = "folder={$folder}&timestamp={$timestamp}";
        $signature = hash('sha256', $sigString . $apiSecret);

        $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloudName}/{$resourceType}/upload");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 180,
            CURLOPT_POSTFIELDS     => [
                'file'      => new \CURLFile($file->getTempName(), $file->getMimeType(), $file->getClientName()),
                'api_key'   => $apiKey,
                'timestamp' => $timestamp,
                'folder'    => $folder,
                'signature' => $signature,
            ],
        ]);
        $response = curl_exec($ch);
        $curlErr  = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErr) return $this->jsonError('cURL error: ' . $curlErr);

        $result = json_decode($response, true);
        if (empty($result['secure_url'])) {
            $errMsg = $result['error']['message'] ?? ('HTTP ' . $httpCode . ': ' . $response);
            return $this->jsonError('Cloudinary error: ' . $errMsg);
        }

        $url = $result['secure_url'];

        // ★ Atomic append using SQL CONCAT to prevent race condition.
        // If two uploads happen in quick succession, a read-then-write approach
        // causes the second upload to overwrite the first (reads empty, writes only itself).
        // Using SQL CONCAT means the DB appends directly without a round-trip read.
        $db = \Config\Database::connect();
        $db->query(
            "UPDATE portfolio_projects SET media_urls = CASE WHEN media_urls = '' OR media_urls IS NULL THEN ? ELSE CONCAT(media_urls, '\n', ?) END WHERE id = ?",
            [$url, $url, $id]
        );

        // Read back the updated value to return to the client
        $proj    = $m->find($id);
        $newUrls = $proj['media_urls'] ?? $url;

        return $this->jsonSuccess(['url' => $url, 'media_urls' => $newUrls], 'Media uploaded.');
    }

    /**
     * Delete a specific media item from a project
     * Also deletes the file from Cloudinary
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

        // Delete from Cloudinary
        $this->deleteFromCloudinary($url);

        $urls    = array_values(array_filter(
            array_map('trim', preg_split('/[\n,]+/', $proj['media_urls'] ?? ''))
        ));
        $urls    = array_filter($urls, fn($u) => $u !== $url);
        $newUrls = implode("\n", $urls);
        $m->update($id, ['media_urls' => $newUrls]);

        return $this->jsonSuccess(['media_urls' => $newUrls], 'Media removed.');
    }

    /**
     * Delete a file from Cloudinary by its secure_url.
     */
    private function deleteFromCloudinary(string $url): void
    {
        $cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? env('CLOUDINARY_CLOUD_NAME') ?? '';
        $apiKey    = $_ENV['CLOUDINARY_API_KEY']    ?? env('CLOUDINARY_API_KEY')    ?? '';
        $apiSecret = $_ENV['CLOUDINARY_API_SECRET'] ?? env('CLOUDINARY_API_SECRET') ?? '';

        if (!$cloudName || !$apiKey || !$apiSecret || !$url) return;

        // Skip YouTube — not hosted on Cloudinary
        if (str_contains($url, 'youtube') || str_contains($url, 'youtu.be')) return;

        // Extract public_id from URL
        // Format: https://res.cloudinary.com/{cloud}/image|video/upload/v{ver}/{public_id}.{ext}
        if (!preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\.[a-z0-9]+)?$/i', $url, $matches)) return;
        $publicId     = $matches[1];
        $resourceType = str_contains($url, '/video/') ? 'video' : 'image';
        $timestamp    = time();
        $sigString    = "public_id={$publicId}&timestamp={$timestamp}";
        $signature    = hash('sha256', $sigString . $apiSecret);

        $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloudName}/{$resourceType}/destroy");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_POSTFIELDS     => [
                'public_id' => $publicId,
                'api_key'   => $apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}