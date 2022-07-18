<?php
namespace Slurp\Model;

use PDO;
use PDOException;

class Recette
{
    protected $titre;
//    ex :'Alerte à Malibu (Boisson de la couleurs des fameux maillots de bains... ou presque)'
    protected $descIngdts;
//    ex : '50 cl de malibu coco|50 cl de gloss cerise|1 l de jus de goyave blanche|1 poignée de griottes'
    protected $preparation;
//    ex : 'Mélanger tous les ingrédients ensemble dans un grand pichet. Placer au frais au moins 3 heures avant de déguster. Tchin tchin !!'
    protected $ingdts;
//    ex : /!\ Ecriture approximmative
//      array (
//          0 => 'Malibu',
//          1 => 'Cerise'),
//          2 => 'Jus de goyave',
//          3 => 'Cerise griotte',
//      ),

//  Constructeur de recette avec tous ses attributs en paramètres
    public function __construct($titre, $descIngdts, $preparation, $ingdts)
    {
        if (is_string($titre) && is_string($descIngdts) && is_string($preparation) && is_array($ingdts) && count($ingdts) >= 1) {
            $this->titre = $titre;
            $this->descIngdts = explode('|',$descIngdts);
            $this->preparation = $preparation;
            $this->ingdts = $ingdts;
        } else {
            return false;
        }
    }

//  Getter des ingrédients
    public function getIngdts()
    {
        return $this->ingdts;
    }

//  Getter du Titre
    public function getTitre()
    {
        return $this->titre;
    }

//  Getter de la liste des ingrédients avec les quantités
    public function getDescIngdts(){
        return $this->descIngdts;
    }

//  Getter de la liste des ingrédients avec les quantités
    public function getPreparation(){
        return $this->preparation;
    }

//  Get tableau de la recette
    public function getArrayRecette(){
        $res=[];
        $res["titre"]=$this->titre;
        $res["descIngdts"]=$this->descIngdts;
        $res["preparation"]=$this->preparation;
        $res["ingdts"]=$this->ingdts;
        return $res;
    }

//  Vérifie si les ingrédients fournis sont contenus dans la recette
//  Retourne la liste des ingrédients qui n'y sont pas
    public function hasIngdts($ingdts){
        $array_ingdts=$ingdts;
        $contains=[];
        foreach($ingdts as $key => $ingdt){
            if(!in_array($ingdt,$this->ingdts)){
                $contains[]=$ingdt;
            } else {
                unset($array_ingdts[$key]);
            }
            if(empty($array_ingdts)){
                break;
            }
        }
        return $contains;
    }

//  Fonction toString d'une Recette
    public function __toString()
    {
        $catFact=\Slurp\Model\CategorieManagerFactory::getInstance();
        $res = "<div><strong>Recette</strong> : { <div style='margin-left:30px'>- titre : " . $this->titre . "</div><div style='margin-left:30px'>- description : [";
        if (count($this->descIngdts) == 0) {
            $res .= "]</div>";
        } else {
            foreach ($this->descIngdts as $descIngdt) {
                $res .= "<div style='margin-left:30px'>- " . $descIngdt . "</div>";
            }
            $res .= "]</div>";
        }
        $res .="</div><div style='margin-left:30px'>- preparation : " . $this->preparation . "</div><div style='margin-left:30px'>- ingredients : [";
        if (count($this->ingdts) == 0) {
            $res .= "]</div>";
        } else {
            foreach ($this->ingdts as $ingdt) {
                $res .= "<div style='margin-left:30px'>" . ($catFact->searchCategByTitre($ingdt)) . "</div>";
            }
            $res .= "]</div>";
        }
        $res .= "}</div>";
        return $res;
    }

    private function addInFavoritesDB(DataBase $db, $email,$date="")
    {
        $pdo=$db->getPdo();
        if($pdo==null){
            return false;
        }
        $pdo->beginTransaction();
        try {
            if($this->isInFavoritesDB($db,$email)){
                return false;
            } else {
                $values = [
                    ':email' => $email,
                    ':recette' => $this->titre
                ];
                if(strlen($date)>1){
                    $stm = $pdo->prepare("Insert into RecetteFavorite(email,recette,date) values(:email,:recette,:dateadd);");
                    $values[':dateadd'] = $date;
                } else {
                    $stm = $pdo->prepare("Insert into RecetteFavorite(email,recette) values(:email,:recette);");
                }
                $stm->execute($values);

                $pdo->commit();
                return true;
            }
        } catch(PDOException $e) {
            $pdo->rollBack();
            return false;
        }
    }

