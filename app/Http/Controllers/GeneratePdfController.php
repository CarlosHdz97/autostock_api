<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use PDF;

class GeneratePdfController extends Controller{

    public function generatePdf(Request $request){
        switch($request->type){
            case 'big-star':
            break;
            case 'star-2':
                return $this->pdf_star_2($request->products, $request->isInnerPack);
            break;
            case 'star-3':
                return $this->pdf_star_3($request->products, $request->isInnerPack);
            break;
            case 'star-4':
            break;
            case 'bag':
                return $this->pdf_bag($request->products, $request->isInnerPack);
            break;
            case 'bag-content':
                return $this->pdf_bag_content($request->products, $request->isInnerPack);
            break;
            case 'celler':
            break;
            case 'wood':
            break;
            case 'sphere':
            break;
        }
    }

    /**
     * Funciones base
     */

    public function getStdProducts($products){
        $products = collect($products);
        return $products->filter( function( $product){
            return $product['type']=='std' || $product['type']=='my';
        });
    }

    public function getOffProducts( $products){
        $products = collect($products);
        return $products->filter( function ($product){
            return $product['type']=='off';
        });
    }

    public function pdf_bag_content($products, $isInnerPack){
        $off = $this->getOffProducts($products);
        $std = $this->getStdProducts($products);
        $counter = 0;
        $std->map( function ($product, $key) use ($isInnerPack, $counter){
            for($i=0; $i<$product['number']; $i++){
                $pzHoja = 16;
                if($counter%$pzHoja==0){
                    PDF::AddPage();
                    PDF::SetMargins(0, 0, 0);
                    PDF::SetAutoPageBreak(FALSE, 0);
                    PDF::setCellPaddings(0,0,0,0);
                    PDF::MultiCell($w=100, $h=10, '<span style="font-size:1.5em;">Página'.intval($key/$pzHoja).'</span>', $border=0, $align='left', $fill=0, $ln=0, $x=5, $y=5, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0);
                }
                $footer = '';
                $addSise = 0;
                if($isInnerPack){
                    $footer = ' | '.$product['ipack'];
                    $addSise = .2;
                }
                $number_of_prices = count($product['_prices']);
                $font_size_prices = 2;
                switch($number_of_prices){
                    case 1:
                        $font_size_prices = 2.7+$addSise;
                    break;
                    case 2:
                        $font_size_prices = 2+$addSise;
                    break;
                    case 3:
                        $font_size_prices = 1.6+$addSise;
                    break;
                    case 4:
                        $font_size_prices = 1.4+$addSise;
                    break;
                }
                $tool = '';
                if($product['tool']){
                    $tool = '+'.$product['tool'];
                }
                /* $content = '<div style="text-align: center;">
                                <span style="font-size:2.8em; font-weight: bold;">'.$product['scode'].'</span><span style="font-size:1em"><br/></span>
                                <span style="font-size:1.2em; font-weight: bold;">'.$product['item'].$tool.'</span><span style="font-size:1em"><br/></span>
                                <span style="font-size:'.$font_size_prices.'em; font-weight: bold;">'.$this->customPrices($product['_prices']).'</span>
                                <span style="font-size:1.2em; font-weight: bold;">'.$footer.'</span>
                            </div>'; */
                $content = '<div style="text-align: center;">
                            <span style="font-size:2.8em; font-weight: bold;">'.$product['scode'].'</span><span style="font-size:1em"><br/></span>
                            <span style="font-size:1.2em; font-weight: bold;">'.$product['item'].$tool.'</span><span style="font-size:1em"><br/></span>
                            <span style="font-size:'.$font_size_prices.'em; font-weight: bold;">'.$this->customPrices($product['prices'],0).'</span>
                            <span style="font-size:1.2em; font-weight: bold;">'.$footer.'</span>
                        </div>';
                //$this->setImageBackground(null, $content, $width = 66.6, $height = 41.6, $cols = 3, $rows = 6, $top_space = 5, $sides_space = 0, $position =$key);
                $this->setImageBackground_area(null, $content, $width=44, $height=49, $cols=4, $rows=4, $top_space=-19.8, $sides_space=6.5, $position=$counter, $top_margin=16, $sides_margin=7.5);
                $counter = $counter+1;
            }
        });
        $counter = 0;
        $off->map( function ($product, $key) use ($isInnerPack, $counter){
            for($i=0; $i<$product['number']; $i++){
                $pzHoja = 16;
                if($counter%$pzHoja==0){
                    PDF::AddPage();
                    PDF::SetMargins(0, 0, 0);
                    PDF::SetAutoPageBreak(FALSE, 0);
                    PDF::setCellPaddings(0,0,0,0);
                    PDF::MultiCell($w=100, $h=10, '<span style="font-size:1.5em;">Página'.intval($key/$pzHoja).'</span>', $border=0, $align='left', $fill=0, $ln=0, $x=5, $y=5, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0);
                }
                $footer = '';
                $addSise = 0;
                if($isInnerPack){
                    $footer = ' | '.$product['ipack'];
                    $addSise = .2;
                }
                $number_of_prices = count($product['_prices']);
                $font_size_prices = 2;
                switch($number_of_prices){
                    case 1:
                        $font_size_prices = 2.7+$addSise;
                    break;
                    case 2:
                        $font_size_prices = 2+$addSise;
                    break;
                    case 3:
                        $font_size_prices = 1.6+$addSise;
                    break;
                    case 4:
                        $font_size_prices = 1.4+$addSise;
                    break;
                }
                $tool = '';
                if($product['tool']){
                    $tool = '+'.$product['tool'];
                }
                /* $content = '<div style="text-align: center;">
                                <span style="font-size:2em"><br/></span>
                                <span style="font-size:2.8em; font-weight: bold;">'.$product['scode'].'</span><span style="font-size:1em"><br/></span>
                                <span style="font-size:1.2em; font-weight: bold;">'.$product['item'].$tool.'</span><span style="font-size:1em"><br/></span>
                                <span style="font-size:'.$font_size_prices.'em; font-weight: bold;">'.$this->customPrices($product['_prices']).'</span>
                                <span style="font-size:1.2em; font-weight: bold;">'.$footer.'</span>
                            </div>'; */
                $content = '<div style="text-align: center;">
                            <span style="font-size:2em"><br/></span>
                            <span style="font-size:2.8em; font-weight: bold;">'.$product['scode'].'</span><span style="font-size:1em"><br/></span>
                            <span style="font-size:1.2em; font-weight: bold;">'.$product['item'].$tool.'</span><span style="font-size:1em"><br/></span>
                            <span style="font-size:'.$font_size_prices.'em; font-weight: bold;">'.$this->customPrices($product['prices'],0).'</span>
                            <span style="font-size:1.2em; font-weight: bold;">'.$footer.'</span>
                        </div>';
                //$this->setImageBackground(null, $content, $width = 66.6, $height = 41.6, $cols = 3, $rows = 6, $top_space = 5, $sides_space = 0, $position =$key);
                $this->setImageBackground_area(null, $content, $width=44, $height=49, $cols=4, $rows=4, $top_space=-19.8, $sides_space=6.5, $position=$counter, $top_margin=16, $sides_margin=7.5);
                $counter = $counter+1;
            }
        });
        $nameFile = time().'.pdf';
        PDF::Output(__DIR__.'/../../../files/'.$nameFile, 'F');

        /* return response()->json([
            'pages_off' => ceil(count($std)/8),
            'pages_std' => ceil(count($off)/8),
            'total' => ceil(count($std)/8) + ceil(count($off)/8),
            'file' => $nameFile,
        ]); */
        PDF::Output(__DIR__.'/../../../files/'.$nameFile, 'F');
        return response()->json(["archivo" => $nameFile, "hojas"=>[
            ['msg' => 'Hojas', 'amount' => (count($products)/8)]
        ]]);
    }

