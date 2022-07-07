<?php

namespace App;

require 'vendor/autoload.php';

class Scrape
{
    private array $products = [];

    public function run(): void
    {
        
        for($i = 1; $i < 3;  $i++){
            $document = ScrapeHelper::fetchDocument('https://www.magpiehq.com/developer-challenge/smartphones/?page='.$i);

            $product = [];

            $fetchProduct = $document->filter('.product-name, .product-capacity, .bg-white .text-lg, .product img, .px-2 span,.bg-white .text-sm')->extract(['_name', '_text', 'class','src', 'data-colour']);
    
            foreach($fetchProduct as $domElement){

                if($domElement[0] == "img"){
                    $product['imageUrl'] = $domElement[3];
                }elseif($domElement[0] == "span"){

                    if(!empty($domElement[4])){
                        $product['color'] = $domElement[4];
                    }

                    if(!empty($domElement[1])){
                        if(preg_match('/\d*[GBMB]/', trim($domElement[1]))){
                            $product['capacity'] = preg_match('/\d*[MB]/', trim($domElement[1])) ? intval($domElement[1]) * 1000 : intval($domElement[1]);
                        }else{
                            $product['title'] = trim($domElement[1]);
                        }
                        
                    }
                }else{
                    if(preg_match('/[£]\d+.?\d+/', trim($domElement[1]))){
                        $product['price'] = trim($domElement[1]);
                    }else{
                        if(trim($domElement[1]) == "Availability: Out of Stock"){
                            $product['availabilityText'] = trim($domElement[1]);
                            $product['isAvailable'] = trim($domElement[1]) == "Availability: Out of Stock" ? "false" : "true";
                        }else{
                            $product['availabilityText'] = "Availability: In Stock";
                            $product['isAvailable']      = "true";
                            $product['shippingText']     = trim($domElement[1]);
                            $product['shippingDate']     = date('Y-m-d');
                        }
                    }
                }

                if(count($product) > 6){
                array_push($this->products, $product );
                }

                    
            }
        }

        file_put_contents('output.json', json_encode($this->products));
    }
}

$scrape = new Scrape();
$scrape->run();
