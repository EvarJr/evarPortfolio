<?php
namespace App\Models;
use CodeIgniter\Model;
class HeaderModel extends Model {
    protected $table='resume_header'; protected $primaryKey='id'; protected $useTimestamps=false;
    protected $allowedFields=['name','position','email','phone','location','linkedin'];
    public function getHeader(): array { return $this->find(1)??['id'=>1,'name'=>'Your Name','position'=>'Your Position','email'=>'','phone'=>'','location'=>'','linkedin'=>'']; }
    public function updateHeader(array $data): bool { if($this->find(1)) return $this->update(1,$data); $data['id']=1; return(bool)$this->insert($data); }
}
