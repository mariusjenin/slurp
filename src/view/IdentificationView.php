<?php
namespace Slurp\View;

use Slim\Routing\RouteContext;
use Slurp\Model\SlurpSite;
use Slurp\Model\User;
use Slurp\Utils\Crypt;

class IdentificationView
{

    public function __construct()
    {
    }


    public function getHTMLIdentification($request,$value,$inscription)
    {
        $erreur=$value!=null;
        $identification=$inscription?"inscription":"connexion";
        $invIdentification=$inscription?"connexion":"inscription";
        $txtSwitchIdentification=$inscription?"Vous possédez déjà un compte ? Connectez-vous ici :":"Vous ne possédez pas de compte ? Inscrivez-vous ici :";
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $cookieUrlRedirect=SlurpSite::getInstance()->getUrlRedirect();
        $erreurAffich=$erreur?'<div class="itemForm100 text-center pl-3 pr-3 text-danger">Nous n\'avons pas réussi à vous '.($inscription?'inscrire':'connecter').'</div>':'';

        $classForm=$identification."Form";
        $classBG=$inscription?"bgInscription":"bgConnexion";
        $titre=ucfirst($identification);

        if($inscription) {
            $secondPwd = <<<END
                            <label class="m-1 flex-1">
                    <div class="text-dark">Répétez votre mot de passe</div>
                    <input type="password" name="password2" placeholder="Validez votre mot de passe"
                           onfocus="this.placeholder = ''" onblur="this.placeholder = 'Validez votre mot de passe'"
                           required class="mt-10 single-input" minlength="4">
                </label>
END;
        } else {
            $secondPwd ="";
        }


        $res = <<<END
<div class="container-fluid {$classBG} d-flex justify-content-center align-items-center flex-column">
    <div class="identificationBox">
        <div class="titreIdentification h1 p-3">
            <a href="{$routeParser->urlFor($cookieUrlRedirect["route"],$cookieUrlRedirect["param"])}" class="mr-3 ml-1 goBackIdentification">
                <img src="{$routeParser->urlFor('img', ["routes" => "imgSlurp/previous.png"])}">
            </a>
            {$titre}
        </div>
        <form class="d-flex justify-content-around flex-column align-content-between {$classForm} p-4" action="{$routeParser->urlFor($identification)}" method="post">
            <div class="itemForm100 text-center pl-3 pr-3">
                {$txtSwitchIdentification}
                <a href="{$routeParser->urlFor($invIdentification)}" class="ml-2 switchConnexionInscription">
                    <img src="{$routeParser->urlFor('img', ["routes" => "imgSlurp/right.png"])}">
                </a>
            </div>
            {$erreurAffich}
           <label class="d-flex flex-column">
                <div class="text-dark">Email</div>
                <input type="email" name="email" placeholder="Votre adresse email" onfocus="this.placeholder = ''"
                       onblur="this.placeholder = 'Votre adresse email'" value="{$value["email"]}" required class="mt-10 single-input">
            </label>
END;
        if($inscription){
            $res.=<<<END
            <div class="d-flex">
                <label class="d-flex flex-column flex-1 m-1">
                    <div class="text-dark">Prénom</div>
                    <input type="text" name="prenom" placeholder="Votre prénom" onfocus="this.placeholder = ''"
                           onblur="this.placeholder = 'Votre prénom'" value="{$value["prenom"]}}" class="mt-10 single-input" minlength="1">
                </label>
                <label class="d-flex flex-column flex-1 m-1">
                    <div class="text-dark">Nom</div>
                    <input type="text" name="nom" placeholder="Votre nom" onfocus="this.placeholder = ''"
                           onblur="this.placeholder = 'Votre nom'" value="{$value["nom"]}" class="mt-10 single-input" minlength="1">
                </label>
            </div>

            <div class="d-flex">
                <label class="d-flex flex-column flex-1">
                    <div class="text-dark">Sexe</div>
                    <div class="d-flex align-items-end justify-content-end mt-15">
                        <label class="d-flex flex-1 m-1 align-items-center justify-content-center"
                               onclick='if($("input[name=\"femme\"]").prop("checked")===false)$("input[name=\"homme\"]").prop("checked",false)'>
                            <div class="text-dark d-inline-block">Femme</div>
                            <div class="confirm-checkbox ml-2">
                                <input type="checkbox" id="femmmeCB" name="femme" {$value["femme"]}>
                                <label for="femmmeCB"></label>
                            </div>
                        </label>
                        <label class="d-flex flex-1 m-1 align-items-center justify-content-center"
                               onclick='if($("input[name=\"homme\"]").prop("checked")===false)$("input[name=\"femme\"]").prop("checked",false)'>
                            <div class="text-dark d-inline-block">Homme</div>
                            <div class="confirm-checkbox ml-2">
                                <input type="checkbox" id="hommeCB" name="homme" {$value["homme"]}>
                                <label for="hommeCB"></label>
                            </div>
                        </label>
                    </div>
                </label>
                <label class="d-flex flex-column flex-1 m-1">
                    <div class="">Date de naissance</div>
                    <input type="date" name="dateNaiss" placeholder="Votre date de naissance" onfocus="this.placeholder = ''"
                           onblur="this.placeholder = 'Votre date de naissance'" class="mt-10 single-input" value="{$value["dateNaiss"]}">
                </label>
            </div>


            <label class="d-flex flex-column">
                <div class="text-dark">Adresse</div>
                <input type="text" name="adresse" placeholder="Votre adresse" onfocus="this.placeholder = ''"
                       onblur="this.placeholder = 'Votre adresse'" class="mt-10 single-input" value="{$value["adresse"]}" minlength="1">
            </label>
            <div class="d-flex">
                <label class="d-flex flex-column flex-1 m-1">
                    <div class="text-dark">Code postal</div>
                    <input type="text" name="codepostal" placeholder="Votre code postal"
                           onfocus="this.placeholder = ''" onblur="this.placeholder = 'Votre code postal'"
                           class="mt-10 single-input" value="{$value["codepostal"]}"  pattern="[0-9]{5}">
                </label>

                <label class="d-flex flex-column flex-1 m-1">
                    <div class="text-dark">Ville</div>
                    <input type="text" name="ville" placeholder="Votre ville" onfocus="this.placeholder = ''"
                           onblur="this.placeholder = 'Votre ville'" class="mt-10 single-input" value="{$value["ville"]}" minlength="1">
                </label>
            </div>
END;

        }
        $res.=<<<END
              <div class="d-flex flex-row">
                <label class="m-1 flex-1">
                    <div class="text-dark">Mot de passe</div>
                    <input type="password" name="password" placeholder="Votre mot de passe"
                           onfocus="this.placeholder = ''" onblur="this.placeholder = 'Votre mot de passe'" required
                           class="mt-10 single-input" minlength="4">
                </label>
            {$secondPwd}
            </div>
            <div class="itemForm100 text-center">
                <button class="submitIdentification" type="submit"><img src="{$routeParser->urlFor('img', ["routes" => "imgSlurp/check.png"])}"></button>
            </div>
        </form>
    </div>
</div>
END;
        return $res;
    }

