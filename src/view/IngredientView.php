<?php
namespace Slurp\View;

use Slim\Routing\RouteContext;
use Slurp\Controler\RecetteControler;
use Slurp\Model\Categorie;
use Slurp\Model\Recette;
use Slurp\Model\RecetteManagerFactory;
use Slurp\Utils\Crypt;
use Slurp\Utils\Utils;

class IngredientView
{

    public function __construct()
    {
    }

    public function getHTMLAliment($request)
    {
        $crypt=Crypt::getInstance();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $res = <<<END
<section>
    <div class="banner-area generic-banner generic-banner-2">
        <div class="container">
            <div class="row justify-content-center generic-height align-items-center">
                <div class="col-lg-8">
                    <div class="banner-content text-center">
                        <!--                        <span class="text-white top text-uppercase">Cherchez votre ingrédient</span>-->
                        <h1 class="text-white text-uppercase">Cherchez votre ingrédient</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!--    Partie à reprendre-->
        <div class="sample-text-area container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="row mb-2">
                        <a href="{$routeParser->urlFor('ingredient',["ingdt"=>$crypt->encrypt("Fruit")])}" class="col-lg-12">
                            <div class="categOfAliment mb-2 mt-2 d-flex"
                                 style='background-image: url("{$routeParser->urlFor('img',["routes"=>"imgSlurp/fruit.jpg"])}");'>
                                <div class="col-md-6 justify-content-center text-center d-flex align-items-center">
                                    Fruit
                                </div>
                            </div>
                        </a>
                        <a href="{$routeParser->urlFor('ingredient',["ingdt"=>$crypt->encrypt("Assaisonnement")])}" class="col-lg-12">
                            <div class="categOfAliment mb-2 mt-2 d-flex"
                                 style='background-image: url("{$routeParser->urlFor('img',["routes"=>"imgSlurp/assaisonnement.jpg"])}");'>
                                <div class="col-md-6 justify-content-center text-center d-flex align-items-center">
                                    Assaisonnement
                                </div>
                            </div>
                        </a>
                        <a href="{$routeParser->urlFor('ingredient',["ingdt"=>$crypt->encrypt("Légume")])}" class="col-lg-12">
                            <div class="categOfAliment mb-2 mt-2 d-flex"
                                 style='background-image: url("{$routeParser->urlFor('img',["routes"=>"imgSlurp/légume.jpg"])}");'>
                                <div class="col-md-6 justify-content-center text-center d-flex align-items-center">
                                    Légume
                                </div>
                            </div>
                        </a>
                        <a href="{$routeParser->urlFor('ingredient',["ingdt"=>$crypt->encrypt("Liquide")])}" class="col-lg-12">
                            <div class="categOfAliment mb-2 mt-2 d-flex"
                                 style='background-image: url("{$routeParser->urlFor('img',["routes"=>"imgSlurp/liquide.jpg"])}");'>
                                <div class="col-md-6 justify-content-center text-center d-flex align-items-center">
                                    Liquide
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 ">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="bannerVerticalAliment d-none d-lg-flex mb-2 mt-2 generic-banner-1 justify-content-center align-items-center">
                                <div class="col-lg-10">
                                    <div class="banner-content text-center">
                                        <h1 class="text-white text-uppercase">Slurp</h1>
                                        <span style="font-size: 20px" class="text-white top text-uppercase">Recherchez un ingrédient pour trouver toutes les recettes liées à celui-ci</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 order-lg-2">
                    <div class="row">
                        <a href="{$routeParser->urlFor('ingredient',["ingdt"=>$crypt->encrypt("Noix et graine oléagineuse")])}" class="col-lg-12">
                            <div class="categOfAliment mb-2 mt-2 d-flex justify-content-lg-end"
                                 style='background-image: url("{$routeParser->urlFor('img',["routes"=>"imgSlurp/noix et graine oléagineuse.jpg"])}");'>
                                <div class="col-md-6 justify-content-center text-center d-flex align-items-center">Noix
                                    et graine oléagineuse
                                </div>
                            </div>
                        </a>
                        <a href="{$routeParser->urlFor('ingredient',["ingdt"=>$crypt->encrypt("Oeuf")])}" class="col-lg-12">
                            <div class="categOfAliment mb-2 mt-2 d-flex justify-content-lg-end"
                                 style='background-image: url("{$routeParser->urlFor('img',["routes"=>"imgSlurp/oeuf.jpg"])}");'>
                                <div class="col-md-6 justify-content-center text-center d-flex align-items-center">
                                    Oeuf
                                </div>
                            </div>
                        </a>
                        <a href="{$routeParser->urlFor('ingredient',["ingdt"=>$crypt->encrypt("Aliments divers")])}" class="col-lg-12">
                            <div class="categOfAliment mb-2 mt-2 d-flex justify-content-lg-end"
                                 style='background-image: url("{$routeParser->urlFor('img',["routes"=>"imgSlurp/aliment divers.jpg"])}");'>
                                <div class="col-md-6 justify-content-center text-center d-flex align-items-center">
                                    Aliments divers
                                </div>
                            </div>
                        </a>
                        <a href="{$routeParser->urlFor('ingredient',["ingdt"=>$crypt->encrypt("Produit laitier")])}" class="col-lg-12">
                            <div class="categOfAliment mb-2 mt-2 d-flex justify-content-lg-end"
                                 style='background-image: url("{$routeParser->urlFor('img',["routes"=>"imgSlurp/produit laitier.jpg"])}");'>
                                <div class="col-md-6 justify-content-center text-center d-flex align-items-center">
                                    Produit laitier
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 ">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="bannerVerticalAliment d-none d-lg-flex mb-2 mt-2 generic-banner-3 justify-content-center align-items-center">
                                <div class="col-lg-10">
                                    <div class="banner-content text-center">
                                        <h1 style="font-size: 24px" class="text-white top text-uppercase">Naviguez à
                                            travers tous nos ingrédients</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
END;
        return $res;
    }

