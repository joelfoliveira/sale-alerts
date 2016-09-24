<?php


namespace SaleAlerts;


class SalesPriceController
{
    private static $waitTimeBetweenProductChecksInMs = 50;

    public function sendReport()
    {
        $products = Product::getAllActive();

        $productsToNotify = array();
        if(is_array($products) && count($products) > 0)
        {
            foreach($products as $key => $product)
            {
                try
                {
                    $productProviderInfo = $this->getProductProviderInfo($product);
                    if(is_array($productProviderInfo->storePrices) && count($productProviderInfo->storePrices))
                    {

                        if($this->shouldUserBeNotifiedAboutProduct($product, $productProviderInfo))
                        {
                            array_push($productsToNotify, $product);
                        }

                        $product->visible = true;
                        $product->updateWithProviderInfo($productProviderInfo);
                    }
                    else
                    {
                        $product->visible = false;
                        $product->update();
                    }
                }
                catch(\Exception $e)
                {
                    echo '|'.$product->name.': '.$e->getMessage().'<br />';
                }
                finally
                {
                    usleep(self::$waitTimeBetweenProductChecksInMs);
                }
            }
        }

        return $this->sendSalesNotificationEmail($productsToNotify);
    }

    private function getProductProviderInfo(Product $product)
    {
        $provider = $this->getProviderFromProductUrl($product->url);
        return $provider->getProductProviderInfo($product);
    }

    private function getProviderFromProductUrl($url)
    {
        if(!$url || empty($url))
        {
            return null;
        }

        if(isset(Config::$providers) && is_array(Config::$providers) && count(Config::$providers) > 0)
        {
            foreach(Config::$providers as $key => $provider)
            {
                if (strpos($url, $key) !== false)
                {
                    $provider = __NAMESPACE__.'\\'.$provider;
                    return new $provider;
                }
            }
        }

        return null;
    }

    private function shouldUserBeNotifiedAboutProduct(Product $product, ProductProviderInfo $productProviderInfo)
    {
        $lowestStorePrice = $productProviderInfo->getLowestStorePrice();

        if($lowestStorePrice == null){
            return false;
        }

        $lowestPrice = $lowestStorePrice->price;

        if($lowestPrice >= $product->lastPrice){
            return false;
        }

        $lowestPercentageDiff = ($lowestPrice / $product->lowestPrice) * 100;
        if($lowestPercentageDiff >= Config::$priceDiffPercentageFromLowest){
            return true;
        }

        $lastPercentageDiff = ($lowestPrice / $product->lastPrice) * 100;
        if($lastPercentageDiff >= Config::$priceDiffPercentageFromLast){
            return true;
        }

        return false;
    }

    private function sendSalesNotificationEmail($productsToNotify)
    {
        if(!is_array($productsToNotify) || count($productsToNotify) == 0)
        {
            return false;
        }

        $emailHtml = $this->getSaleAlertTemplate($productsToNotify);

        if(Config::$printEmail){
            echo $emailHtml;
        }

        if(!empty($emailHtml))
        {
            return Utils::sendEmail($emailHtml, Config::$emailSaleAlertSubject);
        }
        else
        {
            return false;
        }
    }

    private function getSaleAlertTemplate($products)
    {
        $loader = new \Twig_Loader_Filesystem(dirname(__DIR__).DIRECTORY_SEPARATOR.'views');
        $twig = new \Twig_Environment($loader);
        $template = $twig->loadTemplate('SaleAlertEmail.twig');
        return $template->render(array('products' => $products));
    }
}