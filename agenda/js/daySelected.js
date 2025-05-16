var lastDaySelected = document.getElementsByClassName("selected")[0]; //Recupere lelement qui a comme class "selected"
//la fonction renvoie une liste delement mais puisque nous on en a maximum 1 seul on prend donc le premier

function daySelected(id) {
    var day = document.getElementById(id);

    //on retire la class du precedent jour selectionne
    lastDaySelected.classList.remove("selected"); //div
    lastDaySelected.childNodes[0].classList.remove("selected"); // h1

    //on ajoute la class au nouveau jour selectionne
    day.classList.add('selected'); //div
    day.childNodes[0].classList.add('selected'); //h1
    lastDaySelected = day; //on change de jour selectionner
}

