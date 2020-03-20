<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplyHead extends Model{
    protected $table = 'supply_head';
    protected $connection = 'mysql_auto_stock';
    protected $fillable = ['branch_name', 'branch_alias', 'printed'];
    public $timestamps = false;
    
    public function items(){
        return $this->hasMany('App\SupplyBody', '_supply_head', 'id');
    }

    public function status(){
        return $this->belongsTo('App\Status', 'status_id');
    }
}