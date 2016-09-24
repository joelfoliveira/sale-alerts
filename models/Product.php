<?php

namespace SaleAlerts;

class Product
{
    private static $dbTableName = 'products';

    public $id = 0;
    public $name;
    public $url;
    public $imageUrl;
    public $highestPrice;
    public $highestPriceDate;
    public $highestPriceStore;
    public $lowestPrice;
    public $lowestPriceDate;
    public $lowestPriceStore;
    public $averagePrice;
    public $initialPrice;
    public $initialPriceDate;
    public $initialPriceStore;
    public $lastPrice;
    public $lastPriceDate;
    public $lastPriceStore;
    public $active;
    public $visible;
    public $dateCreated;
    public $dateUpdated;
    public $numUpdates;
    public $buildReport;

    public function insert()
    {
        $product = $this->id > 0 ? self::getById($this->id) : null;

        if($product == null)
        {
            $modelArr = self::modelToArray($this);
            $db = Database::getInstance();
            $statement = $db->insert(array_keys($modelArr))->into(self::$dbTableName)->values(array_values($modelArr));
            return $statement->execute(true) > 0 ? true : false;
        }
        else
        {
            return $this->update();
        }
    }

    public function update()
    {
        $product = $this->id > 0 ? self::getById($this->id) : null;

        if($product != null)
        {
            $db = Database::getInstance();
            $statement = $db->update(self::modelToArray($this))->table(self::$dbTableName)->where('id', '=', $this->id);
            return $statement->execute() > 0 ? true : false;
        }
        else
        {
            return $this->insert();
        }
    }

    public function updateWithProviderInfo(ProductProviderInfo $providerInfo)
    {
        $time = time();

        $lowestStorePrice = $providerInfo->getLowestStorePrice();

        if($lowestStorePrice == null) {
            return false;
        }

        if(empty($this->name)){
            $this->name = $providerInfo->name;
        }

        $this->imageUrl = $providerInfo->imageUrl;

        if($lowestStorePrice->price > $this->highestPrice){
            $this->highestPrice = $lowestStorePrice->price;
            $this->highestPriceDate = $time;
            $this->highestPriceStore = $lowestStorePrice->storeName;
        }

        if($this->lowestPrice == 0 || $lowestStorePrice->price < $this->lowestPrice){
            $this->lowestPrice = $lowestStorePrice->price;
            $this->lowestPriceDate = $time;
            $this->lowestPriceStore = $lowestStorePrice->storeName;
        }

        if($this->initialPrice == 0){
            $this->initialPrice = $lowestStorePrice->price;
            $this->initialPriceDate = $time;
            $this->initialPriceStore = $lowestStorePrice->storeName;
        }

        $this->lastPrice = $lowestStorePrice->price;
        $this->lastPriceDate = $time;
        $this->lastPriceStore = $lowestStorePrice->storeName;

        $this->dateUpdated = $time;

        $this->numUpdates++;

        if($this->averagePrice == 0){
            $average = $lowestStorePrice->price;
        }else{
            $average = (($this->averagePrice * ($this->numUpdates - 1)) + $lowestStorePrice->price) / $this->numUpdates;
        }

        $this->averagePrice = round($average, 2);

        return $this->update();
    }

    public static function getTotal($products)
    {
        $total = 0;
        if(is_array($products) && count($products) > 0)
        {
            foreach($products as $key => $product)
            {
                $total += $product->lastPrice;
            }
        }
        return $total;
    }

    public static function getAllActive()
    {
        $products = array();

        $db = Database::getInstance();

        $selectStatement = $db->select()->from(self::$dbTableName)->where('active', '=', '1')->orderBy('id_cat', 'ASC')->orderBy('lowest_price', 'ASC');
        $stmt = $selectStatement->execute();
        $result = $stmt->fetchAll();

        if($result && is_array($result) && count($result) > 0)
        {
            foreach($result as $key => $value)
            {
                $products[] = self::arrayToModel($value);
            }
        }

        return $products;
    }

