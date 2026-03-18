<?php
namespace App\Controllers\Api;
use App\Models\AboutTestimonialModel;
class AboutTestimonialController extends BaseApiController {
    public function add() { $d=$this->getJson(); $m=new AboutTestimonialModel(); $id=$m->insert(['author'=>$d['author']??'New Person','role'=>$d['role']??'Role','quote'=>$d['quote']??'','sort_order'=>$m->nextSortOrder()]); return $this->jsonSuccess(['id'=>$id],'Added.'); }
    public function update(int $id) { $d=$this->getJson(); (new AboutTestimonialModel())->update($id,['author'=>$d['author']??'','role'=>$d['role']??'','quote'=>$d['quote']??'']); return $this->jsonSuccess([],'Updated.'); }
    public function delete(int $id) { (new AboutTestimonialModel())->delete($id); return $this->jsonSuccess([],'Deleted.'); }
}
