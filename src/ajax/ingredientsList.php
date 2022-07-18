<?php
if(isset($_GET["query"]) && isset($_GET["testCateg"])){
    $testCateg= $_GET['testCateg'] !== 'true';
    $Hierarchie=[];
    include __DIR__."/../data/Donnees.inc.php";

    $arrayRes=[];
    $i=0;

//    On ajoute l'aliment supérieur aliment car il n'est pas pertinent de l'avoir dans la liste des ingrédients voulus ou non voulus
    $arrayIngdtsRemove=["Aliment"];
    if(isset($_GET["ingdtsRemove"])){
        $arrayIngdtsRemove=array_merge($arrayIngdtsRemove,$_GET["ingdtsRemove"]);
    }

    $catFact=\Slurp\Model\CategorieManagerFactory::getInstance();
//    var_dump($arrayIngdtsRemove);
    foreach ($Hierarchie as $k => $value){
        $take=!$testCateg;
        if($testCateg){
            $c=$catFact->searchCategByTitre($k);
            if($c->is_Ingredient()) {
                $take=true;
            }
        }

        //Premet d'afficher les valeurs qui ont un caractère identique mais modifié (accent / cédille / ...)
        $nom = $k;
        $nom = htmlentities($nom, ENT_NOQUOTES, 'utf-8');
        $nom = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $nom);
        $chaineInput = $_GET["query"];
        $chaineInput = htmlentities($chaineInput, ENT_NOQUOTES, 'utf-8');
        $chaineInput = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $chaineInput);
        if ($take && !in_array($k, $arrayIngdtsRemove) && preg_filter('/.*' . strtolower($chaineInput) . '.*/', '', strtolower($nom)) !== null) {
            $arrayRes[] = ["value" => $i, "text" => $k];
            $i++;
        }
        if($i==10){
            break;
        }
    }
    echo json_encode($arrayRes);
}
?>
