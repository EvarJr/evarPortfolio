<?php
namespace App\Controllers\Api;
use App\Models\TechStackModel;
class TechController extends BaseApiController {
    public function add() { $d=$this->getJson(); $m=new TechStackModel(); $id=$m->insert(['content'=>$d['content']??'New tech','sort_order'=>$m->nextSortOrder()]); return $this->jsonSuccess(['id'=>$id],'Added.'); }
    public function update(int $id) { $d=$this->getJson(); (new TechStackModel())->update($id,['content'=>$d['content']??'']); return $this->jsonSuccess([],'Updated.'); }
    public function delete(int $id) { (new TechStackModel())->delete($id); return $this->jsonSuccess([],'Deleted.'); }
}