    private function removeFromFavoritesDB(DataBase $db, $email)
    {
        $pdo=$db->getPdo();
        if($pdo==null){
            return false;
        }
        $pdo->beginTransaction();
        try {
            if($this->isInFavoritesDB($db,$email)){
                $stm = $pdo->prepare("DELETE FROM RecetteFavorite WHERE email=:email and recette=:recette;");

                $values = [
                    ':email' => $email,
                    ':recette' => $this->titre
                ];
                $stm->execute($values);

                $pdo->commit();
                return true;
            } else {
                return false;
            }
        } catch(PDOException $e) {
            $pdo->rollBack();
            return false;
        }
    }

    private function isInFavoritesDB(DataBase $db, $email)
    {
        if (!$db->isLinked()) {
            throw new PDOException();
        }
        $stm = $db->getPdo()->prepare("SELECT * FROM RecetteFavorite where email=? and recette=?;");
        $stm->bindValue(1, $email);
        $stm->bindValue(2, $this->titre);
        $stm->execute();

        $user = $stm->fetch(PDO::FETCH_ASSOC);

        if (is_array($user)) {
            return $user;
        } else {
            return false;
        }
    }

    private function addInFavoritesLS($date="")
    {
        $res=false;
        if(!isset($_SESSION)){
            session_start();
        }
        if(!$this->isInFavoritesLS()){
            $arrayRecetteFavorite=[];
            if(isset($_SESSION["recetteFavorite"])){
                parse_str($_SESSION["recetteFavorite"],$arrayRecetteFavorite);
            }
            if(strlen($date)<=1){
                $date=date('Y-m-d');
            }
            $arrayRecetteFavorite[]=["titre"=>$this->titre,"date"=>$date];
            $_SESSION["recetteFavorite"]=http_build_query($arrayRecetteFavorite);
            $res=true;
        }
        return $res;
    }

    private function isInFavoritesLS()
    {
        $res=false;
        if(!isset($_SESSION)){
            session_start();
        }
        if(isset($_SESSION["recetteFavorite"])){
            parse_str($_SESSION["recetteFavorite"],$arrayRecetteFavorite);
            if(in_array($this->titre,array_column($arrayRecetteFavorite,"titre"))){
                $res= true;
            }
        }
        return $res;
    }

    private function removeFromFavoritesLS()
    {
        $res=false;
        if(!isset($_SESSION)){
            session_start();
        }
        if(isset($_SESSION["recetteFavorite"]) && $this->isInFavoritesLS()){
            parse_str($_SESSION["recetteFavorite"],$arrayRecetteFavorite);
            $tailleArrayBefore=count($arrayRecetteFavorite);
            foreach ($arrayRecetteFavorite as $k => $v){
                if($v["titre"]==$this->titre){
                    unset($arrayRecetteFavorite[$k]);
                    break;
                }
            }
            if(count($arrayRecetteFavorite)<$tailleArrayBefore){
                $_SESSION["recetteFavorite"]=http_build_query($arrayRecetteFavorite);
                $res=true;
            }
        }
        return $res;
    }

    public function addInFavorites($emailConnected,$date="")
    {
        if($emailConnected!==false){
            $slurp=SlurpSite::getInstance();
            return $this->addInFavoritesDB($slurp->getDb(),$emailConnected,$date);
        } else {
            return $this->addInFavoritesLS($date);
        }
    }

    public function removeFromFavorites($emailConnected)
    {
        if($emailConnected!==false){
            $slurp=SlurpSite::getInstance();
            return $this->removeFromFavoritesDB($slurp->getDb(),$emailConnected);
        } else {
            return $this->removeFromFavoritesLS();
        }
    }

    public function isInFavorites($emailConnected)
    {
        if($emailConnected!==false){
            $slurp=SlurpSite::getInstance();
            return $this->isInFavoritesDB($slurp->getDb(),$emailConnected);
        } else {
            return $this->isInFavoritesLS();
        }
    }

}

?>