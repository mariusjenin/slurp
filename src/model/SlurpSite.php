<?php


namespace Slurp\Model;

use DI\Container;
use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Slurp\Controler\GeneralControler;
use Slurp\Controler\IdentificationControler;
use Slurp\Controler\IngredientControler;
use Slurp\Controler\RecetteControler;
use Psr\Http\Message\ServerRequestInterface;
use Slurp\Utils\Crypt;
use Throwable;
use Slim\Exception\HttpNotFoundException;

//Représente le site (avec son système de routage et de DB)
class SlurpSite
{
    private static $_instance;

    private $app;
    private $db;

    private function __construct()
    {
        $container = new Container();
        AppFactory::setContainer($container);

        $app = AppFactory::create();
        // Add Slim routing middleware
        $app->addRoutingMiddleware();

        // Set the base path to run the app in a subdirectory.
        // This path is used in urlFor().
        $app->add(new BasePathMiddleware($app));

        $slurp=$this;

        // Define Custom Error Handler
        $notFoundhandler = function (
//            ServerRequestInterface $request,
//            Throwable $exception,
//            bool $displayErrorDetails,
//            bool $logErrors,
//            bool $logErrorDetails
        ) use($slurp) {
            //On redirige la page vers l'url de redirection en cas de not found
            $url=$slurp->getUrlRedirect();
            $response = $slurp->app->getResponseFactory()->createResponse();
            $routeParser=$slurp->app->getRouteCollector()->getRouteParser();
//            return $response->withHeader('Location', $routeParser->urlFor($url["route"],$url["param"]));
            return $response->withHeader('Location', $routeParser->urlFor("accueil",$url["param"]));
        };

        // Add Error Middleware
        $errorMiddleware = $app->addErrorMiddleware(true, true, true);
        // On enregistre un errorMiddleware pour catch les notFound
        $errorMiddleware->setErrorHandler(HttpNotFoundException::class, $notFoundhandler);

        $this->app=$app;

        $this->db=new DataBase();
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            $_SERVER["ROOT_PATH"] = __DIR__ . "/../..";
            self::$_instance = new SlurpSite();
        }
        return self::$_instance;
    }


    //Routes du site
    public function addRoutes()
    {

        //Obligé de mettre l'instance dans une variable pour la passer en "use" des methodes de routage
        $slurp=$this;

        $this->app->add(function ($request, $handler) {
            $response = $handler->handle($request);
            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->withHeader('X-Content-Type-Options', 'nosniff');
        });

        //Accueil
        $this->app->get('/accueil', function (Request $request, Response $response, $args) use ($slurp) {
            $slurp->refreshCookieUrlRedirect($request,$args);
            $genCont = new GeneralControler($request, $args);

            $response->getBody()->write($genCont->getHome());
            return $response;
        })->setName('accueil');

        //Aliment
        $this->app->get('/aliment', function (Request $request, Response $response, $args) use ($slurp) {
            $slurp->refreshCookieUrlRedirect($request,$args);
            $genCont = new GeneralControler($request, $args);
            $ingCont = new IngredientControler($request, $args);
            $response->getBody()->write($genCont->getHeader() . $ingCont->getHTMLAliment() . $genCont->getFooter([]));
            return $response;
        })->setName('aliment');

        //Ingrédient
        $this->app->get('/ingredient/{ingdt}', function (Request $request, Response $response, $args) use ($slurp) {
            $slurp->refreshCookieUrlRedirect($request,$args);
            $genCont = new GeneralControler($request, $args);
            $ingCont = new IngredientControler($request, $args);
            $response->getBody()->write($genCont->getHeader() . $ingCont->getHTMLIngredient() . $genCont->getFooter([]));
            return $response;
        })->setName('ingredient');

        //Recette
        $this->app->get('/recette/{rct}', function (Request $request, Response $response, $args) use ($slurp) {
            $slurp->refreshCookieUrlRedirect($request,$args);
            $genCont = new GeneralControler($request, $args);
            $rctCont = new RecetteControler($request, $args);
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $response->getBody()->write($genCont->getHeader() . $rctCont->getHTMLRecette() . $genCont->getFooter([$routeParser->urlFor('js', ["routes" => "jsRctFavorite.js"])]));
            return $response;
        })->setName('recette');

        //RechercheRecette
        $this->app->get('/recherche-recette', function (Request $request, Response $response, $args) use ($slurp) {
            $slurp->refreshCookieUrlRedirect($request,$args);
            $genCont = new GeneralControler($request, $args);
            $rctCont = new RecetteControler($request, $args);
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $response->getBody()->write($genCont->getHeader() . $rctCont->getHTMLTrouverRecette() . $genCont->getFooter(["https://cdn.jsdelivr.net/gh/xcash/bootstrap-autocomplete@v2.3.7/dist/latest/bootstrap-autocomplete.min.js", $routeParser->urlFor('js', ["routes" => "jsSearchRecette.js"])]));
            return $response;
        })->setName('recherche-recette');

        //Recettes Favorites
        $this->app->get('/mes-recettes', function (Request $request, Response $response, $args) use ($slurp) {
            $slurp->refreshCookieUrlRedirect($request,$args);
            $genCont = new GeneralControler($request, $args);
            $rctCont = new RecetteControler($request, $args);
            $response->getBody()->write($genCont->getHeader() . $rctCont->getHTMLRecetteFavorite() . $genCont->getFooter([]));
            return $response;
        })->setName('mes-recettes');

        //Profil
        $this->app->get('/profil', function (Request $request, Response $response, $args) use ($slurp) {
            $genCont = new GeneralControler($request, $args);
            $identCont = new IdentificationControler($request, $args);
            if($slurp->getEmailConnected()!=false) {
                $response->getBody()->write($genCont->getHeader() . $identCont->getHTMLProfil() . $genCont->getFooter([]));
                return $response;
            } else {
                $routeParser = RouteContext::fromRequest($request)->getRouteParser();
                $url=$slurp->getUrlRedirect();
                return $response->withHeader('Location', $routeParser->urlFor($url["route"],$url["param"]));
            }
        })->setName('profil');

        //Profil Post
        $this->app->post('/profil', function (Request $request, Response $response, $args) use ($slurp) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $identCont=new IdentificationControler($request,$args);
            if($slurp->getEmailConnected()!=false) {
                $url=$identCont->modifierProfil();
            } else {
                $url=$slurp->getUrlRedirect();
            }
            return $response->withHeader('Location', $routeParser->urlFor($url["route"],$url["param"]));
        })->setName('profil');

        //Connexion
        $this->app->get('/connexion', function (Request $request, Response $response, $args) use ($slurp) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $identCont = new IdentificationControler($request, $args);
            if($slurp->getEmailConnected()==false) {
                $genCont = new GeneralControler($request, $args);
                $response->getBody()->write($genCont->getHead() . $identCont->getHTMLIdentification(false) . $genCont->getFoot([]));
                return $response;
            } else {
                $url=$slurp->getUrlRedirect();
                return $response->withHeader('Location', $routeParser->urlFor($url["route"],$url["param"]));
            }
        })->setName('connexion');


        //Connexion Post
        $this->app->post('/connexion', function (Request $request, Response $response, $args) use ($slurp) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $identCont=new IdentificationControler($request,$args);
            if($slurp->getEmailConnected()==false) {
                $url=$identCont->connecter();
            } else {
                $url=$slurp->getUrlRedirect();
            }
            return $response->withHeader('Location', $routeParser->urlFor($url["route"],$url["param"]));
        })->setName('connexion');
        
        //Inscription
        $this->app->get('/inscription', function (Request $request, Response $response, $args) use ($slurp) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $identCont=new IdentificationControler($request,$args);
            if($slurp->getEmailConnected()==false) {
                $genCont = new GeneralControler($request, $args);
                $response->getBody()->write($genCont->getHead() . $identCont->getHTMLIdentification(true) . $genCont->getFoot([]));
                return $response;
            } else {
                $url=$slurp->getUrlRedirect();
                return $response->withHeader('Location', $routeParser->urlFor($url["route"],$url["param"]));
            }
        })->setName('inscription');

        //Inscription Post
        $this->app->post('/inscription', function (Request $request, Response $response, $args) use ($slurp) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $identCont=new IdentificationControler($request,$args);
            if($slurp->getEmailConnected()==false) {
                $url=$identCont->inscrire();
            } else {
                $url=$slurp->getUrlRedirect();
            }
            return $response->withHeader('Location', $routeParser->urlFor($url["route"],$url["param"]));
        })->setName('inscription');

        //Deconnexion (maj variable de session)
        $this->app->get('/deconnexion', function (Request $request, Response $response, $args) use ($slurp) {
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $slurp->deconnect();
            $url=$slurp->getUrlRedirect();
            return $response->withHeader('Location', $routeParser->urlFor($url["route"],$url["param"]));
        })->setName('deconnexion');


        //route pour les fichiers js
        $this->app->get('/js/{routes:.+}', function (Request $request, Response $response, $args) {
            $response->getBody()->write(file_get_contents("../js/" . $args['routes'] . ""));
            return $response->withHeader('Content-Type', 'text/javascript');
        })->setName('js');

        //Route pour les images
        $this->app->get('/img/{routes:.+}', function (Request $request, Response $response, $args) {
            echo file_get_contents("../img/" . $args['routes'] . "");
            return $response;
        })->setName('img');

        //Route pour les fonts
        $this->app->get('/fonts/{routes:.+}', function (Request $request, Response $response, $args) {
            echo file_get_contents("../fonts/" . $args['routes'] . "");
            return $response;
        })->setName('fonts');

        //Route pour le css
        $this->app->get('/css/{routes:.+}', function (Request $request, Response $response, $args) {
            $response->getBody()->write(file_get_contents("../css/" . $args['routes'] . ""));
            return $response->withHeader('Content-Type', 'text/css');
        })->setName('css');

        //Routes pour les requetes ajax (json)
        $this->app->map(['GET', 'POST'],'/ajax/{routes:.+}', function (Request $request, Response $response, $args) {
            include "../src/ajax/" . $args['routes'] . "";
            return $response->withHeader('Content-Type', 'application/json');
        });

    }

