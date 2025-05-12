function daySelected(id) {
    var day = document.getElementById(id);
    if (day.classList.contains('selected')) {
        day.classList.remove('selected');
        day.childNodes[1].classList.remove('selected');
    } else {
        day.classList.add('selected');
        day.childNodes[1].classList.add('selected');
    }
}