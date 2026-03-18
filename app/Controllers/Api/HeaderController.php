<?php
namespace App\Controllers\Api;
use App\Models\HeaderModel;
class HeaderController extends BaseApiController {
    public function update() { $d=$this->getJson(); (new HeaderModel())->updateHeader(['name'=>$d['name']??'','position'=>$d['position']??'','email'=>$d['email']??'','phone'=>$d['phone']??'','location'=>$d['location']??'','linkedin'=>$d['linkedin']??'']); return $this->jsonSuccess([],'Header updated.'); }
}
