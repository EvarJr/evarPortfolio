<?php
namespace App\Models;
use CodeIgniter\Model;

/**
 * ThesisIsoModel — app/Models/ThesisIsoModel.php
 */
class ThesisIsoModel extends Model
{
    protected $table         = 'thesis_iso_scores';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['project_id','label','score','sort_order'];

    public function getForProject(int $projectId): array
    {
        return $this->where('project_id', $projectId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
}
