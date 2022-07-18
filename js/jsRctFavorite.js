let btnAddFavorite = ".btnAjoutFavorite";
let divTitreRecette = '.titreDegradCyanMagenta';
let imgAddFavorite1 = ".imgAddFavorite1";
let imgAddFavorite2 = ".imgAddFavorite2";


function onClickAddFavorite() {
    $(btnAddFavorite).click(function () {
        let recette = $(divTitreRecette)[0].innerHTML;
        let add = $(imgAddFavorite1).css("display") === "block";
        $.ajax(
            '../ajax/rctFavorite.php',
            {
                data: {'recette': recette, "add": add},
                type: 'POST'
            }
        ).done(function (res) {
            if (res) {
                if (add) {
                    //Add Favorite
                    $(imgAddFavorite1).fadeOut(500).css({'transform': 'rotate(360deg)'});
                    $(imgAddFavorite2).fadeIn(1000).css({'transform': 'rotate(720deg)'});
                } else {
                    //Remove Favorite
                    $(imgAddFavorite2).fadeOut(500).css({'transform': 'rotate(0deg)'});
                    $(imgAddFavorite1).fadeIn(1000).css({'transform': 'rotate(0deg)'});
                }
            } else {
                alertAddFavorite(add,recette);
            }
        }).fail(function () {
            alertAddFavorite(add,recette);
        });
    });
}

function alertAddFavorite(add,recette) {
    if (add) {
        alert("Erreur lors de l'ajout de la recette \"" + recette + "\" à vos recettes favorites");
    } else {
        alert("Erreur lors de la suppression de la recette \"" + recette + "\" de vos recettes favorites");
    }
}

function init() {
    onClickAddFavorite();
}

document.addEventListener('DOMContentLoaded', init());