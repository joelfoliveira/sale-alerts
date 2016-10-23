<?php

namespace SaleAlerts;

use Sunra\PhpSimple\HtmlDomParser;

class KK implements IProvider
{
    private $dom;

    public function getProductProviderInfo(Product $product)
    {
        $productInfo = null;

        $this->dom = $this->getProductDomDocumentFromUrl($product->url);

        if(!is_null($this->dom) && !empty($this->dom))
        {
            $productInfo = new ProductProviderInfo();
            $productInfo->name = $this->getProductName();
            $productInfo->imageUrl = $this->getProductImageUrl();
            $productInfo->storePrices = $this->getProductStorePrices();
        }

        if($productInfo == null){
            throw new \NullPointerException(sprintf("Product %s not found", $product->name));
        }

        return $productInfo;
    }

    private function getProductDomDocumentFromUrl($url)
    {
        $dom = null;

        $html = file_get_contents($url);
        if(!empty($html))
        {
            $dom = HtmlDomParser::str_get_html($html);
        }

        return $dom;
    }

    private function getProductName()
    {
        $name = '';

        try
        {
            $nameElem = $this->dom->find('h1[class=product-title]', 0);
            if(!empty($nameElem)){
                $name = $nameElem->plaintext;
            }
        }
        catch(\Exception $e)
        {
            echo '| getProductName - '.$this->product->name.': '.$e->getMessage().'<br />';
        }

        return $name;
    }

    private function getProductImageUrl()
    {
        $image = '';

        try
        {
            $imageElem = $this->dom->find('div[id="product-image"] img', 0);
            if(!empty($imageElem))
            {
                $image = $imageElem->src;
            }
            else
            {
                //Fallback
                $imageElem = $this->dom->find('a[class="product-image"] img[class="img-responsive"]', 0);
                if(!empty($imageElem))
                {
                    $image = $imageElem->src;
                }
            }
        }
        catch(\Exception $e)
        {
            echo '| getProductImageUrl - '.$this->product->name.': '.$e->getMessage().'<br />';
        }

        return $image;
    }

    private function getProductStorePrices()
    {
        $storePrices = array();

        try
        {
            foreach($this->dom->find('div[class=store-line-active]') as $storeElem)
            {
                $storeLinkElem = $storeElem->find('a[class=store-item-go]', 0);
                if(!empty($storeLinkElem))
                {
                    $storeLink = $storeLinkElem->onclick;

                    $store = '';
                    foreach(Config::$allowedStores as $key => $storeKey )
                    {
                        if (strpos($storeLink, $storeKey) !== false)
                        {
                            $store = $storeKey;
                            break;
                        }
                    }

                    if(!empty($store))
                    {
                        $priceStrElem = $storeElem->find('span[class=price]', 0);
                        if(!empty($priceStrElem))
                        {
                            $priceStr = $priceStrElem->plaintext;
                            $price = Utils::stringPriceToFloat($priceStr);
                            if($price > 0)
                            {
                                $sp = new StorePrice($store, $price);
                                array_push($storePrices, $sp);
                            }
                        }
                    }
                }
            }
        }
        catch(\Exception $e)
        {
            echo '| getProductStorePrices - '.$this->product->name.': '.$e->getMessage().'<br />';
        }

        return $storePrices;
    }
}