    public function pdf_bag($products, $isInnerPack){
        $products = collect($products);
        $products->map( function($product, $key) use ($isInnerPack){
            if($key%6==0){
                PDF::AddPage('L');
                PDF::SetMargins(0, 0, 0);
                PDF::SetAutoPageBreak(FALSE, 0);
                PDF::setCellPaddings(0,0,0,0);
                /* PDF::MultiCell($w=100, $h=10, '<span style="font-size:2em;">Página'.intval($key/6).'</span>', $border=0, $align='left', $fill=0, $ln=0, $x=0, $y=0, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0); */
            }
            $pz = '';
            $addSise = .2;
            if($isInnerPack){
                $pz = ' | '.$product['pro_innerpack'];
                $addSise = 0;
            }
            $number_of_prices = count($product['_prices']);
            $font_size_prices = 2;
            switch($number_of_prices){
                case 1:
                    $font_size_prices = 3.1+$addSise;
                break;
                case 2:
                    $font_size_prices = 2.3+$addSise;
                break;
                case 3:
                    $font_size_prices = 1.8+$addSise;
                break;
                case 4:
                    $font_size_prices = 1.5+$addSise;
                break;
            }

            $content = '<div style="text-align: center; transform: rotate(180deg); -ms-transform: rotate(180deg);">
                            <span style="font-size:3.2em; font-weight: bold;">'.$product['pro_shortcode'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:1.6em; font-weight: bold;">'.$product['pro_code'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:'.$font_size_prices.'em; font-weight: bold;">'.$this->customPrices($product['_prices']).'</span>
                            <span style="font-size:1.3em; font-weight: bold;">'.$product['tool'].$pz.'</span>
                        </div>';
            $this->setImageBackground_area(__DIR__.'./resources/img/STAR12.png', $content, $width=47, $height=53, $cols=3, $rows=2, $top_space=-4, $sides_space= 28.2, $key, $top_margin=31.5, $sides_margin=40);

        });
        $nameFile = time().'.pdf';
        PDF::Output(__DIR__.'/../../../files/'.$nameFile, 'F');

        return response()->json([
            'file' => $nameFile,
        ]);
    }

    public function pdf_star_2($products, $isInnerPack){
        PDF::SetTitle('Pdf estrella x2');
        $off = [];
        $std = [];
        foreach( $products as $product){
            if($product['type']=="off"){
                array_push($off, $product);
            }else{
                array_push($std, $product);
            }
        }
        
        $std = collect($std);
        $off = collect($off);
        $i_std = 0;
        $std->map( function($product, $key) use($isInnerPack, $i_std){
            for($i=0; $product['amount']>$i; $i++){
                $i_std++;
                if(($key+$i_std)%8==0){
                    PDF::AddPage();
                    PDF::MultiCell($w=100, $h=10, '<span style="font-size:2em;">Hojas verdes</span>', $border=0, $align='center', $fill=0, $ln=0, $x=0, $y=0, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0);
                }
                $pz = '';
                if($isInnerPack){
                    $pz = ' | '.$product['pro_innerpack'];
                }
                $number_of_prices = count($product['prices']);
                $font_size_prices = 2;
                switch($number_of_prices){
                    case 1:
                        $font_size_prices = 3.2;
                    break;
                    case 2:
                        $font_size_prices = 2.4;
                    break;
                    case 3:
                        $font_size_prices = 1.9;
                    break;
                    case 4:
                        $font_size_prices = 1.6;
                    break;
                }
    
                $content = '<div style="text-align: center;">
                                <span style="font-size:3.2em; font-weight: bold;">'.$product['pro_shortcode'].'</span><span style="font-size:.1em"><br/></span>
                                <span style="font-size:2em; font-weight: bold;">'.$product['pro_code'].'</span><span style="font-size:.1em"><br/></span>
                                <span style="font-size:'.$font_size_prices.'em; font-weight: bold;">'.$this->customPrices($product['_prices']).'</span>
                                <span style="font-size:1.5em; font-weight: bold;">'.$product['tool'].$pz.'</span>
                            </div>';
                $this->setImageBackground(__DIR__.'./resources/img/STAR12.png', $content, 100, 62.5, 2, 4, 5, 0, $key);
            }
        });
        $off->map( function($product, $key) use($isInnerPack){
            if($key%8==0){
                PDF::AddPage();
                PDF::MultiCell($w=100, $h=10, '<span style="font-size:2em;">Hojas naranjas</span>', $border=0, $align='center', $fill=0, $ln=0, $x=0, $y=0, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0);
            }
            $pz = '';
            if($isInnerPack){
                $pz = ' | '.$product['pro_innerpack'];
            }
            $content = '<span style="text-align: center;">
                            <span style="font-size:3.2em; font-weight: bold;">'.$product['pro_shortcode'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:2.2em; font-weight: bold;">'.$product['pro_code'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:2.9em; font-weight: bold;">'.$this->customPrices($product['_prices']).'</span>
                            <span style="font-size:1.5em; font-weight: bold;">'.$product['tool'].$pz.'</span>
                        </span>';
            $this->setImageBackground(__DIR__.'./resources/img/STAR12.png', $content, 100, 62.5, 2, 4, 0, 0, $key);
        });

        $nameFile = time().'.pdf';
        PDF::Output(__DIR__.'/../../../files/'.$nameFile, 'F');

        return response()->json([
            'pages_off' => ceil(count($std)/8),
            'pages_std' => ceil(count($off)/8),
            'total' => ceil(count($std)/8) + ceil(count($off)/8),
            'file' => $nameFile,
        ]);
    }

    public function pdf_star_3($products, $isInnerPack){
        PDF::SetTitle('Pdf estrella x3');
        $off = [];
        $std = [];
        foreach( $products as $product){
            if($product['type']=="off"){
                array_push($off, $product);
            }else{
                array_push($std, $product);
            }
        }
        
        $std = collect($std);
        $off = collect($off);

        $std->map( function($product, $key) use($isInnerPack){
            if($key%18==0){
                PDF::AddPage();
                PDF::MultiCell($w=100, $h=10, '<span style="font-size:2em;">Hojas verdes</span>', $border=0, $align='center', $fill=0, $ln=0, $x=0, $y=0, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0);
            }
            $pz = '';
            if($isInnerPack){
                $pz = ' | '.$product['pro_innerpack'];
            }
            $number_of_prices = count($product['prices']);
            $font_size_prices = 2;
            switch($number_of_prices){
                case 1:
                    $font_size_prices = 2.3;
                break;
                case 2:
                    $font_size_prices = 1.8;
                break;
                case 3:
                    $font_size_prices = 1.2;
                break;
                case 4:
                    $font_size_prices = 1.1;
                break;
            }

            $content = '<div style="text-align: center;">
                            <span style="font-size:2.1em; font-weight: bold;">'.$product['pro_shortcode'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:1.5em; font-weight: bold;">'.$product['pro_code'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:'.$font_size_prices.'em; font-weight: bold;">'.$this->customPrices($product['_prices']).'</span>
                            <span style="font-size:1.2em; font-weight: bold;">'.$product['tool'].$pz.'</span>
                        </div>';
            $this->setImageBackground(__DIR__.'./resources/img/STAR12.png', $content, 66.6, 41.6, 3, 6, 5, 0, $key);
        });
        $off->map( function($product, $key) use($isInnerPack){
            if($key%18==0){
                PDF::AddPage();
                PDF::MultiCell($w=100, $h=10, '<span style="font-size:2em;">Hojas naranjas</span>', $border=0, $align='center', $fill=0, $ln=0, $x=0, $y=0, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0);
            }
            $pz = '';
            if($isInnerPack){
                $pz = ' | '.$product['pro_innerpack'];
            }
            $content = '<span style="text-align: center;">
                            <span style="font-size:2.1em; font-weight: bold;">'.$product['pro_shortcode'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:1.5em; font-weight: bold;">'.$product['pro_code'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:2.3em; font-weight: bold;">'.$this->customPrices($product['_prices']).'</span>
                            <span style="font-size:1.2em; font-weight: bold;">'.$product['tool'].$pz.'</span>
                        </span>';
                        $this->setImageBackground(__DIR__.'./resources/img/STAR12.png', $content, 66.6, 41.6, 3, 6, 5, 0, $key);
        });

        /* return PDF::Output('ccc', 'D'); */
        $nameFile = time().'.pdf';
        PDF::Output(__DIR__.'/../../../files/'.$nameFile, 'F');

        return response()->json([
            'pages_off' => ceil(count($std)/18),
            'pages_std' => ceil(count($off)/18),
            'total' => ceil(count($std)/18) + ceil(count($off)/18),
            'file' => $nameFile,
        ]);
    }

    public function customPrices($prices){
        $prices = collect($prices);
    if(count($prices)>1){
            $text = $prices->reduce( function( $result, $price){
                $row = '<span>'.$price['desc'].' <span style="font-size:1.2em;"> $'.$price['price'].'</span></span><span style="font-size:1em;"><br></span>';
                return $result.$row;
            });
            return $text;
        }
        $text = $prices->reduce( function( $result, $price){
            $row = '<span style="font-size:.5em;">¡¡¡'.$price['desc'].'!!!</span><span style="font-size:.1px;"><br></span><span style="font-size:1.15em;"> $'.$price['price'].'</span><br/>';
            return $result.$row;
        });
        return $text;
    }

    public function setImageBackground($image, $content, $width, $height, $cols, $rows, $top_space, $sides_space, $position){
        $bucle = floor(($position)/($rows*$cols));
        $position = $position-($bucle*$cols*$rows);
        $x = 5+(($position%$cols)*($width))+$sides_space;
        $y = 10+(intval(($position/$cols))*$height)+$top_space;
        if($image){
            $star = PDF::Image($image, 0, 0, 0, '', '', '', '', false, 700, '', true);
            PDF::Image($image, $x, $y, $width, $height, '', '', '', false, 300, '', false, $star);
        }
        PDF::MultiCell($width, $height, $content, $border=1, $align="center", $fill=0, $ln=0, $x, $y+2, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0);
    }

    public function setImageBackground_area($image, $content, $width, $height, $cols, $rows, $top_space, $sides_space, $position, $top_margin, $sides_margin){
        $x_store = 0;
        $y_store = 6;
        $bucle = floor(($position)/($rows*$cols));
        $position = $position-($bucle*$cols*$rows);
        $x = (($position%$cols)*($width))+$sides_space+($sides_margin*(0+($position%$cols)))+$x_store;

        if(floor($position/$cols)==0){
            //$y = 2+(intval(($position/$cols))*$height)+$top_space+($top_margin*(1+(floor($position/$cols))))+$y_store+5;
            $y = 3+(intval(($position/$cols))*$height)+$top_space+($top_margin*(2+(floor($position/$cols))));
        }else{
            $y = 3+(intval(($position/$cols))*$height)+$top_space+($top_margin*(2+(floor($position/$cols))));
        }
        PDF::MultiCell($width, $height, $content, $border=0, $align="center", $fill=0, $ln=0, $x, $y+1, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0);
    }
}