    public static function getById($id)
    {
        $product = null;

        $db = Database::getInstance();

        $selectStatement = $db->select()->from(self::$dbTableName)->where('id', '=', $id);
        $stmt = $selectStatement->execute();
        $result = $stmt->fetch();

        if($result && is_array($result) && count($result) > 0)
        {
            $product = self::arrayToModel($result);
        }

        return $product;
    }

    public static function getByUrl($url)
    {
        $product = null;

        $db = Database::getInstance();

        $selectStatement = $db->select()->from(self::$dbTableName)->where('url', '=', $url);
        $stmt = $selectStatement->execute();
        $result = $stmt->fetch();

        if($result && is_array($result) && count($result) > 0)
        {
            $product = self::arrayToModel($result);
        }

        return $product;
    }

    public static function getBuildProducts()
    {
        $products = array();

        $db = Database::getInstance();

        $statement = $db->select()->from(self::$dbTableName)->where('active', '=', '1')->where('build_report', '=', '1')->orderBy('id_cat', 'ASC');
        $stmt = $statement->execute();
        $result = $stmt->fetchAll();

        if($result && is_array($result) && count($result) > 0)
        {
            foreach($result as $key => $value)
            {
                $products[] = self::arrayToModel($value);
            }
        }

        return $products;
    }

    private static function arrayToModel($objArray)
    {
        $obj = new Product();
        $obj->id = $objArray['id'];
        $obj->name = $objArray['name'];
        $obj->url = $objArray['url'];
        $obj->imageUrl = $objArray['image_url'];
        $obj->highestPrice = $objArray['highest_price'];
        $obj->highestPriceDate = $objArray['highest_price_date'];
        $obj->highestPriceStore = $objArray['highest_price_store'];
        $obj->lowestPrice = $objArray['lowest_price'];
        $obj->lowestPriceDate = $objArray['lowest_price_date'];
        $obj->lowestPriceStore = $objArray['lowest_price_store'];
        $obj->averagePrice = $objArray['average_price'];
        $obj->initialPrice = $objArray['initial_price'];
        $obj->initialPriceDate = $objArray['initial_price_date'];
        $obj->initialPriceStore = $objArray['initial_price_store'];
        $obj->lastPrice = $objArray['last_price'];
        $obj->lastPriceDate = $objArray['last_price_date'];
        $obj->lastPriceStore = $objArray['last_price_store'];
        $obj->active = $objArray['active'] == 1 ? true : false;
        $obj->visible = $objArray['visible'] == 1 ? true : false;
        $obj->dateCreated = $objArray['date_created'];
        $obj->dateUpdated = $objArray['date_updated'];
        $obj->numUpdates = $objArray['num_updates'];
        $obj->buildReport = $objArray['build_report'] == 1 ? true : false;

        return $obj;
    }

    private static function modelToArray(Product $model)
    {
        return array(
            'name' => $model->name,
            'url' => $model->url,
            'image_url' => $model->imageUrl,
            'highest_price' => $model->highestPrice,
            'highest_price_date' => $model->highestPriceDate,
            'highest_price_store' => $model->highestPriceStore,
            'lowest_price' => $model->lowestPrice,
            'lowest_price_date' => $model->lowestPriceDate,
            'lowest_price_store' => $model->lowestPriceStore,
            'average_price' => $model->averagePrice,
            'initial_price' => $model->initialPrice,
            'initial_price_date' => $model->initialPriceDate,
            'initial_price_store' => $model->initialPriceStore,
            'last_price' => $model->lastPrice,
            'last_price_date' => $model->lastPriceDate,
            'last_price_store' => $model->lastPriceStore,
            'active' => $model->active ? 1 : 0,
            'visible' => $model->visible ? 1 : 0,
            'date_created' => $model->dateCreated,
            'date_updated' => $model->dateUpdated,
            'num_updates' => $model->numUpdates,
            'build_report' => $model->buildReport ? 1 : 0
        );
    }
}