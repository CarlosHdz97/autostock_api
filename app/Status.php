<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model{
    protected $table = 'status';
    protected $connection = 'mysql_auto_stock';
    public $timestamps = false;

    public function orders(){
        return $this->hasMany('App\SupplyHead', 'status_id', 'id');
    }
}