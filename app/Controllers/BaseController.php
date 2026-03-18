<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController — app/Controllers/BaseController.php
 *
 * REPLACE your existing BaseController with this file.
 * Extends CI4's native Controller and adds shared helpers.
 */
abstract class BaseController extends Controller
{
    protected $request;

    public function initController(
        RequestInterface  $request,
        ResponseInterface $response,
        LoggerInterface   $logger
    ): void {
        parent::initController($request, $response, $logger);
    }

    // ── JSON response helpers ─────────────────────────────────

    protected function jsonSuccess(array $data = [], string $message = 'Success'): ResponseInterface
    {
        return $this->response
            ->setStatusCode(200)
            ->setContentType('application/json')
            ->setJSON(array_merge(['success' => true, 'message' => $message], $data));
    }

    protected function jsonError(string $message, int $status = 400): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON(['success' => false, 'message' => $message]);
    }

    // ── Read raw JSON body (for AJAX POST) ────────────────────

    protected function getJson(): array
    {
        return json_decode($this->request->getBody(), true) ?? [];
    }

    // ── Auth helper ───────────────────────────────────────────

    protected function isLoggedIn(): bool
    {
        return (bool) session()->get('admin_logged_in');
    }
}
