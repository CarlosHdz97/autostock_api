<?php

namespace App\Http\Controllers;

use DateTime;
use PDO;
use PDF;
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

    public function getDataStore(){
        $store = [
            'id' => env('STORE_ID'),
            'name' => env('STORE_NAME'),
            'alias' => env('STORE_ALIAS'),
            'type' => 'store',
            'namespace' => env('STORE_ALIAS').'.store',
            'typeSupply' => env('STORE_TYPE_SUPPLY'),
            'celler' => env('CELLER')
        ];
        $data = ["store" => $store, "orders" => $this->getOrders()];
        return response()->json($data);
    }

    public function getDataCeller(){
        $store = [
            'id' => env('STORE_ID'),
            'name' => env('STORE_NAME'),
            'alias' => env('STORE_ALIAS'),
            'type' => 'celler',
            'namespace' => env('STORE_ALIAS').'.celler',
            'celler' => ''
        ];
        $data = ["celler" => $store, "orders" => $this->getAll()];
        return response()->json($data);
    }

    public function getOrders(){
        $date = new DateTime();
        $orders = SupplyHead::where('branch_name', env('STORE_NAME'))->whereDate('created_at', $date->format('Y-m-d'))->get();
        $orders = $orders->fresh('status');
        $orders = $orders->sortByDesc('created_at');
        return $orders->values()->all();
    }

    public function getAll(){
        $date = new DateTime();
        $orders = SupplyHead::whereDate('created_at', $date->format('Y-m-d'))->get();
        //$orders = SupplyHead::all();
        $orders = $orders->fresh('status');
        $orders = $orders->sortByDesc('created_at');
        return $orders->values()->all();
    }

    public function getOrder(Request $request){
        $id_order = $request->id;
        $order = SupplyHead::find($id_order);
        $order = $order->fresh('items', 'status');
        $order->items = $order->items->map( function($product){
            $data_pedidos_db = Product::where('pro_code', $product['item'])->first();
            $product->description = $data_pedidos_db['pro_largedesc'];
            $product->location = $data_pedidos_db['pro_location'];
            $product->status = $data_pedidos_db['pro_status'];
            return $product;
        })->filter( function ($product){
            return $product['status'] == 1;
        })->sortBy('location');

        return response()->json($order);
    }

    public function getOrderHere($id_order){
        $order = SupplyHead::find($id_order);
        $order = $order->fresh('items', 'status');
        $order->items = $order->items->map( function($product){
            $data_pedidos_db = Product::where('pro_code', $product['item'])->first();
            $product->description = $data_pedidos_db['pro_largedesc'];
            $product->location = $data_pedidos_db['pro_location'];
            $product->status = $data_pedidos_db['pro_status'];
            return $product;
        })->filter( function ($product){
            return $product['status'] == 1;
        })->sortBy('location');

        return $order;
    }

    public function printTicket(Request $request){
        $id_order = $request->id;
        $site = $request->site;
        $order = $this->getOrderHere($id_order);
        $host = gethostname();
        $ipserver = gethostbyname($host);
        $printername = env('PRINTER');
        $connector = new WindowsPrintConnector("smb://".$ipserver."/".$printername);
        $printer = new Printer($connector);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(2,1);
        $printer->setEmphasis(true);
        $printer->text("███".$order['branch_name']."███\r\n");
        $printer->setTextSize(1,1);
        $printer->text("SOLICITUD DE MERCANCIA █AUTOSTOCK█\r\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("------------------------------------------------\r\n");
        $printer->setTextSize(2,2);
        $printer->setReverseColors(true);
        $printer->text($order['branch_alias']." (".$order['id'].")\r\n");
        $printer->setReverseColors(false);
        $printer->setEmphasis(false);
        $printer->setTextSize(1,1);
        $printer->text("------------------------------------------------\r\n");
        $printer->text("Inicio: ".$order['created_at']."\r\n");
        $printer->text("------------------------------------------------\r\n");
        $articles = collect($order['items']);

        $articles->map(function($article, $index) use ($printer, $site){
            $printer->setTextSize(2,1);
            $printer->text($index+1);
            if($site=='celler'){
                $printer->text("▒".$article['location']);
            }
            $printer->text("▒".$article['item']."\r\n");
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
        $printer->barcode($order['id']);
        $printer->setEmphasis(true);
        $printer->text("\r\n"."GRUPO VIZCARRA");
        $printer->feed(2);
        $printer->cut();
        $printer->close();
        return response()->json(['result'=> 'ticket impreso']);
    }

    public function getPdf(Request $request){
        $id_order = $request->id;
        $order = $this->getOrderHere($id_order);
        PDF::SetTitle('Pdf autostock');
        PDF::SetAuthor('Carlos Hernández');
        PDF::SetMargins(2, 2, 2);
        PDF::SetAutoPageBreak(TRUE, 0);
        PDF::SetFooterMargin(0);
        PDF::setPrintFooter(false);
        PDF::SetFont('helvetica', '', 15);

        PDF::AddPage();
        PDF::resetColumns();
        PDF::setEqualColumns(3, 57);
        PDF::SetFont('times', '', 9);
        PDF::SetTextColor(50, 50, 50);
        $articles = collect($order['items']);
        $piezas = $articles->reduce( function($total, $article){
            return $total + $article->s_units;
        });
        $header = '
        <div style="text-align: center; font-weight:bold;">'.$order['branch_name'].'</div>
        <div style="text-align: center; font-weight:bold;">SOLICITUD DE MERCANCIA</div>
        <span>-----------------------------------------------------</span>
        <div style="text-align: center; font-weight:bold;">'.$order["branch_alias"].' ('.$order["id"].')'.'</div>
        <span>-----------------------------------------------------</span>
        <div  style="text-align: center; font-weight:bold;">Inicio: '.$order['created_at'].'</div>
        <span>-----------------------------------------------------</span>
        <div></div>
        ';
        $index = 0;
        $body = '';
        foreach ($articles as $article){
            $index = $index+1;
            $content = '<span> '.$index.'#<span style="font-weight:bold;">'.$article["location"].'</span>##<span style="font-weight:bold;">'.$article["item"].'</span></span>
            <span><br/></span>
            <span>'.$article["description"].'</span>
            <span><br/></span>
            <span>UF: <span style="font-weight:bold;">'.$article["s_units"].'</span> - UD: '.$article["current_stock"].'</span>
            <div></div>';
            $body=$body.$content;
        }
        $footer = '
        <span>------------------------------------------------------------</span>
        <span style="font-weight:bold;">Modelos: '.count($articles).'- Piezas:'.$piezas.'</span>
        <span>------------------------------------------------------------</span>';
        PDF::writeHTML($header.$body.$footer, true, false, true, false, 'J');
        PDF::lastPage();
        $nameFile = time().'.pdf';
        PDF::Output(__DIR__.'/../../../files/'.$nameFile, 'F');
        return response()->json(["file"=>$nameFile]);
    }

    public function getProductsToSupply(){
        $products = [];
        $date = new DateTime();
        $items = collect($this->getProductsFromAccess());
        $productos = $items->map(function($item){
            $data = Product::where('pro_code',  $item['ARTSTO'])->first();
            if(!$data){
                return $data;
            }
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
            if($product){
                if($product['req']>0){
                    array_push($product_required, $product);
                }
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
            'bodega' => $request->node['celler'],
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
            $order = $this->getOrderHere($id_supply_head);
            return response()->json($order);
        }else{
            return response()->json(['msg'=>'No se ha podido generar el pedido']);
        }
    }

    public function checkStocks($products){
        //Se tiene que adaptar el documento en cada bodega
        $dbName = env('ACCESS_FILE');
        $db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb, *.accdb)};charset=UTF-8; DBQ=$dbName; Uid=; Pwd=;");
        
        $stocks = $products->map( function($product) use ($db){
            $q = $db->prepare("SELECT * FROM F_STO WHERE ARTSTO=?");
            $q->execute([$product['code']]);
            $rows = $q->fetch(PDO::FETCH_ASSOC);
            if($rows){
                return $rows['ACTSTO'];
            }else{
                return 0;
            }
        });
        return $stocks;
    }

    public function chageStatus($request){
        /* $status_id = $request->status_id; */
        $order_id = $request->id;
        $order = SupplyHead::find($order_id);
        $order->status_id = $order->status_id +1;
        $order->save();
        $order = $order->fresh('status');
        return response()->json($order);
    }
}
