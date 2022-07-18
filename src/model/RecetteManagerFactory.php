<?php
namespace Slurp\Model;

use PDO;
use PDOException;

class RecetteManagerFactory
{
    private static $_instance;

//  Constructeur De la CategorieManagerFactory (en private avec le Pattern CategorieManagerFactory)
    private function __construct()
    {
    }

//  Donne l'instance de la CategorieManagerFactory (Mise en place d'un Pattern Singleton)
    public static function getInstance(): RecetteManagerFactory
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new RecetteManagerFactory();
        }
        return self::$_instance;
    }

    public function searchRecetteByTitre($titre)
    {
        $Recettes = "";
        include __DIR__."/../data/Donnees.inc.php";
        if (is_array($Recettes)) {
            foreach ($Recettes as $recette) {
                if ($recette["titre"] == $titre) {
                    return new Recette(
                        $titre,
                        $recette["ingredients"],
                        $recette["preparation"],
                        $recette["index"],
                    );
                }
            }
        }
        return false;
    }

    //  Retourne toutes les recettes contenant $str dans leur nom
    public function searchRecettesLike($str)
    {
        $Recettes = [];
        include __DIR__."/../data/Donnees.inc.php";
        $fctLike = function ($v) use ($str) {
            if (strpos(strtolower($v['titre']), strtolower($str)) !== false) {
                return true;
            } else {
                return false;
            }
        };
        $rcts = array_filter($Recettes, $fctLike);
        //On trie sur le nombre de caractère
        usort($rcts, function ($a, $b) {
            return strlen($a['titre']) - strlen($b['titre']);
        });
        return array_map(function ($v) {
            return new Recette(
                $v['titre'],
                $v["ingredients"],
                $v["preparation"],
                $v["index"],
            );
        }, $rcts);
    }

    //  Retourne toutes les recettes contenant $str dans leur nom
    public function searchRecettesRandom($nbr)
    {
        $Recettes = [];
        include __DIR__."/../data/Donnees.inc.php";
        $arrayRcts = range(0, count($Recettes)-1);
        shuffle($arrayRcts );
        $arrayRcts = array_slice($arrayRcts ,0,$nbr);
        $fct = function (&$v) use ($Recettes) {
            $v = new Recette(
                $Recettes[$v]['titre'],
                $Recettes[$v]["ingredients"],
                $Recettes[$v]["preparation"],
                $Recettes[$v]["index"],
            );
        };
        array_walk( $arrayRcts,$fct);
        return $arrayRcts;
    }

