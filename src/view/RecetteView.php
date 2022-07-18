<?php

namespace Slurp\View;

use Slim\Routing\RouteContext;
use Slurp\Controler\IdentificationControler;
use Slurp\Model\Categorie;
use Slurp\Model\Recette;
use Slurp\Model\RecetteManagerFactory;
use Slurp\Model\SlurpSite;
use Slurp\Utils\Crypt;
use Slurp\Utils\Utils;

class RecetteView
{

    public function __construct()
    {
    }

    public function getHTMLRecette($request, Recette $rct,$recettes)
    {
        $crypt = Crypt::getInstance();
        $utils = Utils::getInstance();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $listIngWithQuantity = $rct->getDescIngdts();
        $listIngdts = $rct->getIngdts();
        $titre = $rct->getTitre();
        $prep = $rct->getPreparation();

        $plurielRecettes=(count($recettes)>1?'s':'');
        $texteRecette=(empty($recettes)?'Pas de recette proche':"Recette".$plurielRecettes." proche".$plurielRecettes);

        $slurp=SlurpSite::getInstance();
        $email=$slurp->getEmailConnected();
        $isFavorite=$rct->isInFavorites($email);
        $styleDisplayAddFavorite=$isFavorite?"display: none; transform : rotate(360deg);":"display: block;";
        $styleDisplayCheckFavorite=$isFavorite?"display: block; transform : rotate(720deg);":"display: none;";

        $res = <<<END
<section>
        <div class="banner-area generic-banner generic-banner-1 pt-4 pb-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="align-self-center col-10 col-md-4 order-md-2 flex align-items-md-center">
                        <div class="w-100 blocImgRecette partieRecette">
END;
        $imgTitreUrl = $utils->getRctToImgInUrl($rct->getTitre());
        if (file_exists($_SERVER["ROOT_PATH"] ."/img/img/" . $imgTitreUrl)) {
            $imgRecette = $routeParser->urlFor('img', ["routes" => "img/" . $imgTitreUrl]);
        } else {
            $imgRecette = $routeParser->urlFor('img', ["routes" => "imgSlurp/cocktail-template.png"]);
        }
        $res .= <<<END
                            <img class="w-100 imgRecette" src="{$imgRecette}">
                        </div>
                    </div>
                    <div class="col-md-8 flex align-items-md-center">
                        <div>
                            <div class="mt-4 mt-md-0 container">
                                <div class="blocTitreRecette partieRecette p-3 mb-4 row text-center justify-content-center relative">
                                    <div class="btnAjoutFavorite h1 d-flex justify-content-center align-items-center">
                                        <img style="{$styleDisplayAddFavorite}" class="imgAddFavorite1" src="{$routeParser->urlFor('img', ["routes" => "imgSlurp/add.png"])}" alt="">
                                        <img style="{$styleDisplayCheckFavorite}" class="imgAddFavorite2" src="{$routeParser->urlFor('img', ["routes" => "imgSlurp/check.png"])}" alt="">
                                    </div>
                                    <h1 class="titreDegradCyanMagenta col-12 text-uppercase mb-2">{$titre}</h1>
                                </div>
                            </div>
                            <div class="mt-4 mt-md-0 container">
                                <div class="blocIngredientsRecette partieRecette p-3 row text-center justify-content-center">
                                    <h1 class="titreJauneorangeRouge col-12 text-uppercase mb-2">Ingrédients</h1>
END;
        for ($i = 0; $i < count($listIngWithQuantity); $i++) {
            $res .= <<<END
                <div class="col-md-6">
                   <a href="{$routeParser->urlFor('ingredient', ["ingdt" => $crypt->encrypt($listIngdts[$i])])}" class="d-block btnIngredient mt-2 pt-2 pb-2 w-100">$listIngWithQuantity[$i]</a>
                </div>
END;

        }
        $res .= <<<END
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="container mt-40">
                        <div class="partieRecette p-3 row  justify-content-center">
                            <h1 class="titeVertfonceVertclair col-12 text-center text-uppercase mb-2">Préparation</h1>
                            <div class="preparationRecette">
                                {$prep}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
     </section>
        {$this->getHTMLListeRecettes($request,$recettes,$texteRecette,8)}

END;
        return $res;
    }

