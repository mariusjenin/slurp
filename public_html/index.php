<?php

use Slurp\Model\SlurpSite;

require_once __DIR__ . '/../vendor/autoload.php';

$slurp=SlurpSite::getInstance();
$slurp->addRoutes();
$slurp->run();