<?php

namespace SaleAlerts;


class BuildReportStats
{
    private static $dbTableName = 'build_report_stats';

    public $highestPrice = 0;
    public $highestPriceDate = 0;
    public $lowestPrice = 0;
    public $lowestPriceDate = 0;
    public $averagePrice = 0;
    public $numReports = 0;

    public function insert()
    {
        $stats = self::getStats();

        if($stats == null)
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
        $stats = self::getStats();

        if($stats != null)
        {
            $db = Database::getInstance();
            $statement = $db->update(self::modelToArray($this))->table(self::$dbTableName);
            return $statement->execute() > 0 ? true : false;
        }
        else
        {
            return $this->insert();
        }
    }

    public function updateWithNewBuildReport(BuildReport $buildReport)
    {
        $time = time();

        if($buildReport->price > $this->highestPrice){
            $this->highestPrice = $buildReport->price;
            $this->highestPriceDate = $time;
        }

        if($this->lowestPrice == 0 || $buildReport->price < $this->lowestPrice){
            $this->lowestPrice = $buildReport->price;
            $this->lowestPriceDate = $time;
        }

        $this->numReports++;

        if($this->averagePrice == 0){
            $average = $buildReport->price;
        }else{
            $average = (($this->averagePrice * ($this->numReports - 1)) + $buildReport->price) / $this->numReports;
        }

        $this->averagePrice = round($average, 2);

        return $this->update();
    }

    public static function getStats()
    {
        $stats = null;

        $db = Database::getInstance();

        $statement = $db->select()->from(self::$dbTableName)->limit(1, 0);
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
        $obj = new BuildReportStats();
        $obj->highestPrice = $objArray['highest_price'];
        $obj->highestPriceDate = $objArray['highest_price_date'];
        $obj->lowestPrice = $objArray['lowest_price'];
        $obj->lowestPriceDate = $objArray['lowest_price_date'];
        $obj->averagePrice = $objArray['average_price'];
        $obj->numReports = $objArray['num_reports'];

        return $obj;
    }

    private static function modelToArray(BuildReportStats $model)
    {
        return array(
            'highest_price' => $model->highestPrice,
            'highest_price_date' => $model->highestPriceDate,
            'lowest_price' => $model->lowestPrice,
            'lowest_price_date' => $model->lowestPriceDate,
            'average_price' => $model->averagePrice,
            'num_reports' => $model->numReports
        );
    }
}