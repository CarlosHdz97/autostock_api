<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model{
    protected $table = 'pricelists';
    protected $primaryKey = 'lp_id';
    protected $fillable = ['lp_name', 'lp_desc'];

    public function products(){
        return $this->belongsToMany('App\Product', 'product_prices', 'pp_pricelist', 'pp_item')->withPivot('pp_price');
    }
}