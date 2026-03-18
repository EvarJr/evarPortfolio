<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * AuthFilter — app/Filters/AuthFilter.php
 *
 * Protects admin + api routes.
 * Already registered in app/Config/Filters.php (see that file).
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('admin_logged_in')) {
            // API calls get a JSON 401
            if (str_starts_with($request->getUri()->getPath(), 'api/') ||
                str_contains($request->getUri()->getPath(), '/api/')) {
                return service('response')
                    ->setStatusCode(401)
                    ->setContentType('application/json')
                    ->setJSON(['success' => false, 'message' => 'Unauthorized. Please log in.']);
            }
            return redirect()->to(base_url('login'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
