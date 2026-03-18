<?php
namespace App\Controllers\Api;
use App\Models\EducationBulletModel;
class EducationBulletController extends BaseApiController {
    public function add() { $d=$this->getJson(); $eid=(int)($d['education_id']??0); $m=new EducationBulletModel(); $id=$m->insert(['education_id'=>$eid,'content'=>$d['content']??'New detail','sort_order'=>$m->nextSortOrder($eid)]); return $this->jsonSuccess(['id'=>$id],'Added.'); }
    public function update(int $id) { $d=$this->getJson(); (new EducationBulletModel())->update($id,['content'=>$d['content']??'']); return $this->jsonSuccess([],'Updated.'); }
    public function delete(int $id) { (new EducationBulletModel())->delete($id); return $this->jsonSuccess([],'Deleted.'); }
}
