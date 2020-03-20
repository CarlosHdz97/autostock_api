<?php

namespace App\Http\Controllers;

use DateTime;
use PDO;

use App\SupplyHead;
use App\Product;

class OrdersController extends Controller{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        date_default_timezone_set('America/Mexico_City');
    }

    public function storeData(){
        $store = [
            'id' => env('STORE_ID'),
            'name' => env('STORE_NAME'),
            'alias' => env('STORE_ALIAS'),
            'typeSupply' => env('STORE_TYPE_SUPPLY'),
            'celler' => env('CELLER')
        ];
        $data = ["store" => $store, "orders" => $this->getOrders()];
        return response()->json($data);
    }

    public function getOrders(){
        $date = new DateTime();
        $orders = SupplyHead::where('branch_name', env('STORE_NAME'))->whereDate('created_at', $date->format('Y-m-d'))->get();
        $orders = $orders->fresh('items', 'status');
        return $orders;
    }

    public function getProductsToSupply(){
        $products = [];
        $date = new DateTime();
        $items = collect($this->getProductsFromAccess());
        $productos = $items->map(function($item){
            $data = Product::where('pro_code',  $item['ARTSTO'])->first();
            $min = intval($item['MINSTO']);
            $max = intval($item['MAXSTO']);
            $stock = intval($item['ACTSTO']);
            return [
                //'code' => $data->pro_code,
                //'description' => $data->pro_largedesc,
                'min' => $min,
                'max' => $max,
                'act' => $stock,
                //'ipack' => $data->pro_innerpack,
                'req' => $this->getSupply($min, $max, $stock, 5),
            ];
        });
        $product_required = [];
        foreach($productos as $product){
            if($product['req']>0){
                array_push($product_required, $product);
            }
        }
        
        return response()->json([
            'node' => [
                'name'=> env('STORE_NAME'),
                'alias'=> env('STORE_ALIAS'),
                'typesupply'=> env('STORE_TYPE_SUPPLY'),
                'emmited' => $date->format('Y-m-d h:i')
            ],
            'items' => $product_required,
        ]);
    }
    
    public function getProductsFromAccess(){
        $dbName = env('ACCESS_FILE');
        try {
            $db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb, *.accdb)};charset=UTF-8; DBQ=$dbName; Uid=; Pwd=;");
            $query = "SELECT * FROM F_STO WHERE MINSTO>0 AND MAXSTO>0";
            $q = $db->prepare($query);
            $q->execute(null);

            $rows = $q->fetchAll(PDO::FETCH_ASSOC);
            return $rows;

        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    public function getSupply($min, $max, $stock, $innerPack){
        $res = $max - $stock;
        switch(env('STORE_TYPE_SUPPLY')){
            case 'containers':
                $cajas = $res/$innerPack;
                return intval($cajas);
            break;
            case 'units':
                if($min>$stock){
                    if(($min-$stock)<=5){
                        return 0;
                    }else{
                        return $res;
                    }
                }
                else{
                    if($res>0){
                        return $res;
                    }
                }
            break;
        }
    }
}
