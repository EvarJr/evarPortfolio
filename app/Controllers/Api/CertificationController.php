<?php
namespace App\Controllers\Api;
use App\Models\CertificationModel;
class CertificationController extends BaseApiController {
    public function add() { $d=$this->getJson(); $m=new CertificationModel(); $id=$m->insert(['name'=>$d['name']??'New Cert','year'=>$d['year']??date('Y'),'sort_order'=>$m->nextSortOrder()]); return $this->jsonSuccess(['id'=>$id],'Added.'); }
    public function update(int $id) { $d=$this->getJson(); (new CertificationModel())->update($id,['name'=>$d['name']??'','year'=>$d['year']??'']); return $this->jsonSuccess([],'Updated.'); }
    public function delete(int $id) { (new CertificationModel())->delete($id); return $this->jsonSuccess([],'Deleted.'); }
}
