<?php

namespace Slurp\Model;

//Représente une catégorie (peut être un ingrédient terminal par exempl)
class Categorie
{
    protected $titre;
//    ex :'Épice'
    protected $sousCateg;
//    ex :
//      array (
//          6 => 'Épice commune',
//          7 => 'Épice européenne',
//          16 => 'Vanille',
//      ),
    protected $superCateg;
//    ex :
//      array (
//          0 => 'Assaisonnement',
//      ),

//  Constructeur de Categorie avec tous ses attributs en paramètres
    public function __construct($titre, $sousCateg, $superCateg)
    {
        if (is_string($titre) && is_array($sousCateg) && is_array($superCateg)
//            && count($sousCateg) >= 1 && count($superCateg) >= 1
        ) {
            $this->titre = $titre;
            $this->sousCateg = $sousCateg;
            $this->superCateg = $superCateg;
        } else {
            return false;
        }
    }

//  Vérifie si l'instance est une Categorie
    public function is_Ingredient(): bool
    {
        return count($this->sousCateg) == 0;
    }

//  Vérifie si la Categorie est l'Aliment general (racine de l'arbre des categories)
    public function is_AlimentGeneral(): bool
    {
        return count($this->superCateg) == 0;
    }

//  Getter de superCateg
    public function getSuperCategs()
    {
        return $this->superCateg;
    }

//  Getter de sousCateg
    public function getSousCategs()
    {
        return $this->sousCateg;
    }

//  Getter de titre
    public function getTitre()
    {
        return $this->titre;
    }

//  Fonction toString d'une Categorie
    public function __toString()
    {
        $res="<div><strong>";
        if($this->is_Ingredient()){
            $res .= "Ingredient";
        } else {
            $res .= "Categorie";
        }

        $res.="</strong> : { <div style='margin-left:30px'>- titre : " . $this->titre . "</div><div style='margin-left:30px'>- sousCateg : [";
        if (count($this->sousCateg) == 0) {
            $res .= "]</div>";
        } else {
            foreach ($this->sousCateg as $categ) {
                $res .= "<div style='margin-left:30px'>- " . $categ . "</div>";
            }
            $res .= "]</div>";
        }
        $res .= "<div style='margin-left:30px'>- superCateg : [";
        if (count($this->superCateg) == 0) {
            $res .= "]</div>";
        } else {
            foreach ($this->superCateg as $categ) {
                $res .= "<div style='margin-left:30px'>- " . $categ . "</div>";
            }
            $res .= "}</div>";
        }
        $res .= "}</div>";
        return $res;
    }

    //Methode recursive utilisant une methode auxiliaire pour retrouver l'arborescence de la categorie/ingredient
    public function getChemin(){
        function getCheminAuxiliaire($categ,$arrayCateg){
            if(!$categ->is_AlimentGeneral()){
                $catFact=CategorieManagerFactory::getInstance();
                $superCateg=$catFact->searchCategByTitre($categ->getSuperCategs()[0]);
                $arrayCateg= getCheminAuxiliaire($superCateg,$arrayCateg);
            }
            $arrayCateg[]=$categ->getTitre();
            return $arrayCateg;
        }
        return getCheminAuxiliaire($this,[]);
    }
}

?>