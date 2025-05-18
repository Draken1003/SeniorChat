var month = currentDate.getMonth();
var year = currentDate.getFullYear();

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

function lastDaySelectedUpdate(){
    lastDaySelected = document.getElementsByClassName("selected")[0];
}

function decreaseMonth() {
    var currentDate = new Date();

    // Si le mois est janvier, on passe à décembre de l'année précédente
    if (month == 0) {
        currentDate.setFullYear(year - 1); 
        year = year - 1;
        month = 11;
        currentDate.setMonth(11);
    }
    else{ // Sinon, on passe au mois précédent
        currentDate.setMonth(month - 1);
        currentDate.setFullYear(year);
        month = month - 1;
    }
    createCalendar(currentDate);
}

function increaseMonth() {
    var currentDate = new Date();

    // Si le mois est décembre, on passe à janvier de l'année suivante
    if (month == 11) {
        currentDate.setFullYear(year + 1);
        year = year + 1;
        month = 0;
        currentDate.setMonth(0);
    }
    else{ // Sinon, on passe au mois suivant
        currentDate.setMonth(month + 1);
        currentDate.setFullYear(year);
        month = month + 1;
    }
    createCalendar(currentDate);
}

function today(){
    // On récupère la date courante

    var currentDate = new Date();
    month = currentDate.getMonth();
    year = currentDate.getFullYear();
    createCalendar(currentDate);
}