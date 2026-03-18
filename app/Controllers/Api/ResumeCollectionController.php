<?php

namespace App\Controllers\Api;

use App\Models\ResumeCollectionModel;

/**
 * ResumeCollectionController
 * app/Controllers/Api/ResumeCollectionController.php
 *
 * Handles resume collection CRUD:
 * create blank, clone, rename, set active, delete
 */
class ResumeCollectionController extends BaseApiController
{
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $d    = $this->getJson();
        $name = trim($d['name'] ?? 'New Resume');
        if (empty($name)) return $this->jsonError('Name is required.');

        $model = new ResumeCollectionModel();
        $id    = $model->createBlank($name);
        return $this->jsonSuccess(['id' => $id, 'name' => $name], 'Resume created.');
    }

    public function clone(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $d       = $this->getJson();
        $name    = trim($d['name'] ?? 'Copy of Resume');
        $model   = new ResumeCollectionModel();
        $source  = $model->find($id);

        if (!$source) return $this->jsonError('Source resume not found.', 404);

        $newId = $model->cloneResume($id, $name);
        return $this->jsonSuccess(['id' => $newId, 'name' => $name], 'Resume cloned successfully.');
    }

    public function rename(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $d    = $this->getJson();
        $name = trim($d['name'] ?? '');
        if (empty($name)) return $this->jsonError('Name is required.');

        $model = new ResumeCollectionModel();
        if (!$model->find($id)) return $this->jsonError('Resume not found.', 404);

        $model->update($id, ['name' => $name]);
        return $this->jsonSuccess([], 'Resume renamed.');
    }

    public function setActive(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $model = new ResumeCollectionModel();
        if (!$model->find($id)) return $this->jsonError('Resume not found.', 404);

        $model->setActive($id);
        return $this->jsonSuccess([], 'Resume set as active. Portfolio will now show this resume.');
    }

    public function delete(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $model = new ResumeCollectionModel();
        if (!$model->find($id)) return $this->jsonError('Resume not found.', 404);

        $ok = $model->deleteResume($id);
        if (!$ok) return $this->jsonError('Cannot delete the only remaining resume.');

        return $this->jsonSuccess([], 'Resume deleted.');
    }
}
