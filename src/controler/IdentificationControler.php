<?php

namespace Slurp\Controler;

use Slim\Routing\RouteContext;
use Slurp\Model\RecetteManagerFactory;
use Slurp\Model\SlurpSite;
use Slurp\Model\User;
use Slurp\Utils\Crypt;
use Slurp\View\IdentificationView;

class IdentificationControler extends Controler
{
    public function getHTMLIdentification($inscription)
    {
        $identCont = new IdentificationView();
        $value=null;
        if(isset($_COOKIE["errConn"])){
            $crypt=Crypt::getInstance();
            parse_str($_COOKIE["errConn"],$post);
            $post=$crypt->decrypt($post);
            setcookie('errConn');
            unset($_COOKIE['errConn']);
            $value["email"]=isset($post["email"])?$post["email"]:"";
            $value["prenom"]=isset($post["prenom"])?$post["prenom"]:"";
            $value["nom"]=isset($post["nom"])?$post["nom"]:"";
            $value["dateNaiss"]=isset($post["dateNaiss"])?$post["dateNaiss"]:"";
            $value["femme"]=isset($post["femme"])?"checked":"";
            $value["homme"]=isset($post["homme"])?"checked":"";
            $value["adresse"]=isset($post["adresse"])?$post["adresse"]:"";
            $value["codepostal"]=isset($post["codepostal"])?$post["codepostal"]:"";
            $value["ville"]=isset($post["ville"])?$post["ville"]:"";
        }
        return $identCont->getHTMLIdentification($this->request,$value,$inscription);
    }

    public function getHTMLProfil()
    {
        $identCont = new IdentificationView();
        $err=null;
        if(isset($_COOKIE["errModifProfil"])){
            $crypt=Crypt::getInstance();
            parse_str($_COOKIE["errModifProfil"],$err);
            $err=$crypt->decrypt($err);
            setcookie('errModifProfil');
            unset($_COOKIE['errModifProfil']);
        }
        $slurp=SlurpSite::getInstance();

        $emailUser=$slurp->getEmailConnected();
        if($emailUser==false){
            return false;
        }
        $userOnlyEmail=new User($emailUser,"");
        $arrayUser=$userOnlyEmail->userExists($slurp->getDb());

        if($arrayUser==false){
            return false;
        }
        $value["email"]=$arrayUser["email"];
        $value["prenom"]=$arrayUser["prenom"];
        $value["nom"]=$arrayUser["nom"];
        $value["dateNaiss"]=$arrayUser["dateNaiss"];
        $value["femme"]=$arrayUser["sexe"]=="F"?"checked":"";
        $value["homme"]=$arrayUser["sexe"]=="M"?"checked":"";
        $value["adresse"]=$arrayUser["adresse"];
        $value["codepostal"]=$arrayUser["codepostal"];
        $value["ville"]=$arrayUser["ville"];

        return $identCont->getHTMLProfil($this->request, $value,$err=="true");
    }

    public function connecter(){
        return $this->actionUserConnect(false);
    }

    public function inscrire(){
        return $this->actionUserConnect(true);
    }

    public function modifierProfil(){
        $slurp=SlurpSite::getInstance();

        $oldEmail=$slurp->getEmailConnected();
        if($oldEmail!=false){
            $oldPassword = null;
            $newEmail=null;
            $newPassword=null;
            if (isset($_POST['email']) && strlen($_POST["email"]) > 1) {
                $newEmail = $_POST["email"];
            }
            if (isset($_POST['password']) && isset($_POST['password2']) && isset($_POST['oldpassword'])
                && strlen($_POST["password"]) >= 4  && strlen($_POST["oldpassword"]) >= 4
                && $_POST["password"] == $_POST["password2"]) {
                $oldPassword = $_POST["oldpassword"];
                $newPassword = $_POST["password"];
            }

            $user = new User($newEmail, $newPassword);
            $user=$this->updateUserWithForm($user);

            $err=!$user->modifProfil($slurp->getDb(),$oldEmail,$oldPassword);
            if(!$err){
                $this->connectUser($user);
            }
        } else{
            $err = true;
        }
        if (!$err) {
            $url = SlurpSite::getInstance()->getUrlRedirect();
        } else {
            $url = ["route"=>"profil","param"=>[]];
            $crypt=Crypt::getInstance();
            setcookie("errModifProfil",$crypt->encrypt("true") , time() + 900); //15min
        }
        return $url;
    }

