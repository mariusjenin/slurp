<?php

use Slurp\Model\SlurpSite;
use Slurp\Utils\Crypt;

//Retourne l'url d'un ingredient
if(isset($_GET["ingdt"])){
    $app = SlurpSite::getInstance()->getApp();
    $routeParser= $app->getRouteCollector()->getRouteParser();
    $crypt= Crypt::getInstance();

    echo json_encode($routeParser->urlFor('ingredient', ["ingdt" => $crypt->encrypt($_GET["ingdt"])]));
}