<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * ThesisPhaseModel — app/Models/ThesisPhaseModel.php
 */
class ThesisPhaseModel extends Model
{
    protected $table         = 'thesis_phases';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['project_id','num','title','content','sort_order'];

    public function getForProject(int $projectId): array
    {
        return $this->where('project_id', $projectId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
}
