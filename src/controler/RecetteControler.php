<?php

namespace Slurp\Controler;

use Slim\Routing\RouteContext;
use Slurp\Model\CategorieManagerFactory;
use Slurp\Model\RecetteManagerFactory;
use Slurp\Model\SlurpSite;
use Slurp\Utils\Crypt;
use Slurp\View\IngredientView;
use Slurp\View\RecetteView;

class RecetteControler extends Controler
{

    public function getHTMLRecette()
    {
        $rctFact= RecetteManagerFactory::getInstance();
        $utils=Crypt::getInstance();
        $nomRct=$utils->decrypt($this->args['rct']);
        $rct=$rctFact->searchRecetteByTitre($nomRct);
        if($rct==false){
            return false;
        }

        $listIngdts = $rct->getIngdts();
        $titre = $rct->getTitre();
        $recFact=RecetteManagerFactory::getInstance();
        $recettes=$recettes=array_map(function ($v){
            return $v['recette'];
        },$recFact->searchRecettesByIngdts($listIngdts,[],false,true,[$titre]));
        $rctCont = new RecetteView();
        return $rctCont->getHTMLRecette($this->request,$rct,$recettes);
    }

    public function getHTMLTrouverRecette()
    {
        $recFact=RecetteManagerFactory::getInstance();
        $rctCont = new RecetteView();
        $recettes=$recFact->searchRecettesRandom(4);
        return $rctCont->getHTMLTrouverRecette($this->request,$recettes);
    }

    public function getHTMLRecetteFavorite()
    {
        $recFact=RecetteManagerFactory::getInstance();
        $nbrRecetteAleat=4;
        $recettes=$recFact->searchRecettesRandom($nbrRecetteAleat);
        $rcts=$recFact->getRecettesFavorites(SlurpSite::getInstance()->getEmailConnected());
        $rctCont = new RecetteView();
        return $rctCont->getHTMLRecetteFavorite($this->request,$rcts,$recettes);
    }
}