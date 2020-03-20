<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model{
    protected $table = 'products';
    protected $primaryKey = 'proid';
    protected $connection = 'mysql_pedidos';
    //protected $fillable = ['pro_code', 'pro_shortcode', 'pro_shortdesc', 'ipack', 'min', 'max', 's_units', 's_containers'];
    public $timestamps = false;
}