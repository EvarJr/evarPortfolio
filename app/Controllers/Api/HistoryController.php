<?php
namespace App\Controllers\Api;
use App\Models\HistoryModel;
class HistoryController extends BaseApiController {
    public function add() { $d=$this->getJson(); $m=new HistoryModel(); $id=$m->insert(['role'=>$d['role']??'New Role','company'=>$d['company']??'Company','start_month'=>$d['start_month']??'','start_year'=>$d['start_year']??'','end_month'=>$d['end_month']??'','end_year'=>$d['end_year']??'','is_current'=>isset($d['is_current'])?(int)$d['is_current']:1,'sort_order'=>$m->nextSortOrder()]); return $this->jsonSuccess(['id'=>$id],'Added.'); }
    public function update(int $id) { $d=$this->getJson(); (new HistoryModel())->update($id,['role'=>$d['role']??'','company'=>$d['company']??'','start_month'=>$d['start_month']??'','start_year'=>$d['start_year']??'','end_month'=>$d['end_month']??'','end_year'=>$d['end_year']??'','is_current'=>isset($d['is_current'])?(int)$d['is_current']:0]); return $this->jsonSuccess([],'Updated.'); }
    public function delete(int $id) { (new HistoryModel())->delete($id); return $this->jsonSuccess([],'Deleted.'); }
}
