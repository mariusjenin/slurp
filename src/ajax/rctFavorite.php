<?php

use Slurp\Model\Recette;
use Slurp\Model\RecetteManagerFactory;
use Slurp\Model\SlurpSite;

$res=false;
//On ajoute ou on retire la recette des favoris
if (isset($_POST['recette']) && isset($_POST['add'])) {
    $recFact= RecetteManagerFactory::getInstance();
    $rct=$recFact->searchRecetteByTitre($_POST['recette']);
    if($rct instanceof Recette){
        $slurp=SlurpSite::getInstance();
        $emailConnected= $slurp->getEmailConnected();
        if($_POST["add"]==="true"){
            $res=$rct->addInFavorites($emailConnected);
        } else {
            $res=$rct->removeFromFavorites($emailConnected);
        }
    }
}
echo json_encode($res);