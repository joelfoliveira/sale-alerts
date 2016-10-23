<?php

namespace SaleAlerts;

set_time_limit(180);

require_once('lib/Config.php');
if(isset($_GET['key']) && $_GET['key'] === Config::$apiKey)
{
    require_once('lib/Application.php');

    if(isset($_GET['action']) && !empty($_GET['action']))
    {
        switch ($_GET['action'])
        {
            case 'sales-alert':
            case '1':
                $controller = new SalesPriceController();
                $success = $controller->sendReport();
                break;
            case 'build-report':
            case '2':
                $controller = new BuildReportController();
                $success = $controller->sendReport();
                break;
            default:
                http_response_code(403);
                echo "Invalid request";
                exit();
        }

        echo '<br/></br>';
        echo 'Job finished '.($success ? 'with' : 'without').' success.';
    }
    else
    {
        http_response_code(403);
        echo "Invalid request";
        exit();
    }
}
else
{
    http_response_code(403);
    echo "Not authorized";
    exit();
}

