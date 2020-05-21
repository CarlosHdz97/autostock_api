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
            if(count($product->prices)<1){
                return response()->json(405);
            }
            $product->type = $this->getType($product->prices);
            $product->amount = 1;
            $prices_required = $request->prices;
            if($product->type=='off'){
                $prices_required = [1];
            }
            $product->tool_price = 0;
            $product->tool = '';
            if(count($code)>1){

                /* $extension = DB::connection('mysql_pedidos')->table('products')->where('pro_code', $code[1])->orWhere('pro_shortcode', $code[1])->first();
                $extension_price = DB::connection('mysql_pedidos')->table('product_prices')->where([['pp_item', $extension->pro_code],['pp_pricelist', 1]])->first(); */
                if($code[1]=='C'){
                    $extension = DB::connection('mysql_pedidos')->table('products')->where('pro_code', 'L-550-N')->orWhere('pro_shortcode', 'L-550')->first();
                    $extension_price = DB::connection('mysql_pedidos')->table('product_prices')->where([['pp_item', $extension->pro_code],['pp_pricelist', 1]])->first();
                    $extension_price = $extension_price->pp_price;
                }elseif($code[1]=='CC'){
                    $extension = DB::connection('mysql_pedidos')->table('products')->where('pro_code', 'L-550-C')->orWhere('pro_shortcode', 'L-550-C')->first();
                    $extension_price = DB::connection('mysql_pedidos')->table('product_prices')->where([['pp_item', $extension->pro_code],['pp_pricelist', 1]])->first();
                    $extension_price = $extension_price->pp_price;
                }else{
                    $extension_price = 0;
                }
                $extension = $code[1];
            }else{
                $extension_price = 0;
                $extension = '';
            }
            return response()->json([
                'code'=> $product->pro_code,
                'scode'=> $product->pro_shortcode,
                'description'=> $product->pro_largedesc,
                'tool'=> $extension,
                'ipack'=> $product->pro_innerpack,
                'tool_price'=> $extension_price,
                'type'=> $product->type,
                'prices'=> $this->customPrices($product->prices, $prices_required, $request->orderBy, $product->type),
                'number' => 1
            ]);
        }
        return response()->json(404);
    }

    public function customPrices($prices, $prices_required, $orderBy, $type){
        $_prices = collect($prices)->map( function($price) use($type){
            if($type == 'off'){
                return [
                    'id' => $price->lp_id,
                    'name' => 'OFERTA',
                    'desc' => 'OFERTA',
                    'price' => $price->pivot->pp_price
                ];
            }
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
            $precios = $_prices->sortBy('id');
        }else{
            $precios = $_prices->sortByDesc('id');
        }

        return $precios->values()->all();
    }

    public function getType($prices){
        if($prices[0]->pivot->pp_price == $prices[1]->pivot->pp_price && $prices[2]->pivot->pp_price == $prices[1]->pivot->pp_price){
            return 'off';
        }
        return 'std';
    }
}