//    //Ajoute les routes pour les tests (n'est jamais appelé dans la version finale du site)
//    public function addRoutesTest()
//    {
//
//        //False root
//        $this->app->get('/root', function (Request $request, Response $response) {
//
//            $response->getBody()->write(
//                '<h1>ROOT</h1>' .
//                '<a style="display: block" href="accueil">Site</a>' .
//                '<a style="display: block" href="test">test</a>' .
//                '<a style="display: block" href="test2">test2</a>' .
//                '<a style="display: block" href="test3">test3</a>' .
//                '<a style="display: block" href="test4">test4</a>' .
//                '<a style="display: block" href="test5">test5</a>' .
//                '<a style="display: block" href="templatemodif/index.html">TemplateModif</a>' .
//                '<a style="display: block" href="template/index.html">TemplateYaseen</a>'
//            );
//            return $response;
//        })->setName('root');
//
//        //test
//        $this->app->get('/test', function (Request $request, Response $response) {
//            $response->getBody()->write("<h1>TEST</h1>" .
//                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/root';\">Return to ROOT</button>"
//            );
//            $titre = "Sucre roux";
//            $response->getBody()->write("<p>Recherche d'une Catégorie avec le titre : <strong>" . $titre . "</strong></p>");
//            $categ = CategorieManagerFactory::getInstance()->searchCategByTitre($titre);
//            $response->getBody()->write("" . $categ);
//            $response->getBody()->write($categ->getTitre() . " est un Ingrédient ? " . ($categ->is_Ingredient() ? "true" : "false"));
//
//            $str = "Sucre";
//            $catFact = CategorieManagerFactory::getInstance();
//            $response->getBody()->write("<ul><p>Recherche de Catégories ayant <strong>\"$str\"</strong> dans leur titre</p>");
//
//            foreach ($catFact->searchCategsLike($str) as $categ) {
//                $response->getBody()->write("<li>" . $categ->getTitre() . "</li>");
//            }
//            $response->getBody()->write("</ul>");
//
//            return $response;
//        })->setName('test');
//
//        //test2
//        $this->app->get('/test2', function (Request $request, Response $response) {
//            $response->getBody()->write("<h1>TEST2</h1>" .
//                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/root';\">Return to ROOT</button>"
//            );
//            $catFact = CategorieManagerFactory::getInstance();
//            $categ = $catFact->searchCategByTitre("Sucre");
//            $aliment = $catFact->searchCategByTitre("Aliment");
//
//            $response->getBody()->write("" . $categ);
//            $response->getBody()->write("" . $aliment);
//            $response->getBody()->write("<div>" . $categ->getTitre() . " sousCateg de " . $aliment->getTitre() . " : " . ($catFact->isSousCategOf($categ->getTitre(), $aliment->getTitre()) ? "true" : "false") . "</div>");
//            $response->getBody()->write("<div>" . $aliment->getTitre() . " superCateg de " . $categ->getTitre() . " : " . ($catFact->isSuperCategOf($aliment->getTitre(), $categ->getTitre()) ? "true" : "false") . "</div>");
//            $response->getBody()->write("<div>" . $categ->getTitre() . " superCateg de " . $aliment->getTitre() . " : " . ($catFact->isSuperCategOf($categ->getTitre(), $aliment->getTitre()) ? "true" : "false") . "</div>");
//            $response->getBody()->write("<div>" . $aliment->getTitre() . " sousCateg de " . $categ->getTitre() . " : " . ($catFact->isSousCategOf($aliment->getTitre(), $categ->getTitre()) ? "true" : "false") . "</div>");
//
//            return $response;
//        })->setName('test2');
//
//        //test3
//        $this->app->get('/test3', function (Request $request, Response $response) {
//            $response->getBody()->write("<h1>TEST3</h1>" .
//                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/root';\">Return to ROOT</button>"
//            );
//            $titre = "Margarita";
//            $response->getBody()->write("<p>Recherche d'une Recette avec le titre : <strong>" . $titre . "</strong></p>");
//            $recFact = RecetteManagerFactory::getInstance();
//            $recette = $recFact->searchRecetteByTitre($titre);
//            $response->getBody()->write("" . $recette);
//
//            $recFact = RecetteManagerFactory::getInstance();
//            $response->getBody()->write("<ul><p>Recherche de Recettes ayant <strong>\"$titre\"</strong> dans leur titre</p>");
//
//            foreach ($recFact->searchRecettesLike($titre) as $rct) {
//                $response->getBody()->write("<li>" . $rct->getTitre() . "</li>");
//            }
//            $response->getBody()->write("</ul>");
//
//
//            return $response;
//        })->setName('test3');
//
//        //test4
//        $this->app->get('/test4', function (Request $request, Response $response) {
//            $response->getBody()->write("<h1>TEST4</h1>" .
//                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/root';\">Return to ROOT</button>"
//            );
//            $avec = ['Sucre roux', "Champagne"];
//            $sans = ["Pomme"];
//            $recFact = RecetteManagerFactory::getInstance();
//            $recettes = $recFact->searchRecettesByIngdts($avec, $sans, false, true, []);
//            $response->getBody()->write("<ul><p>Recherche" . (empty($avec) ? "" : " avec <strong>" . implode("</strong>, <strong>", $avec) . "</strong>") . (empty($avec) || empty($sans) ? "" : " et") . (empty($sans) ? "" : " sans <strong>" . implode("</strong>, <strong>", $sans)) . "</strong></p>");
//            foreach ($recettes as $recette) {
//                $response->getBody()->write("<li><div style='background-color:green;display: inline-block;padding: 5px;color: white;margin: 4px;margin-right: 10px;text-align: center;min-width: 30px;'>" . count($recette["ingdtsAvec"]) . "</div><div style='background-color:red;display: inline-block;padding: 5px;color: white;margin: 4px;margin-right: 10px;text-align: center;min-width: 30px;'>" . count($recette["ingdtsSans"]) . "</div>" . $recette["recette"]->getTitre() . "</li>");
//            }
//            $response->getBody()->write("</ul>");
//            return $response;
//        })->setName('test4');
//
//        //test5
//        $this->app->get('/test5', function (Request $request, Response $response) {
//            $response->getBody()->write("<h1>TEST5</h1>" .
//                "<button onclick=\"window.location.href = '" . $request->getAttributes()['__basePath__'] . "/root';\">Return to ROOT</button>"
//            );
//            $recFact = RecetteManagerFactory::getInstance();
//            $nbr = 10;
//            $recettes = $recFact->searchRecettesRandom($nbr);
//            $response->getBody()->write("<ul><p>Recherche de <strong>" . $nbr . "</strong> recettes aléatoires</p>");
//            foreach ($recettes as $recette) {
//                $response->getBody()->write("<li>" . $recette->getTitre() . "</li>");
//            }
//            $response->getBody()->write("</ul>");
//            return $response;
//        })->setName('test5');
//
//
//    }

