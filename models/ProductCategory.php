<?php

namespace SaleAlerts;

class ProductCategory
{
    private static $dbTableName = 'product_categories';

    public $id;
    public $name;

    public static function getNameFromId($cats, $id)
    {
        $name = '';

        if(is_array($cats) && count($cats) > 0)
        {
            foreach($cats as $key => $cat)
            {
                if($cat->id == $id){
                    $name = $cat->name;
                }
            }
        }

        return $name;
    }

    public static function getAll()
    {
        $cats = array();

        $db = Database::getInstance();

        $selectStatement = $db->select()->from(self::$dbTableName);
        $stmt = $selectStatement->execute();
        $result = $stmt->fetchAll();

        if($result && is_array($result) && count($result) > 0)
        {
            foreach($result as $key => $value)
            {
                $cats[] = self::arrayToModel($value);
            }
        }

        return $cats;
    }

    private static function arrayToModel($objArray)
    {
        $obj = new ProductCategory();
        $obj->id = $objArray['id'];
        $obj->name = $objArray['name'];

        return $obj;
    }

    private static function modelToArray(Product $model)
    {
        return array(
            'name' => $model->name
        );
    }
}