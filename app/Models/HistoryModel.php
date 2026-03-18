<?php
namespace App\Models;
use CodeIgniter\Model;
class HistoryModel extends Model {
    protected $table='resume_history'; protected $primaryKey='id'; protected $useTimestamps=false;
    protected $allowedFields=['role','company','start_month','start_year','end_month','end_year','is_current','sort_order'];
    public function getAllWithBullets(): array {
        $entries=$this->orderBy('sort_order','ASC')->findAll(); $bm=new HistoryBulletModel();
        foreach($entries as &$e){ $e['bullets']=$bm->where('history_id',$e['id'])->orderBy('sort_order','ASC')->findAll(); }
        return $entries;
    }
    public function nextSortOrder(): int { return(int)($this->selectMax('sort_order','m')->first()['m']??0)+1; }
}
