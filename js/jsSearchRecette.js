let detailsCarteRecette ;
const selectWith = '.autoSelectWith';
const selectWithout = '.autoSelectWithout';
const btnAddWith = '.btn-ajouter-ingdtWith';
const btnAddWithout = '.btn-ajouter-ingdtWithout';
const listIngdtWith = '.listIngdtWith';
const listIngdtWithout = '.listIngdtWithout';
const btnEffectSearch = '.btn-valider-search-recette';
const listAffichRecettes = '.listAffichRecettes';

function actualizeDetailsCarteRecette(){
    detailsCarteRecette = $('.carteRecetteSearchRecette~.hoverIngdtWithout.affichDetailsON,.carteRecetteSearchRecette~.hoverIngdtWith.affichDetailsON');
}

function setupAutocomplete(selSelect, selList, selList2) {
    //On met en place l'autocomplete
    $(selSelect).autoComplete({
        minLength: 0,
        resolver: "custom",
        noResultsText: "Pas d'ingrédients",
        events: {
            search: function (qry, callback) {
                let testCateg=$('#default-switch').is(':checked');
                let ingdtsRemove = [];
                //On récupère les ingrédients déjà ajoutés pour ne pas les reprendre
                $(selList).children().each(function (i) {
                    ingdtsRemove.push($(selList).children()[i].firstElementChild.textContent);
                })
                $(selList2).children().each(function (i) {
                    ingdtsRemove.push($(selList2).children()[i].firstElementChild.textContent);
                })
                // Custom ajax call
                $.ajax(
                    'ajax/ingredientsList.php',
                    {
                        data: {'query': qry, 'ingdtsRemove': ingdtsRemove,'testCateg':testCateg}
                    }
                ).done(function (res) {
                    callback(res)
                }).fail(function () {
                    alert("Erreur lors du chargement de la liste des ingrédients");
                });
            },
            searchPost: function (resultsFromServer, origJQElement) {
                let resultsJson
                if ($.type(resultsFromServer) === "string") {
                    resultsJson = JSON.parse(resultsFromServer);
                } else {
                    resultsJson = resultsFromServer;
                }
                return resultsJson;
            }
        }
    });

    $(selSelect).click(function () {
        $(selSelect).autoComplete('show');
    });
}

function removeIngdtFromList(elem, selList) {
    event.stopPropagation();
    let numberOfIngdt =  $(selList).children().length;
    $(elem).parent().fadeOut(300, function () {
        $(elem).parent().remove();
        if (numberOfIngdt === 1) {
            $(selList).removeClass("p-3");
        }
    });
}

