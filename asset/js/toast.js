// Toast notification — pengganti alert()
function toast(message, type) {
    type = type || 'info';
    var c = document.querySelector('.toast-container');
    if (!c) {
        c = document.createElement('div');
        c.className = 'toast-container';
        document.body.appendChild(c);
    }
    var t = document.createElement('div');
    t.className = 'toast toast-' + type;
    t.textContent = message;
    c.appendChild(t);
    setTimeout(function () {
        t.style.opacity = '0';
        t.style.transition = 'opacity 0.3s';
    }, 3000);
    setTimeout(function () { t.remove(); }, 3400);
}
