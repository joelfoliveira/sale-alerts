<?php

namespace SaleAlerts;

class Application
{
    public static function start()
    {
        chdir(dirname(__DIR__));

        require_once 'vendor/autoload.php';
        require_once 'lib/Config.php';
        require_once 'lib/Database.php';
        require_once 'lib/Utils.php';

        require_once 'controllers/ComponentListController.php';
        require_once 'controllers/SalesPriceController.php';
        require_once 'controllers/BuildReportController.php';

        require_once 'models/Product.php';
        require_once 'models/ProductCategory.php';
        require_once 'models/ProductProviderInfo.php';
        require_once 'models/BuildReport.php';
        require_once 'models/BuildReportStats.php';
        require_once 'models/StorePrice.php';

        require_once 'providers/IProvider.php';
        require_once 'providers/KK.php';
    }
}

Application::start();