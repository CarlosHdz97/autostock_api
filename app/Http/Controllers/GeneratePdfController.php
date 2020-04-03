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
            case 'bag':
                return $this->pdf_bag($request->products, $request->isInnerPack);
            case 'star-4':
            break;
            case 'cellar':
            break;
            case 'wood':
            break;
            case 'sphere':
            break;
        }
    }

    public function pdf_bag($products, $isInnerPack){
        $products = collect($products);
        $products->map( function($product, $key) use ($isInnerPack){
            if($key%6==0){
                PDF::AddPage('L');
                PDF::SetMargins(0, 0, 0);
                PDF::SetAutoPageBreak(TRUE, 0);
                PDF::setCellPaddings(0,0,0,0);
                /* PDF::MultiCell($w=100, $h=10, '<span style="font-size:2em;">Página'.intval($key/6).'</span>', $border=0, $align='left', $fill=0, $ln=0, $x=0, $y=0, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0); */
            }
            $pz = '';
            if($isInnerPack){
                $pz = ' | '.$product['ipack'];
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

            $content = '<div style="text-align: center; transform: rotate(180deg); -ms-transform: rotate(180deg);">
                            <span style="font-size:3.2em; font-weight: bold;">'.$product['scode'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:2em; font-weight: bold;">'.$product['item'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:'.$font_size_prices.'em; font-weight: bold;">'.$this->customPrices($product['prices']).'</span>
                            <span style="font-size:1.5em; font-weight: bold;">'.$product['tool'].$pz.'</span>
                        </div>';
            $this->setImageBackground_area(__DIR__.'./resources/img/STAR12.png', $content, $width=47, $height=53, $cols=3, $rows=2, $top_space=0, $sides_space= 26, $key, $top_margin=30, $sides_margin=52);
        });
        $nameFile = time().'.pdf';
        PDF::Output(__DIR__.'/../../../files/'.$nameFile, 'F');

        return response()->json([
            'file' => $nameFile,
        ]);
    }

    public function test(){
        PDF::SetTitle('Hello World');
        PDF::AddPage('L');
        PDF::StartTransform();
        $angle= 180;
        $px= 148;
        $py= 104.8;
        PDF::Rotate($angle, $px, $py);
        PDF::SetMargins(0, 0, 0);
        PDF::SetAutoPageBreak(TRUE, 0);
        PDF::setCellPaddings(0,0,0,0);

        for ($xx=0; $xx<=280; $xx=$xx+10){
            for($yy=0; $yy<=190; $yy=$yy+10){
                if($yy==0 || $yy==190){
                    PDF::MultiCell(10, 10, $xx, $border=1, $align='center', $fill=0, $ln=0, $xx, $yy, $reseth=true, $stretch=0, $ishtml=false, $autopadding=false, $maxh=0);
                }elseif($xx==0 || $xx==280){
                    PDF::MultiCell(10, 10, $yy, $border=1, $align='center', $fill=0, $ln=0, $xx, $yy, $reseth=true, $stretch=0, $ishtml=false, $autopadding=false, $maxh=0);
                }else{
                    PDF::MultiCell(10, 10, '', $border=1, $align='center', $fill=0, $ln=0, $xx, $yy, $reseth=true, $stretch=0, $ishtml=false, $autopadding=false, $maxh=0);
                }
            }
        }
        PDF::Output('hello_world.pdf');
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

        $std->map( function($product, $key) use($isInnerPack){
            if($key%8==0){
                PDF::AddPage();
                PDF::MultiCell($w=100, $h=10, '<span style="font-size:2em;">Hojas verdes</span>', $border=0, $align='center', $fill=0, $ln=0, $x=0, $y=0, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0);
            }
            $pz = '';
            if($isInnerPack){
                $pz = ' | '.$product['ipack'];
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
                            <span style="font-size:3.2em; font-weight: bold;">'.$product['scode'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:2em; font-weight: bold;">'.$product['item'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:'.$font_size_prices.'em; font-weight: bold;">'.$this->customPrices($product['prices']).'</span>
                            <span style="font-size:1.5em; font-weight: bold;">'.$product['tool'].$pz.'</span>
                        </div>';
            $this->setImageBackground(__DIR__.'./resources/img/STAR12.png', $content, 100, 62.5, 2, 4, 5, 0, $key);
        });
        $off->map( function($product, $key) use($isInnerPack){
            if($key%8==0){
                PDF::AddPage();
                PDF::MultiCell($w=100, $h=10, '<span style="font-size:2em;">Hojas naranjas</span>', $border=0, $align='center', $fill=0, $ln=0, $x=0, $y=0, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0);
            }
            $pz = '';
            if($isInnerPack){
                $pz = ' | '.$product['ipack'];
            }
            $content = '<span style="text-align: center;">
                            <span style="font-size:3.2em; font-weight: bold;">'.$product['scode'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:2.2em; font-weight: bold;">'.$product['item'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:2.9em; font-weight: bold;">'.$this->customPrices($product['prices']).'</span>
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
                $pz = ' | '.$product['ipack'];
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
                            <span style="font-size:2.1em; font-weight: bold;">'.$product['scode'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:1.5em; font-weight: bold;">'.$product['item'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:'.$font_size_prices.'em; font-weight: bold;">'.$this->customPrices($product['prices']).'</span>
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
                $pz = ' | '.$product['ipack'];
            }
            $content = '<span style="text-align: center;">
                            <span style="font-size:2.1em; font-weight: bold;">'.$product['scode'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:1.5em; font-weight: bold;">'.$product['item'].'</span><span style="font-size:.1em"><br/></span>
                            <span style="font-size:2.3em; font-weight: bold;">'.$this->customPrices($product['prices']).'</span>
                            <span style="font-size:1.2em; font-weight: bold;">'.$product['tool'].$pz.'</span>
                        </span>';
                        $this->setImageBackground(__DIR__.'./resources/img/STAR12.png', $content, 66.6, 41.6, 3, 6, 5, 0, $key);
        });

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
                $row = '<span>'.$price['labprint'].' <span style="font-size:1.2em;"> $ '.$price['price'].'</span></span><span style="font-size:.1px;"><br></span>';
                return $result.$row;
            });
            return $text;
        }
        $text = $prices->reduce( function( $result, $price){
            $row = '<span style="font-size:.5em;">¡¡¡'.$price['labprint'].'!!!</span><span style="font-size:.1px;"><br></span><span style="font-size:1em;"> $ '.$price['price'].'</span><br/>';
            return $result.$row;
        });
        return $text;
    }

    public function setImageBackground($image, $content, $width, $height, $cols, $rows, $top_space, $sides_space, $position){
        $bucle = floor(($position)/($rows*$cols));
        $position = $position-($bucle*$cols*$rows);
        $x = 5+(($position%$cols)*($width))+$sides_space;
        $y = 10+(intval(($position/$cols))*$height)+$top_space;
        $star = PDF::Image($image, 0, 0, 0, '', '', '', '', false, 700, '', true);
        PDF::Image($image, $x, $y, $width, $height, '', '', '', false, 300, '', false, $star);
        PDF::MultiCell($width, $height, $content, $border=1, $align="center", $fill=0, $ln=0, $x, $y+1, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0);
    }

    public function setImageBackground_area($image, $content, $width, $height, $cols, $rows, $top_space, $sides_space, $position, $top_margin, $sides_margin){
        $bucle = floor(($position)/($rows*$cols));
        $position = $position-($bucle*$cols*$rows);
        $x = (($position%$cols)*($width))+$sides_space+($sides_margin*(0+($position%$cols)));

        if(floor($position/$cols)==0){
            $y = (intval(($position/$cols))*$height)+$top_space+($top_margin*(1+(floor($position/$cols))));
        }else{
            $y = (intval(($position/$cols))*$height)+$top_space+($top_margin*(2+(floor($position/$cols))));
        }
        $star = PDF::Image($image, 0, 0, 0, '', '', '', '', false, 700, '', true);
        PDF::Image($image, $x, $y, $width, $height, '', '', '', false, 300, '', false, $star);
        PDF::MultiCell($width, $height, $content, $border=0, $align="center", $fill=0, $ln=0, $x, $y+1, $reseth=true, $stretch=0, $ishtml=true, $autopadding=false, $maxh=0);
    }
}