    public function getHTMLListeRecettes($request, array $recettes,$texteRecette,$nMaxRecettes)
    {
        $crypt=Crypt::getInstance();
        $utils=Utils::getInstance();
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $rootRecette = $routeParser->urlFor('recette', ["rct" => ""]);

        $res=<<<END
<!--        Partie sur les recettes-->
    <section>
        <div class="banner-area generic-banner generic-banner-3">
            <div class="container">
                <div class="row mt-40">
                    <div class="mt-4 mt-md-0 col-md-12">
                        <div class="p-5 row text-center justify-content-center">
                            <h1 class="text-white w-100 text-uppercase">{$texteRecette}</h1>
END;
        $nRecettes=0;
        foreach ($recettes as $r){
            if($nRecettes>=$nMaxRecettes){
                break;
            }
            $titre= $r->getTitre();
            $imgTitreUrl=$utils->getRctToImgInUrl($r->getTitre());
            if (file_exists($_SERVER["ROOT_PATH"] ."/img/img/" . $imgTitreUrl)) {
                $imgRecette = $routeParser->urlFor('img', ["routes" => "img/" . $imgTitreUrl]);
            } else {
                $imgRecette = $routeParser->urlFor('img', ["routes" => "imgSlurp/cocktail-template.png"]);
            }
            $res.=<<<END
<div class="mt-4 col-12 col-md-6 col-lg-3">
                                <a href="{$rootRecette}{$crypt->encrypt($titre)}">
                                    <div class="partieIngredient carteRecetteDansIngredient">
                                        {$titre}
                                        <hr>
                                        <div class="imgRecetteDansIngredient"
                                             style='background-image: url("{$imgRecette}");'></div>
                                    </div>
                                </a>
                            </div>
END;
            $nRecettes++;
        }
        $res.=<<<END
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
END;
        return $res;
    }

    public function getHTMLTrouverRecette($request,$recettes)
    {
        $texteRecette="Idées de recettes";

        $res=<<<END
<section>
    <div class="banner-area generic-banner generic-banner-4">
        <div class="container">
            <div class="row justify-content-center generic-height align-items-center">
                <div class="col-lg-12">
                    <div class="banner-content text-center">
                        <div class="row justify-content-center">
                            <h1 class="text-white text-uppercase">Recherchez la recette parfaite</h1>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-lg-6 inputIngdt inputIngdtWith">
                                <div>
                                    <div class="add-ingdt relative">

                                        <select class="ingdtInputWith form-control autoSelectWith" name="ingdtWith"
                                                placeholder="Ingrédient désiré"
                                                onfocus="this.placeholder = ''"
                                                onblur="this.placeholder = 'Ingrédient désiré'"
                                                autocomplete="off"></select>

                                        <div style="position: absolute; left: -5000px;">
                                            <input type="text" name="b_36c4fd991d266f23781ded980_aefe40901a"
                                                   tabindex="-1" value="">
                                        </div>
                                        <button class="btn-ajouter-ingdtWith primary-btn btn-style-positif hover d-inline-flex align-items-center">
                                            <span class="mr-10">Ajouter</span>
                                            <span
                                                    class="lnr lnr-arrow-right">

                                        </span>
                                        </button>
                                        <div class="info"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 inputIngdt inputIngdtWithout">
                                <div>
                                    <div class="add-ingdt relative">
                                        <select class="ingdtInputWith form-control autoSelectWithout"
                                                name="ingdtWithout" placeholder="Ingrédient non désiré"
                                                onfocus="this.placeholder = ''"
                                                onblur="this.placeholder = 'Ingrédient non désiré'"
                                                autocomplete="off"></select>

                                        <div style="position: absolute; left: -5000px;">
                                            <input type="text" name="b_36c4fd991d266f23781ded980_aefe40901a"
                                                   tabindex="-1" value="">
                                        </div>
                                        <button class="btn-ajouter-ingdtWithout primary-btn btn-style-negatif hover d-inline-flex align-items-center">
                                            <span class="mr-10">Ajouter</span>
                                            <span
                                                    class="lnr lnr-arrow-right">

                                        </span>
                                        </button>
                                        <div class="info"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-center mt-4">
                            <div class="mt-4 mt-md-0 col-6">
                                <div class=" w-100 text-center justify-content-center listIngdtWith">

                                </div>
                            </div>
                            <div class="mt-4 mt-md-0 col-6">
                                <div class=" w-100 text-center justify-content-center listIngdtWithout">

                                </div>
                            </div>
                        </div>

                        <div class="row justify-content-center mt-4 d-flex flex-column justify-content-center align-content-center align-items-center">

                            <div class="pl-3 pr-3 pt-2 pb-2 switchGroupSearchCateg switch-wrap d-flex justify-content-center">
                                <div class="mr-2">Recherche avec des catégories d'ingrédients</div>
                                <div class="primary-switch">
                                    <input type="checkbox" id="default-switch">
                                    <label for="default-switch"></label>
                                </div>
                            </div>

                            <button class="h4 col-8 col-md-4 primary-btn btn-valider-search-recette hover d-flex align-items-center justify-content-center align-content-center text-dark">
                                <span class="mr-10">Effectuer la recherche</span>
                                <span class="lnr lnr-arrow-right"> </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="mt-40">
    <div class="listAffichRecettes container d-flex flex-column">
    </div>
</section>
{$this->getHTMLListeRecettes($request,$recettes,$texteRecette,count($recettes))}
END;
        return $res;
    }

