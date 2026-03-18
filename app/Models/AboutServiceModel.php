<?php
namespace App\Models;
use CodeIgniter\Model;
class AboutServiceModel extends Model {
    protected $table='resume_about_services'; protected $primaryKey='id'; protected $allowedFields=['icon','title','description','sort_order']; protected $useTimestamps=false;
    public function getAllOrdered(): array { return $this->orderBy('sort_order','ASC')->findAll(); }
    public function nextSortOrder(): int { return(int)($this->selectMax('sort_order','m')->first()['m']??0)+1; }
}
