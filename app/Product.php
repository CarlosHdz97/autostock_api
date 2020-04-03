<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model{
    protected $table = 'products';
    protected $primaryKey = 'pro_code';
    protected $connection = 'mysql_pedidos';
    //protected $fillable = ['pro_code', 'pro_shortcode', 'pro_shortdesc', 'ipack', 'min', 'max', 's_units', 's_containers'];
    public $timestamps = false;
    public $incrementing = false;
    
    public function prices(){
        return $this->belongsToMany('App\Price', 'product_prices', 'pp_item', 'pp_pricelist')->withPivot('pp_price');
    }
}