function onClickAjouterIngdtRecherche(selBtn, selSelect, selList, nameClasse) {
    $(selBtn).click(function () {
        let ingdt = $(selSelect)[0].value;
        if (ingdt.length > 1) {
            $.ajax(
                'ajax/urlIngdt.php',
                {
                    data: {'ingdt': ingdt}
                }
            ).done(function (res) {
                //On vérifie si le Div est vide (si oui on ajoute du padding
                if ( $(selList).children().length === 0) {
                    $(selList).addClass("p-3");
                }
                let html = "<div onclick=\"location.href='" + res + "';\" class=\"mt-2 p-2 w-100 ingdtWithWithout " + nameClasse + "\">\n" +
                    "                                        <div>" + ingdt + "</div>\n" +
                    "                                        <svg onclick='removeIngdtFromList(this,\"" + selList + "\")' xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\"\n" +
                    "                                             fill=\"currentColor\" class=\"align-self-start bi bi-x-circle-fill\"\n" +
                    "                                             viewBox=\"0 0 16 16\">\n" +
                    "                                            <path d=\"M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z\"/>\n" +
                    "                                        </svg>\n" +
                    "                                    </div>";
                $(html).appendTo(selList).hide().fadeIn(300);
            }).fail(function () {
                alert("Erreur lors de l'ajout de l'ingrédient \""+ingdt+"\"");
            });
        }
        $(selSelect)[0].value = "";
    });
}
function affichDetails(elem,details,affich){
    let e=$(elem).parents().eq(3).find(details);
    if(affich){
        e.addClass("affichDetailsON");
        e.removeClass("affichDetailsOFF");
    } else {
        e.addClass("affichDetailsOFF");
        e.removeClass("affichDetailsON");
    }
}
function onClickLaunchSearchRecette(selBtnSearch, selListWith, selListWithout, selListRecette) {
    $(selBtnSearch).click(function () {
        let ingdtsWith = [];
        let ingdtsWithout = [];
        //On récupère les ingrédients déjà ajoutés pour ne pas les reprendre
        $(selListWith).children().each(function (i) {
            ingdtsWith.push($(selListWith).children()[i].firstElementChild.textContent);
        })
        $(selListWithout).children().each(function (i) {
            ingdtsWithout.push($(selListWithout).children()[i].firstElementChild.textContent);
        })
        let nbIngdtListWith = $(selListWith).children().length;
        let nbIngdtListWithout = $(selListWithout).children().length;
        let testCateg=$('#default-switch').is(':checked');

        $(selListRecette).fadeOut(300, function () {
            $(this).html( "\n" +
                "            <div class=\"row p-4\">\n" +
                "                <div class=\"col-12 text-center h4\">\n" +
                "                    <div>\n" +
                "                        <img style=\"max-height: 100px\" src=\"img/imgSlurp/loading.gif\" alt=\"\">\n" +
                "                    </div>\n" +
                "                </div>\n" +
                "            </div>").show();
            $.ajax(
                'ajax/searchRecette.php',
                {
                    data: {'ingdtsWith': ingdtsWith, 'ingdtsWithout': ingdtsWithout, 'testCateg':testCateg}
                }
            ).done(function (resultsFromServer) {
                let resultsJson;
                if ($.type(resultsFromServer) === "string") {
                    resultsJson = JSON.parse(resultsFromServer);
                } else {
                    resultsJson = resultsFromServer;
                }
                $(selListRecette).fadeOut(300, function () {
                    $(this).html('').show();
                    let html;
                    if (resultsJson.length === 0) {
                        html = "<div class=\"row p-5\">\n" +
                            "                <div class=\"col-12 text-center h4\">\n" +
                            "                    <div>\n" +
                            "                        Pas de recette trouvée\n" +
                            "                    </div>\n" +
                            "                </div>\n" +
                            "            </div>";
                        $(html).css('order','-1').appendTo(selListRecette).hide().fadeIn(300);
                    } else {
                        html = "<div class=\"row text-center\">\n" +
                            "                <div class=\"col-12 mt-3\">\n" +
                            "                    <div class=\"p-4 carteRecetteExempleSearchRecette\">\n" +
                            "                        <div class=\"row\">\n" +
                            "                            <div class=\"h5 col-12 col-md-6\">\n" +
                            "                                Intitulé de la recette\n" +
                            "                            </div>\n" +
                            "                            <div class=\"h5 col-6 mt-4 mt-md-0 col-md-3 vertLineLeft-md border-grey\">\n" +
                            "                                Ingrédients désirés\n" +
                            "                            </div>\n" +
                            "                            <div class=\"h5 col-6 mt-4 mt-md-0 col-md-3 vertLineLeft border-grey\">\n" +
                            "                                Ingrédients non désirés\n" +
                            "                            </div>\n" +
                            "                        </div>\n" +
                            "                    </div>\n" +
                            "                </div>\n" +
                            "            </div>";

                        $(html).css('order','-1').appendTo(selListRecette).hide().fadeIn(300);
                        let noRecette=0;
                        resultsJson.forEach(function (item) {
                            let noThisRct=noRecette;
                            noRecette++;
                            let nomRecette=item.recette.titre;
                            let ingdtsAvec=Object.values(item.ingdtsAvec);
                            let ingdtsSans=Object.values(item.ingdtsSans);
                            let nbIngdtsAvec=ingdtsAvec.length;
                            let nbIngdtsSans=ingdtsSans.length;
                            let nameClasseGradient;
                            if(nbIngdtsAvec===nbIngdtListWith && nbIngdtsSans===0){
                                //Exactement la recette recherchée
                                nameClasseGradient="linGradRecettePerfect";
                            } else if(nbIngdtsSans===0){
                                //Exactement aucun mauvais ingdt
                                nameClasseGradient="linGradRecetteGood";
                            } else if(nbIngdtsSans<Math.max(1,nbIngdtListWithout/2)){
                                //Un nombre d'ingdt mauvais plus petit que la moitié des ingdt mauvais au total
                                nameClasseGradient="linGradRecetteNormal";
                            } else{
                                //Un nombre d'ingdt mauvais egal ou plus grand que la moitié des ingdt mauvais au total
                                nameClasseGradient="linGradRecetteBad";
                            }
                            $.ajax(
                                'ajax/urlRecette.php',
                                {
                                    data: {'recette': nomRecette}
                                }
                            ).done(function (res) {
                                if ($.type(res) === "string") {
                                    res = JSON.parse(res);
                                } else {
                                    res = res;
                                }
                                let url=res.url;
                                let html = "<div class=\"row\">\n" +
                                    "                <div class=\"col-12 mt-3\">\n" +
                                    "                    <div onclick=\"location.href='" + url + "';\" class=\"p-4 carteRecetteSearchRecette "+nameClasseGradient+"\">\n" +
                                    "                       <div class=\"row\">\n" +
                                    "                          <div class=\"pl-4 titreCarteRecette h4 col-12 col-md-6 d-flex justify-content-center align-content-center flex-column\">\n" +
                                    "                              "+nomRecette+"\n" +
                                    "                            </div>\n" +
                                    "                            <div class=\"text-center h3 col-6 mt-4 mt-md-0 col-md-3 vertLineLeft-md border-white d-flex flex-row justify-content-center align-items-center\">\n" +
                                    "                                <div onmouseleave=\"affichDetails(this,'.hoverIngdtWith',false)\" onmouseenter=\"affichDetails(this,'.hoverIngdtWith',true)\" class=\"badgeNumberCarteRecette d-flex flex-column justify-content-center align-content-center\">\n" +
                                    "                                    "+nbIngdtsAvec+"\n" +
                                    "                                </div>\n" +
                                    "                            </div>\n" +
                                    "                            <div class=\"text-center h3 col-6 mt-4 mt-md-0 col-md-3 vertLineLeft-md border-white d-flex flex-row justify-content-center align-items-center\">\n" +
                                    "                               <div onmouseleave=\"affichDetails(this,'.hoverIngdtWithout',false)\" onmouseenter=\"affichDetails(this,'.hoverIngdtWithout',true)\" class=\"badgeNumberCarteRecette d-flex flex-column justify-content-center align-content-center\">\n" +
                                    "                                   "+nbIngdtsSans+"\n" +
                                    "                              </div>\n" +
                                    "                         </div>\n" +
                                    "                       </div>\n" +
                                    "                       </div>\n" +
                                    "                       <div class='affichDetailsOFF hoverIngdtWith h6 p-3'>";
                                if(nbIngdtsAvec===0){
                                    html+="                      <ul><div class=\"h5\">Pas d'ingrédient désiré</div>";
                                }else if(nbIngdtsAvec===1){
                                    html+="                      <ul><div class=\"h5\">L'ingrédient désiré est :</div>";
                                }else {
                                    html+="                      <ul><div class=\"h5\">Les ingrédients désirés sont :</div>";
                                }
                                ingdtsAvec.forEach(function (ing){
                                    html +="                        <li class=\"p-2\">"+ing+"</li>";
                                });
                                html+=
                                    "                           </ul>" +
                                    "                       </div>\n" +
                                    "                       <div class='affichDetailsOFF hoverIngdtWithout h6 p-3'>";
                                if(nbIngdtsSans===0){
                                    html+="                      <ul><div class=\"h5\">Pas d'ingrédient non désiré</div>";
                                }else if(nbIngdtsSans===1){
                                    html+="                      <ul><div class=\"h5\">L'ingrédient non désiré est :</div>";
                                }else {
                                    html+="                      <ul><div class=\"h5\">Les ingrédients non désirés sont :</div>";
                                }
                                ingdtsSans.forEach(function (ing){
                                    html +="                        <li class=\"p-2\">"+ing+"</li>";
                                });
                                html+=
                                    "                       </div>\n" +
                                    "                </div>\n" +
                                    "            </div>";
                                $(html).css('order',noThisRct).appendTo(selListRecette).hide().fadeIn(300);
                            }).fail(function () {
                                alert("Erreur lors du chargement de la recette \""+nomRecette+"\"");
                            });
                        })
                    }
                });
            }).fail(function () {
                alert("Erreur lors du chargement des recettes");
            });

        });
    });
}

function init() {
    actualizeDetailsCarteRecette();
    setupAutocomplete(selectWith, listIngdtWith, listIngdtWithout);
    setupAutocomplete(selectWithout, listIngdtWith, listIngdtWithout);
    onClickAjouterIngdtRecherche(btnAddWith, selectWith, listIngdtWith, "ingdtWith");
    onClickAjouterIngdtRecherche(btnAddWithout, selectWithout, listIngdtWithout, "ingdtWithout");
    onClickLaunchSearchRecette(btnEffectSearch);
    onClickLaunchSearchRecette(btnEffectSearch, listIngdtWith, listIngdtWithout, listAffichRecettes);
    window.onmousemove = function (e) {
        actualizeDetailsCarteRecette();
        var x = (e.clientX ) ,
            y = (e.clientY - 15) ;
        if(detailsCarteRecette.length>0){
            detailsCarteRecette[0].style.top = (y - $(detailsCarteRecette[0]).outerHeight(false))+ 'px';
            detailsCarteRecette[0].style.left = (x - $(detailsCarteRecette[0]).outerWidth(false)/2.0 )+ 'px';
        }
    };
}

document.addEventListener('DOMContentLoaded', init());