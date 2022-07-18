<?php
namespace Slurp\View;

use Slim\Routing\RouteContext;
use Slurp\Model\SlurpSite;
use Slurp\Utils\Crypt;

class GeneralView
{

    public function __construct()
    {
    }

    public function getHTMLHome($request)
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $res = <<<END
{$this->getHeader($request,[],true)}
        <div class="banner-area relative">
            <div class="overlay hero-overlay-bg"></div>
            <div class="container">
                <div class="row height align-items-center justify-content-center">
                    <div class="col-lg-7">
                        <div class="banner-content text-center">
                            <h1 class="text-uppercase text-white"><span>Slurp</span> <br> Les cocktails les plus
                                savoureux</h1>
                            <p class="text-white p-2 mb-30">
                                Vous pouvez découvrir et redécouvrir une multitude de recettes de cocktails et de
                                boissons non alcoolisées. <br>Des boissons fruités en passant par les vins et les
                                alcools forts, faites découvrir de nouveaux breuvages à vos amis et à votre famille !
                            </p>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="main-wrapper">

    <!-- Start about Area -->
    <section class="about-area pt-100 pb-100">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-6 col-md-12 col-sm-12 about-left">
                    <img class="img-fluid" src="{$routeParser->urlFor('img',["routes"=>"imgSlurp/cocktail-perso.jpg"])}" alt="">
                </div>
                <div class="col-lg-6 col-md-12 col-sm-12 about-right">
                    <span class="lnr lnr-sun"></span>
                    <h1 class="text-uppercase">

                        <span>Été</span> <br>
                        Ensoleillé
                    </h1>
                    <p>
                        <i>Slurp</i> est le site parfait pour préparer des boissons estivales pour vos meilleurs moments
                        autour de la piscine !
                    </p>
                </div>
            </div>
        </div>
    </section>
    <!-- End about Area -->

    <!-- Start feature Area -->
    <section class="feature-area pt-100 pb-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 d-flex flex-row">
                    <div class="single-feature col-12">
                        <h2 class="alone-title green-title">Slurp</h2>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex flex-row">
                    <div class="single-feature-img col-12">
                        <div style='background-image: url("{$routeParser->urlFor('img',["routes"=>"imgSlurp/cocktail-perso2.jpg"])}");' class="col-12">
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex flex-row">
                    <div class="single-feature col-12">
                        <h2 class="alone-title red-title">Slurp</h2>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex flex-row">
                    <div class="single-feature-img col-12">
                        <div style='background-image: url("{$routeParser->urlFor('img',["routes"=>"imgSlurp/cocktail-perso3.jpg"])}");' class="col-12">
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex flex-row">
                    <div class="single-feature col-12">
                        <h2 class="alone-title blue-title">Slurp</h2>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex flex-row">
                    <div class="single-feature-img col-12">
                        <div style='background-image: url("{$routeParser->urlFor('img',["routes"=>"imgSlurp/cocktail-perso4.jpg"])}");' class="col-12">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>    
{$this->getFooter($request,[],[])}
END;

