<?php
namespace App\Models;
use CodeIgniter\Model;
class EducationBulletModel extends Model {
    protected $table='resume_education_bullets'; protected $primaryKey='id'; protected $allowedFields=['education_id','content','sort_order']; protected $useTimestamps=false;
    public function nextSortOrder(int $eid): int { return(int)($this->where('education_id',$eid)->selectMax('sort_order','m')->first()['m']??0)+1; }
}
