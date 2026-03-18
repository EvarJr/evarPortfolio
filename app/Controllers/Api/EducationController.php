<?php
namespace App\Controllers\Api;
use App\Models\EducationModel;
class EducationController extends BaseApiController {
    public function add() { $d=$this->getJson(); $m=new EducationModel(); $id=$m->insert(['degree'=>$d['degree']??'New Degree','school'=>$d['school']??'School','start_month'=>$d['start_month']??'','start_year'=>$d['start_year']??'','end_month'=>$d['end_month']??'','end_year'=>$d['end_year']??'','sort_order'=>$m->nextSortOrder()]); return $this->jsonSuccess(['id'=>$id],'Added.'); }
    public function update(int $id) { $d=$this->getJson(); (new EducationModel())->update($id,['degree'=>$d['degree']??'','school'=>$d['school']??'','start_month'=>$d['start_month']??'','start_year'=>$d['start_year']??'','end_month'=>$d['end_month']??'','end_year'=>$d['end_year']??'']); return $this->jsonSuccess([],'Updated.'); }
    public function delete(int $id) { (new EducationModel())->delete($id); return $this->jsonSuccess([],'Deleted.'); }
}
