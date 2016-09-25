<?php

namespace SaleAlerts;

require_once('lib/Config.php');
require_once('lib/Application.php');

$controller = new ComponentListController();
$controller->showComponentList();