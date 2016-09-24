<?php


namespace SaleAlerts;


class StorePrice
{
    public $storeName;
    public $price;

    public function __construct($storeName, $price)
    {
        $this->storeName = $storeName;
        $this->price = $price;
    }
}