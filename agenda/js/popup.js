function popup_open(){
    var popup = document.getElementById("popup");
    popup.style.display = "block";
    var calendar = document.getElementById("calendar");
    calendar.style.display = "none";
}

function popup_close(){
    var popup = document.getElementById("popup");
    popup.style.display = "none";
    var calendar = document.getElementById("calendar");
    calendar.style.display = "block";
}