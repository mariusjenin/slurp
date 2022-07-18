<?php

namespace Slurp\Controler;

use Slim\Routing\RouteContext;
use Slurp\Model\CategorieManagerFactory;
use Slurp\Model\RecetteManagerFactory;
use Slurp\Utils\Crypt;
use Slurp\View\IngredientView;

class IngredientControler extends Controler
{

    public function getHTMLAliment()
    {
        $ingCont = new IngredientView();
        return $ingCont->getHTMLAliment($this->request);
    }

    public function getHTMLIngredient()
    {
        $catFact= CategorieManagerFactory::getInstance();
        $utils=Crypt::getInstance();
        $nomIgdt=$utils->decrypt($this->args['ingdt']);
        $ingdt=$catFact->searchCategByTitre($nomIgdt);
        if($ingdt==false){
            return false;
        }
        $ingCont = new IngredientView();

        $recFact=RecetteManagerFactory::getInstance();
        $recettes=array_map(function ($v){
            return $v['recette'];
        },$recFact->searchRecettesByIngdts([$ingdt->getTitre()],[],false,false,[]));
        return $ingCont->getHTMLIngredient($this->request,$ingdt,$recettes);
    }
}

?>