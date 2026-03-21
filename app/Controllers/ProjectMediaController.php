<?php
namespace App\Controllers;

/**
 * ProjectMediaController — app/Controllers/ProjectMediaController.php
 *
 * Serves uploaded project media files publicly at:
 * /uploads/projects/{filename}
 *
 * Add this route to app/Config/Routes.php:
 * $routes->get('uploads/projects/(:any)', 'ProjectMediaController::serve/$1');
 */
class ProjectMediaController extends BaseController
{
    public function serve(string $filename): void
    {
        // Sanitize — no path traversal
        $filename = basename($filename);
        $path     = WRITEPATH . 'uploads/projects/' . $filename;

        if (!file_exists($path)) {
            http_response_code(404); exit;
        }

        $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'jpg','jpeg' => 'image/jpeg',
            'png'        => 'image/png',
            'gif'        => 'image/gif',
            'webp'       => 'image/webp',
            'mp4'        => 'video/mp4',
            'webm'       => 'video/webm',
            default      => 'application/octet-stream',
        };

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=31536000');
        readfile($path);
        exit;
    }
}