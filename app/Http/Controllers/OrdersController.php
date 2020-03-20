<?php

namespace App\Http\Controllers;

use DateTime;
use PDO;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\SupplyHead;
use App\Product;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\CapabilityProfile;

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
                'code' => $data->pro_code,
                'description' => $data->pro_largedesc,
                'min' => $min,
                'max' => $max,
                'act' => $stock,
                'ipack' => $data->pro_innerpack,
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

    public function generateOrder(Request $request){
        //crear registro en supply_head
        $id_supply_head = DB::connection('mysql_auto_stock')->table('supply_head')->insertGetId([
            'branch_name' => $request->node['name'],
            'branch_alias' => $request->node['alias'],
            'printed' => false,
            'created_at' => new Datetime,
            'updated_at' => new Datetime,
            'status_id' => 1
        ]);
        $type_supply = $request->node['typesupply'];
        if($id_supply_head){
            $products = collect($request->items);
            $stocks = $this->checkStocks($products);
            $items = array();
            
            $productsToOrder = $products->map( function($product, $index) use ($id_supply_head, $type_supply, $items, $stocks){
                $boxes = 0;
                $units = 0;
                if($type_supply == 'containers'){
                    $boxes = $product['req'];
                    $units = $product['req'] * $product['ipack'];
                }else{
                    $units = $product['req'];
                    $boxes = 0;
                    if($product['ipack']!=0){
                        $boxes = $product['req'] / $product['ipack'];
                    }
                }
                //&& $available-$units>=0
                return [
                    '_supply_head' => $id_supply_head,
                    'item' => $product['code'],
                    'current_stock' => $stocks[$index],
                    'ipack' => $product['ipack'],
                    'min' => $product['min'],
                    'max' => $product['max'],
                    's_units' => $units,
                    's_containers' => $boxes,
                ];
            })->filter(function($article){
                return $article['current_stock']>0;
            })->sortBy('location');

            $products2 = [];
            foreach($productsToOrder as $product){
                array_push($products2, $product);
            }
            $res = DB::table('supply_body')->insert($products2);
            
        }
        $order = SupplyHead::find($id_supply_head);
        $order = $order->fresh('items', 'status');
        $order->items = $order->items->map( function( $product){
            $data_pedidos_db = Product::where('pro_code', $product['item'])->first();
            $product->description = $data_pedidos_db['pro_largedesc'];
            $product->location = $data_pedidos_db['pro_location'];
            $product->status = $data_pedidos_db['pro_status'];
            return $product;
        })->filter( function ($product){
            return $product['status'] == 1;
        })->sortBy('location');
        $this->printTicket($order);
        return response()->json(['ticket' => 'imprimiendo']);
    }

    public function checkStocks($products){
        //Se tiene que adaptar el documento en cada bodega
        //$dbName = "C:\\2020_MOCHILA_CED\\Datos\\VPA2020.mdb";
        $dbName = "C:\\Users\\Yusev\\Desktop\\autostock\\ACCESS\\VPA2020.mdb";
        $db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb, *.accdb)};charset=UTF-8; DBQ=$dbName; Uid=; Pwd=;");
        
        $stocks = $products->map( function($product) use ($db){
            $q = $db->prepare("SELECT * FROM F_STO WHERE ARTSTO=?");
            $q->execute([$product['code']]);
            $rows = $q->fetchAll(PDO::FETCH_ASSOC);
            return $rows[0]['ACTSTO'];
        });

        return $stocks;
    }

    public function printTicket($ticket){
        $host = gethostname();
        $ipserver = gethostbyname($host);
        $printername = 'PETUNIO';
        $connector = new WindowsPrintConnector("smb://".$ipserver."/".$printername);
        $printer = new Printer($connector);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(2,1);
        $printer->setEmphasis(true);
        $printer->text("███ ".$ticket['branch_name']."███\r\n");
        $printer->setTextSize(1,1);
        $printer->text("SOLICITUD DE MERCANCIA █AUTOSTOCK█\r\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("------------------------------------------------\r\n");
        $printer->setTextSize(2,2);
        $printer->setReverseColors(true);
        $printer->text($ticket['branch_alias']." (".$ticket['id'].")\r\n");
        $printer->setReverseColors(false);
        $printer->setEmphasis(false);
        $printer->setTextSize(1,1);
        $printer->text("------------------------------------------------\r\n");
        $printer->text("Inicio: ".$ticket['created_at']."\r\n");
        $printer->text("------------------------------------------------\r\n");
        $articles = collect($ticket['items']);

        $articles->map(function($article, $index) use ($printer){
            //$data_pedidos_db = Product::where('pro_code', $article['item'])->first();
            $printer->setTextSize(2,1);
            $printer->text($index+1);
            //$printer->text("▒".$data_pedidos_db['pro_location']);
            $printer->text("▒".$article['location']);
            $printer->text("▒".$article['item']."\r\n");
            //$available = $this->checkAvailableProduct($article['item']);
            //$available = 0;
            $printer -> setTextSize(1,1);
            $printer -> text($article['description']."\r\n");
            $printer->setTextSize(2,1);
            //$printer->text("UF:".$article['s_units']." - UD:".number_format($available,0,',', '')."\r\n");
            $printer->text("UF:".$article['s_units']." - UD:".$article['current_stock']."\r\n\n");
            $printer->setTextSize(1,1);
        });
        $piezas = $articles->reduce( function($total, $article){
            return $total + $article->s_units;
        });
        $printer->setTextSize(1,1);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("------------------------------------------------\r\n");
        $printer->text("Modelos: ".count($articles)." - Piezas: ".$piezas."\r\n");
        $printer->text("------------------------------------------------\r\n");
        $printer->barcode($ticket['id']);
        $printer->setEmphasis(true);
        $printer->text("\r\n"."GRUPO VIZCARRA");
        $printer->feed(2);
        $printer->cut();
        $printer->close();
    }
}
