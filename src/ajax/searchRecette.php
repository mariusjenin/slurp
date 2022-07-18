<?php
//retourne la listes des recettes correspondant à la recherche
if (isset($_GET['testCateg'])) {
    $ingdtsWith = [];
    $ingdtsWithout = [];
    if (isset($_GET['ingdtsWith'])) {
        $ingdtsWith = array_merge($ingdtsWith, $_GET["ingdtsWith"]);
    }
    if (isset($_GET['ingdtsWithout'])) {
        $ingdtsWithout = array_merge($ingdtsWithout, $_GET["ingdtsWithout"]);
    }
    $recFact = \Slurp\Model\RecetteManagerFactory::getInstance();

    $rcts = $recFact->searchRecettesByIngdts($ingdtsWith, $ingdtsWithout, $_GET['testCateg'] === 'true', true, []);

    function viableForJS($arrRct)
    {
        $arrRct["recette"] = $arrRct["recette"]->getArrayRecette();
        return $arrRct;
    }

    $rcts = array_map("viableForJS", $rcts);
    echo json_encode($rcts);
}