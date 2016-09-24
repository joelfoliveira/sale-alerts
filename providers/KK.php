<?php

namespace SaleAlerts;

use Goutte\Client;

class KK implements IProvider
{
    private $crawler;
    private $product;

    public function getProductProviderInfo(Product $product)
    {
        $this->product = $product;

        $client = new Client();
        $this->crawler = $client->request('GET', $product->url);

        $productInfo = new ProductProviderInfo();
        $productInfo->name = $this->getProductName();
        $productInfo->imageUrl = $this->getProductImageUrl();
        $productInfo->storePrices = $this->getProductStorePrices();

        return $productInfo;
    }

    private function getProductName()
    {
        $name = '';

        try
        {
            $name = $this->crawler->filter('h1[class="product-title"]')->text();
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
            $image = $this->crawler->filter('a[class="fancybox product-image"] img')->attr('src');
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
            $this->crawler->filter('#stores-offer-wrapper div[class="store-line store-line-active"]')->each(function($node, $i) use (&$storePrices)
            {
                $storeLink = $node->filter('a[class="btn store-item-go"]')->attr('onclick');

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
                    $priceStr = $node->filter('span[class="price "]')->text();
                    $price = Utils::stringPriceToFloat($priceStr);
                    if($price > 0)
                    {
                        $sp = new StorePrice($store, $price);
                        array_push($storePrices, $sp);
                    }
                }
            });
        }
        catch(\Exception $e)
        {
            echo '| getProductStorePrices - '.$this->product->name.': '.$e->getMessage().'<br />';
        }

        return $storePrices;
    }
}