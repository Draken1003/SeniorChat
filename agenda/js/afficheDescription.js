function afficheDescription(id,id2) {
    var bottom = document.getElementById(id);
    var fleche = document.getElementById(id2);
    if (bottom.classList.contains('active')) {
        bottom.classList.remove('active');
        fleche.classList.remove('active-fleche');
        
    } else {
        bottom.classList.add('active');
        fleche.classList.add('active-fleche');
    }
}