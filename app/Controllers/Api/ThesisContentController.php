<?php
namespace App\Controllers\Api;
use App\Models\ThesisPhaseModel;
use App\Models\ThesisIsoModel;

/**
 * ThesisContentController — app/Controllers/Api/ThesisContentController.php
 * Handles CRUD for thesis phases and ISO scores
 */
class ThesisContentController extends BaseApiController
{
    // ── PHASES ────────────────────────────────────────────

    public function addPhase(): \CodeIgniter\HTTP\ResponseInterface
    {
        $d = $this->getJson();
        $m = new ThesisPhaseModel();
        $max = $m->where('project_id', (int)($d['project_id'] ?? 0))
                  ->selectMax('sort_order', 'mx')->first();
        $id = $m->insert([
            'project_id' => (int)($d['project_id'] ?? 0),
            'num'        => $d['num']     ?? '01',
            'title'      => $d['title']   ?? 'New Phase',
            'content'    => $d['content'] ?? '',
            'sort_order' => (int)($max['mx'] ?? 0) + 1,
        ]);
        return $this->jsonSuccess(['id' => $id], 'Phase added.');
    }

    public function updatePhase(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $d = $this->getJson();
        $m = new ThesisPhaseModel();
        if (!$m->find($id)) return $this->jsonError('Not found.', 404);
        $m->update($id, [
            'num'     => $d['num']     ?? '',
            'title'   => $d['title']   ?? '',
            'content' => $d['content'] ?? '',
        ]);
        return $this->jsonSuccess([], 'Phase updated.');
    }

    public function deletePhase(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        (new ThesisPhaseModel())->delete($id);
        return $this->jsonSuccess([], 'Phase deleted.');
    }

    public function reorderPhases(): \CodeIgniter\HTTP\ResponseInterface
    {
        $d = $this->getJson();
        $m = new ThesisPhaseModel();
        foreach ((array)($d['order'] ?? []) as $i => $id) {
            $m->update((int)$id, ['sort_order' => $i + 1]);
        }
        return $this->jsonSuccess([], 'Order saved.');
    }

    // ── GET ENDPOINTS (for dynamic loading) ─────────────

    public function getPhases(int $projectId): \CodeIgniter\HTTP\ResponseInterface
    {
        $phases = (new ThesisPhaseModel())->getForProject($projectId);
        return $this->jsonSuccess(['phases' => $phases]);
    }

    public function getIso(int $projectId): \CodeIgniter\HTTP\ResponseInterface
    {
        $scores = (new ThesisIsoModel())->getForProject($projectId);
        return $this->jsonSuccess(['scores' => $scores]);
    }

    // ── ISO SCORES ────────────────────────────────────────

    public function updateIso(): \CodeIgniter\HTTP\ResponseInterface
    {
        $d = $this->getJson();
        $m = new ThesisIsoModel();
        $projectId = (int)($d['project_id'] ?? 0);
        $scores    = (array)($d['scores'] ?? []);

        // Delete existing and re-insert
        $m->where('project_id', $projectId)->delete();
        foreach ($scores as $i => $s) {
            $m->insert([
                'project_id' => $projectId,
                'label'      => trim($s['label'] ?? ''),
                'score'      => max(0, min(100, (int)($s['score'] ?? 0))),
                'sort_order' => $i + 1,
            ]);
        }
        return $this->jsonSuccess([], 'ISO scores saved.');
    }
}