    private function updateUserWithForm($user):User{
        if (isset($_POST["nom"]) && strlen($_POST["nom"]) > 0) {
            $user->setNom($_POST["nom"]);
        }
        if (isset($_POST["prenom"]) && strlen($_POST["prenom"]) > 0) {
            $user->setPrenom($_POST["prenom"]);
        }
        if (isset($_POST["dateNaiss"])) {
            $time = strtotime($_POST['dateNaiss']);
            if ($time) {
                $date = date('Y-m-d', $time);
                $user->setDateNaiss($date);
            }
        }
        if (isset($_POST["adresse"]) && isset($_POST["codepostal"]) && isset($_POST["ville"])
            && strlen($_POST["adresse"]) > 0 && strlen($_POST["codepostal"]) == 5 && strlen($_POST["ville"]) > 0) {
            $user->setAdresse($_POST["adresse"]);
            $user->setCodepostal($_POST["codepostal"]);
            $user->setVille($_POST["ville"]);
        }
        if (isset($_POST["homme"]) && !isset($_POST["femme"])) {
            $user->setSexe("M");
        } else if (!isset($_POST["homme"]) && isset($_POST["femme"])) {
            $user->setSexe("F");
        }
        return $user;
    }

    private function actionUserConnect($inscription)
    {
        $err = false;

        if (isset($_POST["email"]) && isset($_POST["password"]) && strlen($_POST["password"]) >= 4) {
            $user = new User($_POST["email"], $_POST["password"]);
            if ($inscription) {

                if (isset($_POST["password2"]) && $_POST["password"] == $_POST["password2"]) {
                    $user=$this->updateUserWithForm($user);
                } else {
                    $err = true;
                }


            }
            if (!$err) {
                $err = !$this->identifierUser($user, $inscription);
            }
        } else {
            $err = true;
        }
        if (!$err) {
            $url = SlurpSite::getInstance()->getUrlRedirect();
        } else {
            if($inscription){
                $route = "inscription";
            } else {
                $route = "connexion";
            }
            $url = ["route"=>$route,"param"=>[]];
            $crypt=Crypt::getInstance();
            setcookie("errConn", $crypt->encrypt(http_build_query($_POST)) ,time() + 900); //15min
        }
        return $url;
    }

    private function identifierUser(User $user, $inscription)
    {
        $db = SlurpSite::getInstance()->getDb();
        if ($inscription) {
            //On hache le mot de passe dans la DB
            $resActionBDD = $user->create($db);
        } else {
            $resActionBDD = $user->userValid($db);
        }
        if ($resActionBDD) {
            $this->connectUser($user);
        }
        return $resActionBDD;
    }

    public function connectUser(User $user){
        $slurp=SlurpSite::getInstance();
        $slurp->setEmailConnected($user->getEmail());
        //On récupère les recettes stockées en n'étant pas connecté pour les ajouter au compte
        if(isset($_SESSION["recetteFavorite"])){
            parse_str($_SESSION["recetteFavorite"],$arrayRecetteFavorite);
            $recFact=RecetteManagerFactory::getInstance();
            foreach($arrayRecetteFavorite as $v){
                $rct=$recFact->searchRecetteByTitre($v["titre"]);
                $rct->addInFavorites($user->getEmail(),$v["date"]);
            }
            $_SESSION['recetteFavorite'] = "";
            unset($_SESSION['recetteFavorite']);;
        }
    }
}

?>