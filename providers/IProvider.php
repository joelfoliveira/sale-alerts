<?php

namespace SaleAlerts;


interface IProvider
{
    public function getProductProviderInfo(Product $product);
}