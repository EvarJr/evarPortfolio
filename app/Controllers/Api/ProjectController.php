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
        $m->delete($id);
        return $this->jsonSuccess([], 'Project deleted.');
    }

    public function reorder(): \CodeIgniter\HTTP\ResponseInterface
    {
        $d = $this->getJson();
        $m = new ProjectModel();
        // $d['order'] = [id, id, id, ...] in new order
        foreach ((array)($d['order'] ?? []) as $i => $id) {
            $m->update((int)$id, ['sort_order' => $i + 1]);
        }
        return $this->jsonSuccess([], 'Order saved.');
    }
}
