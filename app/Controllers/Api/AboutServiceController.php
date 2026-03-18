<?php
namespace App\Controllers\Api;
use App\Models\AboutServiceModel;
class AboutServiceController extends BaseApiController {
    public function add() { $d=$this->getJson(); $m=new AboutServiceModel(); $id=$m->insert(['icon'=>$d['icon']??'fas fa-star','title'=>$d['title']??'New Service','description'=>$d['description']??'','sort_order'=>$m->nextSortOrder()]); return $this->jsonSuccess(['id'=>$id],'Service added.'); }
    public function update(int $id) { $d=$this->getJson(); (new AboutServiceModel())->update($id,['icon'=>$d['icon']??'','title'=>$d['title']??'','description'=>$d['description']??'']); return $this->jsonSuccess([],'Updated.'); }
    public function delete(int $id) { (new AboutServiceModel())->delete($id); return $this->jsonSuccess([],'Deleted.'); }
}
