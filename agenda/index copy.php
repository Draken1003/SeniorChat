<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="color-font.css">
    
<title>Agenda</title>
</head>

<body>
    <div class="container">
        <div class="left">
            
        </div>

        <div class="middle" id="calendar">    
            <div class="top">
                <div class="title">
                    <button onclick="decreaseMonth()"><img class="left" src="../img/fleche2.png" alt=""></button>
                    <h1 id="month"></h1><h1 id="year"></h1>
                    <button onclick="increaseMonth()"><img src="../img/fleche2.png" alt=""></button>
                </div>
                <div class="today">
                    <button onclick="today()">Aujourd'hui</button>
                </div>
            </div>
            <div class="bottom">
                <div class="calendar">
                    <div class="week">
                        <div class="day">
                            <h1>Lun</h1>
                        </div>
                        <div class="day">
                            <h1>Mar</h1>
                        </div>
                        <div class="day">
                            <h1>Mer</h1>
                        </div>
                        <div class="day">
                            <h1>Jeu</h1>
                        </div>
                        <div class="day">
                            <h1>Ven</h1>
                        </div>
                        <div class="day">
                            <h1>Sam</h1>
                        </div>
                        <div class="day">
                            <h1>Dim</h1>
                        </div>
                    </div>
                    <div class="days" id="days">
                        
                    </div>
                </div>
                <div class="add-event">
                    <button onclick="popup_open()">+</button>
                </div>
            </div>
        </div>
        <div class="pop-up" id="popup">    
            <div class="close">
                <button onclick="popup_close()">
                    <img src="/icon/button-icon/croix (2).png" alt="">
                </button>
            </div>
            <div class="title">
                <h1>Ajouter une conference</h1>
            </div>
            <div class="inputs">
                <form action="" method="post">
                    <input type="text" placeholder="Titre" name="titre">
                    <input type="text" placeholder="Thème" name="theme">
                    <input type="text" placeholder="Type d'intervention" name="type">
                    <input type="text" placeholder="Langue" name="langue">
                    <div class="lieu">
                        <input type="text" placeholder="Salle" name="salle">
                        <input type="text" placeholder="Aile" name="aile">
                    </div>
                    <div class="duree">
                        <input type="text" placeholder="Debut" name="debut">
                        <img src="/icon/fleche.png" alt="fleche">
                        <input type="text" placeholder="Fin" name="fin">
                    </div>
                    <textarea name="Description" value="Description" placeholder="Description" ></textarea>
                    <div class="buttons">
                        <input type="reset" value="Annuler">
                        <input type="submit" value="Confirmer">
                    </div>
                </form>
            </div>
        </div>
        <div class="right">
            <div class="top">
                <h1>jeu. 5</h1>
            </div>
            
            <div class="evenements">
                <!-- php -->
                <div class="evenement">
                    <div class="top">
                        <h2>La santé en voyage</h2>
                        <button onclick="afficheDescription('bottom1','fleche1')"><img id="fleche1" src="../img/fleche.png" alt=""></button>
                    </div>
                    <div class="bottom" id="bottom1">
                        <hr>
                        <div class="description">
                            <p class="duree">15:00-16h30</p>
                            <p class="salle">Salle 255 - Aile B</p>
                        </div>
                        <form action="" method="post">
                            <input type="submit" value="Supprimer">
                        </form>
                    </div>
                    
                </div>
                <!-------->

                <div class="evenement">
                    <div class="top">
                        <h2>La santé en voyage</h2>
                        <button onclick="afficheDescription('bottom2','fleche2')"><img id="fleche2" src="../img/fleche.png" alt=""></button>
                    </div>
                    <div class="bottom" id="bottom2">
                        <hr>
                        <div class="description">
                            <p class="duree">15:00-16h30</p>
                            <p class="salle">Salle 255 - Aile B</p>
                        </div>
                        <form action="" method="post">
                            <input type="submit" value="Supprimer">
                        </form>
                    </div>
                </div>
                <div class="evenement">
                    <div class="top">
                        <h2>La santé en voyage</h2>
                        <button onclick="afficheDescription('bottom3','fleche3')"><img id="fleche3" src="../img/fleche.png" alt=""></button>
                    </div>
                    <div class="bottom" id="bottom3">
                        <hr>
                        <div class="description">
                            <p class="duree">15:00-16h30</p>
                            <p class="salle">Salle 255 - Aile B</p>
                        </div>
                        <form action="" method="post">
                            <input type="submit" value="Supprimer">
                        </form>
                    </div>
                </div>
                <div class="evenement">
                    <div class="top">
                        <h2>La santé en voyage</h2>
                        <button onclick="afficheDescription('bottom4','fleche4')"><img id="fleche4" src="../img/fleche.png" alt=""></button>
                    </div>
                    <div class="bottom" id="bottom4">
                        <hr>
                        <div class="description">
                            <p class="duree">15:00-16h30</p>
                            <p class="salle">Salle 255 - Aile B</p>
                        </div>
                        <form action="" method="post">
                            <input type="submit" value="Supprimer">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
<script src="js/createCalendar.js"></script>
<script src="js/afficheDescription.js"></script>
<script src="js/daySelected.js"></script>
<script src="js/changeMonth.js"></script>
<script src="js/popup.js"></script>

</html>