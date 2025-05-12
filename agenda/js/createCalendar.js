var daysContainer = document.getElementById("days");
var currentDate = new Date();
var selectedDate = currentDate;
var selectedDayBlock = null;
var globalEventObj = {};



function createCalendar(date) {
   
   var currentDate = date; // Date courante
   var startDate = new Date(currentDate.getFullYear(), currentDate.getMonth()); // Date de début du mois

   var monthTitle = document.getElementById("month"); // On recupere la balise ou l'on va afficher le mois
   var monthName = currentDate.toLocaleString("fr-FR", {
      month: "long"
   }); // Nom du mois en français

   var yearTitle = document.getElementById("year"); // On recupere la balise ou l'on va afficher l'année
   var yearNum = currentDate.getFullYear(); // Année courante
   monthTitle.innerHTML = `${monthName}`; // On affiche le mois
   yearTitle.innerHTML = `${yearNum}`; // On affiche l'année

   daysContainer.className = "days"; // On applique la classe "days" au conteneur des jours
   


   daysContainer.innerHTML = ""; // on supprime le contenu du conteneur des jours

   let currentRow = createRow(); // On crée une nouvelle ligne pour les jours
   daysContainer.appendChild(currentRow); // On ajoute la ligne au conteneur des jours

   // Jours vides avant le premier jour du mois
   //getDay() retourne 0 pour dimanche, 1 pour lundi, ..., 6 pour samedi
   for (let i = 1; i < (startDate.getDay() || 7); i++) {
      currentRow.appendChild(createEmptyDay()); // On ajoute des cases vides jusqu'au premier jour du mois
   }

   // Dernier jour du mois
   var lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate(); 
   

   for (let i = 1; i <= lastDay; i++) {
      if (currentRow.children.length >= 7) { // Si la ligne a 7 jours, on crée une nouvelle ligne
         currentRow = createRow(); // On crée une nouvelle ligne
         daysContainer.appendChild(currentRow); // On ajoute la nouvelle ligne au conteneur des jours
      }

      let dayDiv = document.createElement("div"); // On crée une div pour le jour
      dayDiv.className = "day"; // On applique la classe "day" à la div
      let h1 = document.createElement("h1"); // On crée un élément h1 pour le numéro du jour
      h1.textContent = i;  // On met le numéro du jour dans l'élément h1
      dayDiv.appendChild(h1); // On ajoute l'élément h1 à la div du jour

      // On crée une date pour le jour courant
      let currentDayDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), i); 

      // Sélection du jour courant
      // Si le jour courant est le jour sélectionné, on applique la classe "selected" à la div du jour
      if (currentDayDate.toDateString() === selectedDate.toDateString()) {
         dayDiv.classList.add("selected"); // On applique la classe "selected" à la div du jour
         dayDiv.children[0].classList.add("selected"); // On applique la classe "selected" à l'élément h1 du jour
         selectedDayBlock = dayDiv; // On garde une référence à la div du jour sélectionné
      }
      
      // Marquer les événements
      //------------A utiliser pour marquer les jour avec evenement ---------------
      // if (globalEventObj[currentDayDate.toDateString()]) {
      //    let mark = document.createElement("div"); // On crée une div pour marquer l'événement
      //    mark.className = "day marked"; // On applique la classe "marked" à la div de l'événement
      //    dayDiv.appendChild(mark); // On ajoute la div de l'événement à la div du jour
      // }

      currentRow.appendChild(dayDiv); // On ajoute la div du jour à la ligne courante
   }

   // Compléter la dernière ligne avec des cases vides
   // Tant que la dernière ligne n'a pas 7 jours, on ajoute des cases vides
   while (currentRow.children.length < 7) {
      currentRow.appendChild(createEmptyDay());
   }

   daysContainer.className = "days"; // On applique la classe "days" au conteneur des jours

   
}

function createRow() {
   let row = document.createElement("div");
   row.className = "row";
   return row;
}

function createEmptyDay() {
   let emptyDay = document.createElement("div");
   emptyDay.className = "day";
   return emptyDay;
}

createCalendar(currentDate);