    public function getHTMLProfil($request,$value,$erreur)
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $erreurAffich=$erreur?'<div class="itemForm100 text-center pl-3 pr-3 mb-20 text-danger">Nous n\'avons pas réussi à modifier votre profil</div>':'';

        $res = <<<END
 <section>
        <div class="banner-area generic-banner generic-banner-7">
            <div class="container">
                <div class="row justify-content-center generic-height align-items-center relative">
                    <div class="col-lg-8">
                        <div class="banner-content text-center">
                            <h1 class="text-white text-uppercase">Mon profil</h1>
                        </div>
                    </div>
                    <div onclick="location.href='{$routeParser->urlFor("deconnexion")}'" class="text-white text-uppercase btnDeconnexion d-flex align-items-center justify-content-center absolute-lg m-4">Deconnexion</div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="">
                <form class="row modifProfilForm p-4"
                      action="{$routeParser->urlFor("profil")}" method="post">
                      {$erreurAffich}
                    <div class="col-12 col-lg-6 ">
                        <div class="h4 titrePartModifForm pb-3 mb-3">Modifiez vos informations</div>
                        <label class="d-flex flex-column">
                            <div class="text-dark">Email</div>
                            <input type="email" name="email" placeholder="Votre adresse email"
                                   onfocus="this.placeholder = ''"
                                   onblur="this.placeholder = 'Votre adresse email'" required
                                   class="mt-10 single-input" value="{$value["email"]}">
                        </label>
                        <div class="d-flex">
                            <label class="d-flex flex-column flex-1 m-1">
                                <div class="text-dark">Prénom</div>
                                <input type="text" name="prenom" placeholder="Votre prénom"
                                       onfocus="this.placeholder = ''"
                                       onblur="this.placeholder = 'Votre prénom'" class="mt-10 single-input" value="{$value["prenom"]}" minlength="1">
                            </label>
                            <label class="d-flex flex-column flex-1 m-1">
                                <div class="text-dark">Nom</div>
                                <input type="text" name="nom" placeholder="Votre nom" onfocus="this.placeholder = ''"
                                       onblur="this.placeholder = 'Votre nom'" class="mt-10 single-input" value="{$value["nom"]}" minlength="1">
                            </label>
                        </div>

