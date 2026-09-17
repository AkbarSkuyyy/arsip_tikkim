// Modal konfirmasi custom — pengganti confirm() bawaan browser.
// Usage:
//   confirmDialog({
//       title: 'Hapus Data?',
//       message: 'Data akan dihapus permanen.',
//       type: 'danger' | 'warning' | 'info',   // default 'danger'
//       okText: 'Hapus',                        // default 'Ya, Lanjutkan'
//       cancelText: 'Batal',
//       onConfirm: function () { ... }          // dipanggil saat tombol OK diklik
//   });
function confirmDialog(opts) {
    opts = opts || {};
    var type = opts.type || 'danger';
    var okText = opts.okText || 'Ya, Lanjutkan';
    var cancelText = opts.cancelText || 'Batal';

    // Pastikan overlay sudah ada
    var overlay = document.getElementById('confirmOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'confirmOverlay';
        overlay.className = 'confirm-overlay';
        overlay.innerHTML =
            '<div class="confirm-box">' +
            '  <div class="confirm-icon" id="confirmIcon"></div>' +
            '  <div class="confirm-title" id="confirmTitle"></div>' +
            '  <div class="confirm-message" id="confirmMessage"></div>' +
            '  <div class="confirm-actions">' +
            '    <button type="button" class="confirm-btn confirm-btn-cancel" id="confirmCancel"></button>' +
            '    <button type="button" class="confirm-btn confirm-btn-ok" id="confirmOk"></button>' +
            '  </div>' +
            '</div>';
        document.body.appendChild(overlay);
    }

    var icons = {
        danger: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
    };

    var iconEl = document.getElementById('confirmIcon');
    iconEl.className = 'confirm-icon ' + type;
    iconEl.innerHTML = icons[type] || icons.danger;

    document.getElementById('confirmTitle').textContent = opts.title || 'Konfirmasi';
    document.getElementById('confirmMessage').textContent = opts.message || '';

    var okBtn = document.getElementById('confirmOk');
    okBtn.textContent = okText;
    okBtn.className = 'confirm-btn confirm-btn-ok';
    if (type === 'warning') okBtn.classList.add('warning');
    if (type === 'info') okBtn.classList.add('primary');

    document.getElementById('confirmCancel').textContent = cancelText;

    overlay.classList.add('show');

    // Bersihkan listener lama
    var newOk = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOk, okBtn);
    var newCancel = document.getElementById('confirmCancel').cloneNode(true);
    document.getElementById('confirmCancel').parentNode.replaceChild(newCancel, document.getElementById('confirmCancel'));

    function close() { overlay.classList.remove('show'); }

    newCancel.addEventListener('click', close);
    overlay.onclick = function (e) { if (e.target === overlay) close(); };
    newOk.addEventListener('click', function () {
        close();
        if (typeof opts.onConfirm === 'function') opts.onConfirm();
    });
}

// Helper: pasang konfirmasi pada form (submit form hanya jika dikonfirmasi).
// Usage: <form ... onsubmit="return confirmSubmit(event, {title, message, type})">
function confirmSubmit(event, opts) {
    event.preventDefault();
    var form = event.target;
    confirmDialog({
        title: opts.title || 'Konfirmasi',
        message: opts.message || '',
        type: opts.type || 'danger',
        okText: opts.okText,
        onConfirm: function () { form.submit(); }
    });
    return false;
}

// Auto-binder: form apa pun dengan atribut `data-confirm` otomatis
// diintercept — tampilkan modal konfirmasi sebelum submit.
// Atribut opsional:
//   data-confirm-title, data-confirm-msg, data-confirm-type, data-confirm-ok
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            confirmDialog({
                title: form.getAttribute('data-confirm-title') || 'Konfirmasi',
                message: form.getAttribute('data-confirm-msg') || 'Lanjutkan aksi ini?',
                type: form.getAttribute('data-confirm-type') || 'danger',
                okText: form.getAttribute('data-confirm-ok') || 'Ya, Lanjutkan',
                onConfirm: function () { form.submit(); }
            });
        });
    });

    // Link/button dengan atribut `data-confirm` (mis. logout, restore)
    document.querySelectorAll('[data-confirm-link]').forEach(function (el) {
        el.addEventListener('click', function (event) {
            event.preventDefault();
            var href = el.getAttribute('href');
            confirmDialog({
                title: el.getAttribute('data-confirm-title') || 'Konfirmasi',
                message: el.getAttribute('data-confirm-msg') || 'Lanjutkan aksi ini?',
                type: el.getAttribute('data-confirm-type') || 'danger',
                okText: el.getAttribute('data-confirm-ok') || 'Ya, Lanjutkan',
                onConfirm: function () { window.location.href = href; }
            });
        });
    });
});

