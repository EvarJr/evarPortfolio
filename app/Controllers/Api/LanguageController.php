<?php
namespace App\Controllers\Api;
use App\Models\LanguageModel;
class LanguageController extends BaseApiController {
    public function add() { $d=$this->getJson(); $m=new LanguageModel(); $id=$m->insert(['language'=>$d['language']??'New Language','mastery'=>(int)($d['mastery']??60),'sort_order'=>$m->nextSortOrder()]); return $this->jsonSuccess(['id'=>$id],'Added.'); }
    public function update(int $id) { $d=$this->getJson(); (new LanguageModel())->update($id,['language'=>$d['language']??'','mastery'=>(int)($d['mastery']??60)]); return $this->jsonSuccess([],'Updated.'); }
    public function delete(int $id) { (new LanguageModel())->delete($id); return $this->jsonSuccess([],'Deleted.'); }
}
