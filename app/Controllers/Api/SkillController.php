<?php
namespace App\Controllers\Api;
use App\Models\PersonalSkillModel;
class SkillController extends BaseApiController {
    public function add() { $d=$this->getJson(); $m=new PersonalSkillModel(); $id=$m->insert(['content'=>$d['content']??'New skill','sort_order'=>$m->nextSortOrder()]); return $this->jsonSuccess(['id'=>$id],'Added.'); }
    public function update(int $id) { $d=$this->getJson(); (new PersonalSkillModel())->update($id,['content'=>$d['content']??'']); return $this->jsonSuccess([],'Updated.'); }
    public function delete(int $id) { (new PersonalSkillModel())->delete($id); return $this->jsonSuccess([],'Deleted.'); }
}