//    public function addRoutesTemplateModif()
//    {
//        //css
//        $this->app->get('/templatemodif/css/{routes:.+}', function (Request $request, Response $response, $args) {
//            $response->getBody()->write(file_get_contents("../css/" . $args['routes'] . ""));
//            return $response->withHeader('Content-Type', 'text/css');
//        });
//
//        //js
//        $this->app->get('/templatemodif/js/{routes:.+}', function (Request $request, Response $response, $args) {
//            $response->getBody()->write(file_get_contents("../js/" . $args['routes'] . ""));
//            return $response->withHeader('Content-Type', 'text/javascript');
//        });
//
//        //ajax(json)
//        $this->app->get('/templatemodif/ajax/{routes:.+}', function (Request $request, Response $response, $args) {
//            include "../src/ajax/" . $args['routes'] . "";
//            return $response->withHeader('Content-Type', 'application/json');
//        });
//
//        //templatemodif
//        $this->app->get('/templatemodif/{routes:.+}', function (Request $request, Response $response, $args) {
//            echo file_get_contents("../templatemodif/" . $args['routes'] . "");
//            return $response;
//        })->setName('yaseen');
//    }

    public function run()
    {
        // Run app
        $this->app->run();
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @return DataBase
     */
    public function getDb(): DataBase
    {
        return $this->db;
    }

    //On stocke l'email de l'utilisateur connecté dans la variable de session
    public function setEmailConnected($email){
        if(!isset($_SESSION)){
            session_start();
        }
        $_SESSION["email"] = $email;
    }

    public function getEmailConnected(){
        if(!isset($_SESSION)){
            session_start();
        }
        if(isset($_SESSION["email"])){
            return $_SESSION["email"];
        }
        return false;
    }

    public function deconnect(){
        if(!isset($_SESSION['email'])){
            session_start();
        }
        $_SESSION['email'] = "";
        unset($_SESSION['email']);
    }

    //On stocke toujours la page de redirection si l'utilisateur veut se connecter ou se deconnecter par exemple
    public function refreshCookieUrlRedirect(Request $request,$args){
        if(!isset($_SESSION)){
            session_start();
        }
        $_SESSION["urlRedirect"]=["route"=>$request->getAttribute("__route__")->getName(),"param"=>$args];
    }

    //Getter de l'url de redirection (stocké dans la session)
    public function getUrlRedirect(){
        if(!isset($_SESSION)){
            session_start();
        }
        if(isset($_SESSION["urlRedirect"])){
            return $_SESSION["urlRedirect"];
        }
        return ["route"=>"accueil","param"=>[]];
    }
}