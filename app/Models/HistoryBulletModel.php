<?php
namespace App\Models;
use CodeIgniter\Model;
class HistoryBulletModel extends Model {
    protected $table='resume_history_bullets'; protected $primaryKey='id'; protected $useTimestamps=false;
    protected $allowedFields=['history_id','content','sort_order'];
    public function nextSortOrder(int $hid): int { return(int)($this->where('history_id',$hid)->selectMax('sort_order','m')->first()['m']??0)+1; }
}
