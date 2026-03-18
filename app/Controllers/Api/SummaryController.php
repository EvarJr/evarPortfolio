<?php
namespace App\Controllers\Api;
use App\Models\SummaryModel;
class SummaryController extends BaseApiController {
    public function update() { $d=$this->getJson(); (new SummaryModel())->updateSummary($d['content']??''); return $this->jsonSuccess([],'Summary updated.'); }
}
