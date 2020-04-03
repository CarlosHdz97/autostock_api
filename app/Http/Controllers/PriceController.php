<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Product;

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
        $product = Product::where('pro_code', $code[0])->first();
        if(!$product){
            $product = Product::where('pro_shortcode', $code[0])->first();
        }
        if($product){
            $product = $product->fresh('prices');
            $product->type = $this->getType($product->prices);
            $product->amount = 1;
            $prices_required = $request->prices;
            if($product->type=='off'){
                $prices_required = [1];
            }
            $product->_prices = $this->customPrices($product->prices, $prices_required, $request->orderBy);
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
        $_prices = collect($prices)->map( function($price){
            return [
                'id' => $price->lp_id,
                'name' => $price->lp_name,
                'desc' => $price->lp_desc,
                'price' => $price->pivot->pp_price
            ];
        })->filter(function( $price) use ($prices_required){
            $price_valid = false;
            foreach($prices_required as $required){
                if($required === $price['id']){
                    $price_valid = true;
                }
            }
            return $price_valid;
        });
        
        if($orderBy == 'Desc'){
            return $_prices->sortByDesc('pp_pricelist');
        }
        return $_prices->sortBy('pp_pricelist');
    }

    public function getType($prices){
        if($prices[0]->pivot->pp_price == $prices[1]->pivot->pp_price && $prices[2]->pivot->pp_price == $prices[1]->pivot->pp_price){
            return 'off';
        }
        return 'std';
    }
}