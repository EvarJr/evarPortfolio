<?php
namespace App\Models;
use CodeIgniter\Model;
class CertificationModel extends Model {
    protected $table='resume_certifications'; protected $primaryKey='id'; protected $allowedFields=['name','year','sort_order']; protected $useTimestamps=false;
    public function getAllOrdered(): array { return $this->orderBy('sort_order','ASC')->findAll(); }
    public function nextSortOrder(): int { return(int)($this->selectMax('sort_order','m')->first()['m']??0)+1; }
}
