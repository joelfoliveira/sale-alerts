<?php

namespace SaleAlerts;

class BuildReport
{
    private static $dbTableName = 'build_report';

    public $id = 0;
    public $date;
    public $price;
    public $products;

    public function insert()
    {
        $buildReport = is_int($this->id) && $this->id > 0 ? self::getById($this->id) : null;

        if($buildReport == null)
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
        $buildReport = is_int($this->id) && $this->id > 0 ? self::getById($this->id) : null;

        if($buildReport != null)
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

    public static function getById($id)
    {
        $stats = new BuildReportStats();

        $db = Database::getInstance();

        $statement = $db->select()->from(self::$dbTableName)->where('id', '=', $id)->limit(1, 0);
        $stmt = $statement->execute();
        $result = $stmt->fetch();

        if($result && is_array($result) && count($result) > 0)
        {
            $stats = self::arrayToModel($result);
        }

        return $stats;
    }

    public static function getLast()
    {
        $stats = new BuildReportStats();

        $db = Database::getInstance();

        $statement = $db->select()->from(self::$dbTableName)->orderBy('date', 'DESC');
        $stmt = $statement->execute();
        $result = $stmt->fetch();

        if($result && is_array($result) && count($result) > 0)
        {
            $stats = self::arrayToModel($result);
        }

        return $stats;
    }

    private static function arrayToModel($objArray)
    {
        $obj = new BuildReport();
        $obj->id = $objArray['id'];
        $obj->date = $objArray['date'];
        $obj->price = $objArray['price'];
        $obj->products = unserialize(base64_decode($objArray['products']));

        return $obj;
    }

    private static function modelToArray(BuildReport $model)
    {
        return array(
            'date' => $model->date,
            'price' => $model->price,
            'products' => base64_encode(serialize($model->products))
        );
    }

}