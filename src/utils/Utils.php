<?php


namespace Slurp\Utils;


use Slurp\Model\Recette;

class Utils
{

    private static $_instance;

    //COnstructeur privé pour singleton
    private function __construct()
    {
    }

    //  Donne l'instance de la Crypt (Mise en place d'un Pattern Singleton)
    public static function getInstance(): Utils
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Utils();
        }
        return self::$_instance;
    }

    public function getRctToImgInUrl($titre){
        // exemple "Boisson'Exémple str(str2)" => "Boissonexemple_strstr2.jpg"
        $titre = htmlentities($titre, ENT_NOQUOTES, 'utf-8');
        //On met en minuscule
        $titre = strtolower($titre);
        //On remplace les caractères accentuées par leur equivalent
        $titre = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $titre);
        // pour les ligatures exemple : 'œ'
        $titre = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $titre);
        // supprime les caractères spéciaux (pas une lettre et pas un espace)
        $titre = preg_replace('#[^a-z ]+#i', '', $titre);
        // remplace les espaces par des underscore
        $titre = str_replace(' ','_',$titre);
        //On met en majuscule la première lettre et on rajoute .jpg
        return ucfirst($titre).".jpg";
    }

    public function numMonthTOStrMonthFR($num){
        switch($num){
            case "1":
                return "janvier";
            case "2":
                return "février";
            case "3":
                return "mars";
            case "4":
                return "avril";
            case "5":
                return "mai";
            case "6":
                return "jeuin";
            case "7":
                return "juillet";
            case "8":
                return "août";
            case "9":
                return "septembre";
            case "10":
                return "octobre";
            case "11":
                return "novembre";
            case "12":
                return "décembre";
            default:
                return false;
        }
    }
}