                        <div class="d-flex">
                            <label class="d-flex flex-column flex-1">
                                <div class="text-dark">Sexe</div>
                                <div class="d-flex align-items-end justify-content-end mt-15">
                                    <label class="d-flex flex-1 m-1 align-items-center justify-content-center"
                                           onclick='if($("input[name=\"femme\"]").prop("checked")===false)$("input[name=\"homme\"]").prop("checked",false)'>
                                        <div class="text-dark d-inline-block">Femme</div>
                                        <div class="confirm-checkbox ml-2">
                                            <input type="checkbox" id="femmmeCB" name="femme" {$value["femme"]}>
                                            <label for="femmmeCB"></label>
                                        </div>
                                    </label>
                                    <label class="d-flex flex-1 m-1 align-items-center justify-content-center"
                                           onclick='if($("input[name=\"homme\"]").prop("checked")===false)$("input[name=\"femme\"]").prop("checked",false)'>
                                        <div class="text-dark d-inline-block">Homme</div>
                                        <div class="confirm-checkbox ml-2">
                                            <input type="checkbox" id="hommeCB" name="homme" {$value["homme"]}>
                                            <label for="hommeCB"></label>
                                        </div>
                                    </label>
                                </div>
                            </label>
                            <label class="d-flex flex-column flex-1 m-1">
                                <div class="">Date de naissance</div>
                                <input type="date" name="dateNaiss" placeholder="Votre date de naissance"
                                       onfocus="this.placeholder = ''"
                                       onblur="this.placeholder = 'Votre date de naissance'" class="mt-10 single-input" value="{$value["dateNaiss"]}">
                            </label>
                        </div>


                        <label class="d-flex flex-column">
                            <div class="text-dark">Adresse</div>
                            <input type="text" name="adresse" placeholder="Votre adresse"
                                   onfocus="this.placeholder = ''"
                                   onblur="this.placeholder = 'Votre adresse'" class="mt-10 single-input" value="{$value["adresse"]}" minlength="1">
                        </label>
                        <div class="d-flex">
                            <label class="d-flex flex-column flex-1 m-1">
                                <div class="text-dark">Code postal</div>
                                <input type="text" name="codepostal" pattern="[0-9]{5}"
                                       placeholder="Votre code postal"
                                       onfocus="this.placeholder = ''" onblur="this.placeholder = 'Votre code postal'"
                                       class="mt-10 single-input" value="{$value["codepostal"]}">
                            </label>

                            <label class="d-flex flex-column flex-1 m-1">
                                <div class="text-dark">Ville</div>
                                <input type="text" name="ville" placeholder="Votre ville"
                                       onfocus="this.placeholder = ''"
                                       onblur="this.placeholder = 'Votre ville'" class="mt-10 single-input" value="{$value["ville"]}" minlength="1">
                            </label>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 vertLineLeft-lg border-bleu ">
                        <div class="h4 titrePartModifForm pb-3 mb-3">Changez votre mot de passe</div>
                        <div class="d-flex justify-content-center align-items-center">
                            <div>
                                <label class="m-1 itemForm100">
                                    <div class="text-dark">Mot de passe actuel</div>
                                    <input type="password" name="oldpassword" placeholder="Votre mot de passe actuel"
                                           onfocus="this.placeholder = ''" onblur="this.placeholder = 'Votre mot de passe actuel'"
                                           class="mt-10 single-input" minlength="4">
                                </label>
                                <label class="m-1 itemForm100">
                                    <div class="text-dark">Nouveau mot de passe</div>
                                    <input type="password" name="password" placeholder="Votre nouveau mot de passe"
                                           onfocus="this.placeholder = ''" onblur="this.placeholder = 'Votre nouveau mot de passe'"
                                           class="mt-10 single-input" minlength="4">
                                </label>
                                <label class="m-1 itemForm100">
                                    <div class="text-dark">Répétez votre nouveau mot de passe</div>
                                    <input type="password" name="password2" placeholder="Validez votre nouveau mot de passe"
                                           onfocus="this.placeholder = ''"
                                           onblur="this.placeholder = 'Validez votre nouveau mot de passe'"
                                           class="mt-10 single-input" minlength="4">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="itemForm100 text-center mt-4">
                        <button class="submitIdentification" type="submit"><img src="{$routeParser->urlFor('img', ["routes" => "imgSlurp/check.png"])}"></button>
                    </div>
                </form>
            </div>
        </div>
    </section>
END;
        return $res;
    }
}

?>