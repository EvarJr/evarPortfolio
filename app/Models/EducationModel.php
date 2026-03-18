<?php
namespace App\Models;
use CodeIgniter\Model;
class EducationModel extends Model {
    protected $table='resume_education'; protected $primaryKey='id'; protected $allowedFields=['degree','school','start_month','start_year','end_month','end_year','sort_order']; protected $useTimestamps=false;
    public function getAllWithBullets(): array {
        $entries=$this->orderBy('sort_order','ASC')->findAll(); $bm=new EducationBulletModel();
        foreach($entries as &$e){ $e['bullets']=$bm->where('education_id',$e['id'])->orderBy('sort_order','ASC')->findAll(); }
        return $entries;
    }
    public function nextSortOrder(): int { return(int)($this->selectMax('sort_order','m')->first()['m']??0)+1; }
}
