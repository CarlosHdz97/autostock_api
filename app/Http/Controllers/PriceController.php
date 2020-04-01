<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceController extends Controller{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        //
    }

    public function get(){
        $priceList = DB::connection('mysql_pedidos')->table('pricelists')->get();
        return response()->json($priceList);
    }

    public function getProduct(Request $request){
        $code = explode('+',$request->code);
        $product = DB::connection('mysql_pedidos')->table('products')->where('pro_code', $code[0])->first();
        if(!$product){
            $product = DB::connection('mysql_pedidos')->table('products')->where('pro_shortcode', $code[0])->first();
        }
        if($product){
            $prices = DB::connection('mysql_pedidos')->table('product_prices')->where('pp_item', $product->pro_code)->get();
            $product->type = $this->getType($prices);
            $prices_required = $request->prices;
            if($product->type=='off'){
                $prices_required = [0];
            }
            $product->prices = $this->customPrices($prices, $prices_required, $request->orderBy);
            $product->tool_price = 0;
            $product->tool = '';
            if(count($code)>1){
                $extension = DB::connection('mysql_pedidos')->table('products')->where('pro_code', $code[1])->orWhere('pro_shortcode', $code[1])->first();
                $extension_price = DB::connection('mysql_pedidos')->table('product_prices')->where([['pp_item', $extension->pro_code],['pp_pricelist', 1]])->first();
                $product->tool_price = $extension_price->pp_price;
                $product->tool = $extension->pro_code;
            }
            return response()->json($product);
        }
        return response()->json(100);  
    }

    public function customPrices($prices, $prices_required, $orderBy){
        $_prices = collect($prices);
        if($orderBy == 'Desc'){
            return $_prices->filter(function( $price) use ($prices_required){
                $price_valid = false;
                foreach($prices_required as $required){
                    if($required == $price->pp_pricelist){
                        $price_valid = true;
                    }
                }
                return $price_valid;
            })->sortByDesc('pp_pricelist');
        }
        return $_prices->filter(function( $price) use ($prices_required){
            $price_valid = false;
            foreach($prices_required as $required){
                if($required == $price->pp_pricelist){
                    $price_valid = true;
                }
            }
            return $price_valid;
        })->sortBy('pp_pricelist');
    }

    public function getType($prices){
        if($prices[0]->pp_price == $prices[1]->pp_price && $prices[2]->pp_price == $prices[1]->pp_price){
            return 'off';
        }
        return 'std';
    }
}