        return $res;
    }

    public function getHead($request){
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $res = <<<END
<!DOCTYPE html>
<html lang="zxx" class="no-js">
<head>
    <!-- Mobile Specific Meta -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Favicon-->
    <link rel="shortcut icon" href="{$routeParser->urlFor('img',["routes"=>"imgSlurp/fav.png"])}">
    <!-- meta character set -->
    <meta charset="UTF-8">
    <!-- Site Title -->
    <title>Slurp</title>

    <link rel="icon" type="image/png" href="{$routeParser->urlFor('img',["routes"=>"imgSlurp/logo-onglet.png"])}" />
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,300,500,600" rel="stylesheet">
    <!--
    CSS
    ============================================= -->
    <link rel="stylesheet" href="{$routeParser->urlFor('css',["routes"=>"linearicons.css"])}">
    <link rel="stylesheet" href="{$routeParser->urlFor('css',["routes"=>"font-awesome.min.css"])}">
    <link rel="stylesheet" href="{$routeParser->urlFor('css',["routes"=>"nice-select.css"])}">
    <link rel="stylesheet" href="{$routeParser->urlFor('css',["routes"=>"magnific-popup.css"])}">
    <link rel="stylesheet" href="{$routeParser->urlFor('css',["routes"=>"bootstrap.css"])}">
    <link rel="stylesheet" href="{$routeParser->urlFor('css',["routes"=>"main.css"])}">
    <link rel="stylesheet" href="{$routeParser->urlFor('css',["routes"=>"perso.css"])}">
</head>
<body>
END;
        return $res;
    }

    public function getHeader($request,$args,$isHome=false)
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $rootUrl = $routeParser->urlFor('accueil');
        $classHome = ($isHome ? "" : "main-wrapper");
        $slurp=SlurpSite::getInstance();
        $isConnected=$slurp->getEmailConnected()!==false;
        $txtItemConnexion=$isConnected?"Mon profil":"Connexion";
        $RouteItemConnexion=$routeParser->urlFor($isConnected?"profil":"connexion");
        $res = <<<END
{$this->getHead($request)}
<div class="{$classHome} main-wrapper-first">
    <header>
        <div class="container">
            <div class="header-wrap">
                <div class="header-top d-flex justify-content-between align-items-center">
                    <div class="logo">
                        <a style="display:flex;align-items: center" href="{$rootUrl}">
                            <img style="max-height: 70px" src="{$routeParser->urlFor('img',["routes"=>"imgSlurp/logo.png"])}" alt="">
                            <h1 style='color:#aa0a5f;font-size:40px;margin-left:15px;display:inline-block;font-family:"Cherry"'>
                                Slurp</h1>
                        </a>
                    </div>
                    <div class="main-menubar d-flex align-items-center">
                        <nav class="hide">
                            <a href="{$rootUrl}">Accueil</a>
                            <a href="{$routeParser->urlFor("aliment")}">Ingrédients</a>
                            <a href="{$routeParser->urlFor("recherche-recette")}">Trouver une recette</a>
                            <a href="{$routeParser->urlFor("mes-recettes")}">Mes recettes</a>
                            <a href="{$RouteItemConnexion}">{$txtItemConnexion}</a>
                        </nav>
                        <div class="menu-bar"><span class="lnr lnr-menu"></span></div>
                    </div>
                </div>
            </div>
        </div>
    </header>
END;
        return $res;
    }

    public function getFooter($request, array $srcScript,$args){

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $slurp=SlurpSite::getInstance();
        $isConnected=$slurp->getEmailConnected()!==false;
        $txtItemConnexion=$isConnected?"Mon profil":"Connexion";
        $RouteItemConnexion=$routeParser->urlFor($isConnected?"profil":"connexion");
        $res = <<<END
    <section class="footer-area pt-60 pb-60">
        <div class="container">
            <div class="row d-flex justify-content-center">
                <ul class="footer-menu flex-wrap justify-content-center">
                    <li>
                        <a href="{$routeParser->urlFor('accueil')}">Accueil</a>
                    </li>
                    <li>
                        <a href="{$routeParser->urlFor('aliment')}">Ingrédients</a>
                    </li>
                    <li>
                        <a href="{$routeParser->urlFor("recherche-recette")}">Trouver une recette</a>
                    </li>
                    <li>
                        <a href="{$routeParser->urlFor("mes-recettes")}">Mes recettes</a>
                    </li>
                    <li>
                        <a href="{$RouteItemConnexion}">{$txtItemConnexion}</a>
                    </li>
                </ul>
            </div>
            <footer>
            </footer>
        </div>
    </section>
    <!-- End Footer Widget Area -->
</div>
{$this->getFoot($request,$srcScript)}
END;
        return $res;
    }

    public function getFoot($request,$srcScript){
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $res = <<<END
<script src="{$routeParser->urlFor('js',["routes"=>"vendor/jquery-2.2.4.min.js"])}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"
        integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4"
        crossorigin="anonymous"></script>
<script src="{$routeParser->urlFor('js',["routes"=>"vendor/bootstrap.min.js"])}"></script>
<script src="{$routeParser->urlFor('js',["routes"=>"jquery.ajaxchimp.min.js"])}"></script>
<script src="{$routeParser->urlFor('js',["routes"=>"jquery.nice-select.min.js"])}"></script>
<script src="{$routeParser->urlFor('js',["routes"=>"jquery.magnific-popup.min.js"])}"></script>
<script src="{$routeParser->urlFor('js',["routes"=>"waypoints.min.js"])}"></script>
<script src="{$routeParser->urlFor('js',["routes"=>"jquery.counterup.min.js"])}"></script>
<script src="{$routeParser->urlFor('js',["routes"=>"main.js"])}"></script>
END;
        foreach ($srcScript as $src){
            $res .= <<<END
<script src="{$src}"></script>
END;
        }
        $res .= <<<END
</body>
</html>
END;
        return $res;
    }
}

?>