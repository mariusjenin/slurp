<?php
namespace Slurp\Model;

class CategorieManagerFactory
{
    private static $_instance;

//  Constructeur De la CategorieManagerFactory (en private avec le Pattern Singleton)
    private function __construct()
    {
    }

//  Donne l'instance de la CategorieManagerFactory (Mise en place d'un Pattern Singleton)
    public static function getInstance(): CategorieManagerFactory
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new CategorieManagerFactory();
        }
        return self::$_instance;
    }

//  Créé une instance Categorie (existante dans les données)
    public function searchCategByTitre($titre)
    {
        $Hierarchie = [];
        include __DIR__."/../data/Donnees.inc.php";
        if (is_array($Hierarchie) && isset($Hierarchie[$titre])) {
            $categ = $Hierarchie[$titre];
            $categObj = new Categorie(
                $titre,
                isset($categ["sous-categorie"]) ? $categ["sous-categorie"] : [],
                isset($categ["super-categorie"]) ? $categ["super-categorie"] : [],
            );
        } else {
            return false;
        }
        return $categObj;
    }

//  Retourne toutes les catégories contenant $str dans leur nom
    public function searchCategsLike($str)
    {
        $Hierarchie = [];
        include __DIR__."/../data/Donnees.inc.php";
        $fctLike = function ($k) use ($str) {
            if (strpos(strtolower($k), strtolower($str)) !== false) {
                return true;
            } else {
                return false;
            }
        };
        $categs = array_filter($Hierarchie, $fctLike, ARRAY_FILTER_USE_KEY);
        //On trie sur le nombre de caractère
        //exemple : en faisant la recherche sur "Sucre" on obtiendra :
        //Sucre
        //Sucre roux
        //Sucre vanillé
        //Sucre en poudre
        //Sirop de sucre de canne
        uksort($categs, function ($a, $b) {
            return strlen($a) - strlen($b);
        });
        return array_map(function ($k, $v) {
            return new Categorie(
                $k,
                isset($v["sous-categorie"]) ? $v["sous-categorie"] : [],
                isset($v["super-categorie"]) ? $v["super-categorie"] : [],
            );
        }, array_keys($categs), array_values($categs));
    }

// Les fonctions pour rechercher dans les catégories sont ici pour éviter des compléxité trop élevée avec la création d'objets Categorie
// Le temps d'une recherche d'une recette avec des ingrédients est déjà relativement long

//  Vérifie si la categorie est sous-catégorie d'une autre catégorie
    public function isSousCategOf($categ1, $categ2)
    {
        $Hierarchie = "";
        include __DIR__."/../data/Donnees.inc.php";

        if ($categ2 == "Aliment") {
            return true;
        } elseif (is_array($Hierarchie) && isset($Hierarchie[$categ2]["sous-categorie"])) {
            $sousCateg2 = $Hierarchie[$categ2]['sous-categorie'];
            if (in_array($categ1, $sousCateg2)) {
                return true;
            } else {
                foreach ($sousCateg2 as $categ) {
                    if ($this->isSousCategOf($categ1, $categ)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

//  Vérifie si la categorie est super-catégorie d'une autre catégorie
    public function isSuperCategOf($categ1, $categ2)
    {
        return $this->isSousCategOf($categ2, $categ1);
    }

}