    public function getHTMLRecetteFavorite($request,$rcts,$recettes)
    {
        $texteRecette="Idées de recettes";
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $utils=Utils::getInstance();
        $crypt=Crypt::getInstance();

        $res=<<<END
<section>
        <div class="banner-area generic-banner generic-banner-6">
            <div class="container">
                <div class="row justify-content-center generic-height align-items-center">
                    <div class="col-lg-8">
                        <div class="banner-content text-center">
                            <h1 class="text-white text-uppercase">Mes recettes favorites</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt-40">
END;
        if(empty($rcts)){
            $res.=<<<END
                        <div class="row text-center">
                            <div class="col-12">
                                <h4>Pas de recettes favorites</h4>
                                <p>Vous pouvez ajouter des recettes à votre liste de recettes préférées sur la page d'une recette</p>
                            </div>
                        </div>
END;
        } else {
            $res.=<<<END
            <div class="row">
                <div class="col-12 mt-3">
                    <div class="p-4 text-center">
                        <div class="row">
                            <div class="h5 d-none d-md-block col-2">
                                Illustration de la recette
                            </div>
                            <div class="h5 col-6 col-md-5 vertLineLeft-md border-grey">
                                Intitulé de la recette
                            </div>
                            <div class="h5 col-6 col-md-5 vertLineLeft border-grey">
                                Date à laquelle vous avez ajouté cette recette
                            </div>
                        </div>
                    </div>
                </div>
            </div>
END;
            foreach ($rcts as $rct){
                $imgTitreUrl=$utils->getRctToImgInUrl($rct["titre"]);
                $imgTemplateClass="";
                if (file_exists($_SERVER["ROOT_PATH"] ."/img/img/" . $imgTitreUrl)) {
                    $imgRecette = $routeParser->urlFor('img', ["routes" => "img/" . $imgTitreUrl]);
                } else {
                    $imgTemplateClass="p-2";
                    $imgRecette = $routeParser->urlFor('img', ["routes" => "imgSlurp/cocktail-template.png"]);
                }
                $jour=date("d",strtotime($rct['date']));
                $mois= $utils->numMonthTOStrMonthFR(date("m",strtotime($rct['date'])));
                $annee=date("Y",strtotime($rct['date']));
                $date=$jour." ".$mois." ".$annee;
                $res.=<<<END
            <div class="row">
                <div class="col-12 mt-3">
                    <div onclick="location.href='{$routeParser->urlFor('recette', ["rct" => $crypt->encrypt($rct["titre"])])}'" class="p-4 carteRecetteFavorite linGradRecetteFavorite">
                        <div class="row">
                            <div class="h4 d-none d-md-flex col-2 mt-4 mt-md-0 border-white flex-row justify-content-center align-items-center ">
                                <div class="p-3 bgImgRecetteFavorite">
                                    <img class="imgRecetteFavorite {$imgTemplateClass}" src="{$imgRecette}">
                                </div>
                            </div>
                            <div class="divCarteRecette vertLineLeft-md h4 col-6 col-md-5  d-flex justify-content-center align-items-center flex-column">
                                {$rct["titre"]}
                            </div>
                            <div class="divCarteRecette h4 col-6 col-md-5 vertLineLeft border-white d-flex flex-row justify-content-center align-items-center ">
                                <div>
                                    {$date}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
END;
            }

        }
        $res.=<<<END
        </div>
    </section>
{$this->getHTMLListeRecettes($request,$recettes,$texteRecette,count($recettes))}
END;
        return $res;
    }
}