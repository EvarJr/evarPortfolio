<?php
namespace App\Controllers\Api;
use App\Models\HistoryBulletModel;
class HistoryBulletController extends BaseApiController {
    public function add() { $d=$this->getJson(); $hid=(int)($d['history_id']??0); $m=new HistoryBulletModel(); $id=$m->insert(['history_id'=>$hid,'content'=>$d['content']??'New bullet','sort_order'=>$m->nextSortOrder($hid)]); return $this->jsonSuccess(['id'=>$id],'Added.'); }
    public function update(int $id) { $d=$this->getJson(); (new HistoryBulletModel())->update($id,['content'=>$d['content']??'']); return $this->jsonSuccess([],'Updated.'); }
    public function delete(int $id) { (new HistoryBulletModel())->delete($id); return $this->jsonSuccess([],'Deleted.'); }
}
