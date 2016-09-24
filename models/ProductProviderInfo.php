<?php

namespace SaleAlerts;


class ProductProviderInfo
{
    public $name;
    public $url;
    public $imageUrl;

    /** @var StorePrice[] */
    public $storePrices;

    public function getLowestStorePrice()
    {
        $storePrice = null;
        $minValue = 0;

        if(is_array($this->storePrices) && count($this->storePrices) > 0)
        {
            foreach($this->storePrices as $key => $value)
            {
                if($minValue == 0 || $value->price < $minValue)
                {
                    $minValue = $value->price;
                    $storePrice = $value;
                }
            }
        }

        return $storePrice;
    }
}