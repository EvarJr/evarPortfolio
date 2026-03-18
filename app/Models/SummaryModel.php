<?php
namespace App\Models;
use CodeIgniter\Model;
class SummaryModel extends Model {
    protected $table='resume_summary'; protected $primaryKey='id'; protected $useTimestamps=false;
    protected $allowedFields=['content'];
    public function getSummary(): array { return $this->first()??['id'=>null,'content'=>'']; }
    public function updateSummary(string $content): bool { $r=$this->getSummary(); if($r['id']) return $this->update($r['id'],['content'=>$content]); return(bool)$this->insert(['content'=>$content]); }
}
