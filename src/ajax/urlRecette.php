<?php

use Slurp\Model\SlurpSite;
use Slurp\Utils\Crypt;

//Retourne l'url d'une recette
if(isset($_GET["recette"])){
    $app = SlurpSite::getInstance()->getApp();
    $routeParser= $app->getRouteCollector()->getRouteParser();
    $crypt= Crypt::getInstance();

    echo json_encode(array("url"=>$routeParser->urlFor('recette', ["rct" => $crypt->encrypt($_GET["recette"])])));
}