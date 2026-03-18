<?php
namespace App\Controllers;
use App\Models\ProjectModel;
use App\Models\ThesisPhaseModel;
use App\Models\ThesisIsoModel;

/**
 * ProjectsAdminController — app/Controllers/ProjectsAdminController.php
 */
class ProjectsAdminController extends BaseController
{
    public function index(): string
    {
        $projects = (new ProjectModel())->getAllOrdered();

        // Load phases and ISO scores for ALL projects, keyed by project_id
        $phaseModel = new ThesisPhaseModel();
        $isoModel   = new ThesisIsoModel();

        $projectPhases = [];
        $projectIso    = [];

        foreach ($projects as $p) {
            $pid = (int)$p['id'];
            $projectPhases[$pid] = $phaseModel->getForProject($pid);
            $projectIso[$pid]    = $isoModel->getForProject($pid);
        }

        return view('admin/projects', [
            'projects'      => $projects,
            'projectPhases' => $projectPhases,
            'projectIso'    => $projectIso,
            'adminUsername' => session()->get('admin_username') ?? 'admin',
        ]);
    }
}