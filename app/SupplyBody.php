<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplyBody extends Model{
    protected $table = 'supply_body';
    protected $connection = 'mysql_auto_stock';
    protected $fillable = ['_supply_head', 'item', 'current_stock', 'ipack', 'min', 'max', 's_units', 's_containers'];
    public $timestamps = false;

    public function order(){
        return $this->belongsTo('App\SupplyHead', '_supply_head');
    }
}