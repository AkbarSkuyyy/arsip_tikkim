(function () {
    'use strict';
    if (!document.querySelector('[data-rt]')) return;

    function apply(d) {
        document.querySelectorAll('[data-rt]').forEach(function (el) {
            var key = el.getAttribute('data-rt');
            if (key === 'dokumen') {
                var kode = el.getAttribute('data-rt-kode');
                var item = (d.dokumen || []).find(function (x) { return String(x.kode) === String(kode); });
                if (item) el.textContent = item.jumlah;
            } else if (key === 'loker') {
                var id = el.getAttribute('data-rt-id');
                var it = (d.loker || []).find(function (x) { return String(x.id) === String(id); });
                if (it) el.textContent = it.jumlah;
            } else if (d[key] !== undefined) {
                el.textContent = d[key];
            }
        });
        var t = document.querySelector('[data-rt-time]');
        if (t && d.waktu) t.textContent = d.waktu;
    }

    function poll() {
        var q = location.search || '';
        fetch('realtime.php' + q, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(apply)
            .catch(function () {});
    }

    poll();
    setInterval(poll, 10000);
})();