//  Recherche toutes les recettes avec les ingrdients ingdtsAvec et sans les ingrédients ingdtsSans
//  Le test sur les catégories (récursif) est facultatif et ne se fait que si des categories sont parmi les ingrédients (voirSiTestCategNecessaire)
//  Peut renvoyer toutes les recettes plus ou moins proches de la recette (prendreRecettesProches)
    public function searchRecettesByIngdts(array $ingdtsAvec, array $ingdtsSans, $voirSiTestCategNecessaire , $prendreRecettesProches,array $titreRecettesExclues)
    {
        $faireTestCateg=false;

        $Recettes = "";
        $recettesByIngdts = [];
        include __DIR__."/../data/Donnees.inc.php";
        if (is_array($Recettes)) {
            $catFact = CategorieManagerFactory::getInstance();

            if($voirSiTestCategNecessaire) {
                foreach ($ingdtsAvec as $v) {
                    if ($faireTestCateg) {
                        break;
                    }
                    $ing = $catFact->searchCategByTitre($v);
                    if (!$ing->is_Ingredient()) {
                        $faireTestCateg = true;
                    }
                }
                foreach ($ingdtsSans as $v) {
                    if ($faireTestCateg) {
                        break;
                    }
                    $ing = $catFact->searchCategByTitre($v);
                    if (!$ing->is_Ingredient()) {
                        $faireTestCateg = true;
                    }
                }
            }

            $compare = function ($rec1, $rec2) use ($catFact, $faireTestCateg) {
                if ($faireTestCateg) {
                    $testCateg = $catFact->isSousCategOf($rec1, $rec2);
                } else {
                    $testCateg = false;
                }
                return ( $testCateg || $rec1 == $rec2) ? 0 : ($rec1 < $rec2 ? -1 : 1);
            };
            foreach ($Recettes as $recette) {
                $ingdtsAvecIntersect=[];
                $ingdtsSansIntersect=[];
                foreach($ingdtsAvec as $k => $v){
                    foreach($recette["index"] as $v2){
                        if($compare($v2,$v)==0){
                            $ingdtsAvecIntersect[]=$v2;
                            break;
                        }
                    }
                }
                foreach($ingdtsSans as $k => $v){
                    foreach($recette["index"] as $v2){
                        if($compare($v2,$v)==0){
                            $ingdtsSansIntersect[]=$v2;
                            break;
                        }
                    }
                }
                $ajouterRecette = false;
                if (!$prendreRecettesProches) {
                    //On prend la recette si elle a exactement le nombre d'ingrédients "Avec" et exactement 0 ingrédients "Sans"
                    if (count($ingdtsSansIntersect) == 0 && count($ingdtsAvecIntersect) == count($ingdtsAvec)) $ajouterRecette = true;
                } else {
                    //On prend la recette si elle a au moins un ingrédients "Avec" ou au plus <le nombre d'ingrédients "Sans" -1> ingrédients "Sans" (si exactement le nombre d'ingrédients "Avec")
                    if (count($ingdtsAvecIntersect) >= min(1, count($ingdtsAvec)) || (count($ingdtsAvecIntersect) == count($ingdtsAvec)) && count($ingdtsSansIntersect) < count($ingdtsSans)) $ajouterRecette = true;
                }
                if(in_array($recette["titre"],$titreRecettesExclues)){
                    $ajouterRecette=false;
                }
                if ($ajouterRecette) {
                    $recettesByIngdts[] = [
                        "recette" => new Recette(
                            $recette["titre"],
                            $recette["ingredients"],
                            $recette["preparation"],
                            $recette["index"],
                        ),
                        "ingdtsAvec" => $ingdtsAvecIntersect,
                        "ingdtsSans" => $ingdtsSansIntersect];
                }

            }
        }

        //On trie pour avoir les recettes qui correspondent le mieux à la recherche en premier et celle qui convienne le mieux en dernier
        //On prend toutes les recettes qui ont au moins un ingredient voulu
        // exemple : (Ingredients voulus : IV et Ingredients Retirés : IR)
        // - Recette n°1 avec 3 IV et 0 IR
        // - Recette n°2 avec 2 IV et 0 IR
        // - Recette n°3 avec 1 IV et 0 IR
        // - Recette n°4 avec 4 IV et 1 IR
        // - Recette n°5 avec 2 IV et 1 IR
        // ...
        usort($recettesByIngdts, function ($a, $b) {
            return -(-(count($a['ingdtsSans']) <=> count($b['ingdtsSans'])) ?: count($a['ingdtsAvec']) <=> count($b['ingdtsAvec']));
        });
        return $recettesByIngdts;
    }

    //Renvoie les noms des recettes et la date à laquelle elles ont été ajoutées
    public function getRecettesFavorites($emailConnected){
        if($emailConnected!==false){
            $slurp=SlurpSite::getInstance();
            $res= $this->getRecettesFavoritesDB($slurp->getDb(),$emailConnected);
        } else {
            $res= $this->getRecettesFavoritesLS();
        }
        usort($res,function($v,$v2){
            return strtotime($v["date"])>strtotime($v2["date"])?-1:1;
        });
        return $res;
    }

    //Renvoie les noms des recettes et la date à laquelle elles ont été ajoutées (Local)
    public function getRecettesFavoritesLS(){
        $res=[];
        if(!isset($_SESSION)){
            session_start();
        }
        if(isset($_SESSION["recetteFavorite"])){
            parse_str($_SESSION["recetteFavorite"],$arrayRecetteFavorite);
            $res=$arrayRecetteFavorite;
        }
        return $res;
    }

    //Renvoie les noms des recettes et la date à laquelle elles ont été ajoutées (DataBase)
    public function getRecettesFavoritesDB($db,$email){
        $pdo=$db->getPdo();
        if($pdo==null){
            return [];
        }
        try {
            $res=[];
            $stm = $pdo->prepare("Select * FROM RecetteFavorite WHERE email=?;");
            $stm->bindValue(1, $email);
            $stm->execute();

            while($line=$stm->fetch(PDO::FETCH_OBJ))
            {
                $res[]=["titre"=>$line->recette,"date"=>$line->date];
            }
            return $res;
        } catch(PDOException $e) {
            return [];
        }
    }
}