    public function getHTMLIngredient($request,Categorie $ingdt,$recettes)
    {
        $crypt=Crypt::getInstance();
        $recView=new RecetteView();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $rootAliment = $routeParser->urlFor('aliment');

        $texteSuperCateg="Catégorie".(count($ingdt->getSuperCategs())>1?'s':''). " supérieure".(count($ingdt->getSuperCategs())>1?'s':'');
        $texteSousCateg="Catégorie".(count($ingdt->getSousCategs())>1?'s':''). " inférieure".(count($ingdt->getSousCategs())>1?'s':'');

        $texteRecette=(empty($recettes)?'Pas de recette':"Recette".(count($recettes)>1?'s':'')) ." avec cet ingrédient";

        $chemin=$ingdt->getChemin();
        $res = <<<END
<section>
    <div class="banner-area generic-banner generic-banner-2">
        <div class="container">
            <div class="row justify-content-center generic-height align-items-center">
                <div class="col-lg-8">
                    <div class="banner-content text-center">
                        <span class="text-white top text-uppercase">Cherchez votre ingrédient</span>
                        <h1 class="text-white text-uppercase">{$ingdt->getTitre()}</h1>
                        <div class="mt-2">
END;
        for($i=0;$i<count($chemin);$i++){
            if($chemin[$i]=="Aliment"){
                $res.=<<<END
                            <div onclick="location.href='{$routeParser->urlFor("aliment")}'" class="itemBreadcrumb p-2 ">
                                Aliment
                            </div>
END;
            }else {
                if($chemin[$i]==$ingdt->getTitre()){
                    $res.=<<<END
                            <div class="itemBreadcrumb p-2 ">
END;
                } else {$res.=<<<END
                            <div onclick="location.href='{$routeParser->urlFor("ingredient",["ingdt"=>$crypt->encrypt($chemin[$i])])}'" class="itemBreadcrumb p-2 ">
END;
                }
                $res.=<<<END
                                {$chemin[$i]}
                            </div>
END;
            }
            if($i<count($chemin)-1){
                $res.=<<<END
                            <div class="delimiterBreadcrumb text-white">
                                >
                            </div>
END;
            }
        }
        $res.=<<<END
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <div class="container mt-40">
            <div class="row">
                <div class="mt-4 mt-md-0 col-md-6">
                    <div class="partieIngredient p-3 w-100 text-center justify-content-center">
                        <h3 class="titreDegradCyanMagenta w-100 text-uppercase mb-2">{$texteSuperCateg}</h3>
END;
        foreach ($ingdt->getSuperCategs() as $v){
            if($v=="Aliment"){
                $res .= <<<END
<a href="{$rootAliment}" class="mt-2 pt-4 pb-4 w-100 btnIngredient">{$v}</a>
END;
            } else {
                $res .= <<<END
<a href="{$routeParser->urlFor('ingredient',["ingdt"=>$crypt->encrypt($v)])}" class="mt-2 pt-4 pb-4 w-100 btnIngredient">{$v}</a>
END;
            }
        }
        if(empty($ingdt->getSuperCategs())){
            $res.=<<<END
Pas de super-catégorie
END;
        }
        $res.=<<<END
                    </div>
                </div>
                <div class="mt-4 mt-md-0 col-md-6">
                    <div class="partieIngredient p-3 w-100 text-center justify-content-center">
                        <h3 class="titreJauneorangeRouge w-100 text-uppercase mb-2">{$texteSousCateg}</h3>
END;
        foreach ($ingdt->getSousCategs() as $v){
            $res.=<<<END
<a href="{$routeParser->urlFor('ingredient',["ingdt"=>$crypt->encrypt($v)])}" class="mt-2 pt-4 pb-4 w-100 btnIngredient">{$v}</a>
END;
        }
        if(empty($ingdt->getSousCategs())){
            $res.=<<<END
Pas de sous-catégorie
END;
        }
        $res.=<<<END
                    </div>
                </div>
            </div>
        </div>
     </section>
        {$recView->getHTMLListeRecettes($request,$recettes,$texteRecette,8)}
END;
        return $res;
    }
}
?>