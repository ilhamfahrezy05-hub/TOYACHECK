<script>
(function () {
    'use strict';

    /* Sidebar Toggle (Mobile) */
    const sidebar   = document.getElementById('appSidebar');
    const backdrop  = document.getElementById('sidebarBackdrop');
    const toggleBtn = document.getElementById('sidebarToggle');

    function openSidebar()  { sidebar.classList.add('open'); backdrop.classList.add('show'); document.body.style.overflow = 'hidden'; }
    function closeSidebar() { sidebar.classList.remove('open'); backdrop.classList.remove('show'); document.body.style.overflow = ''; }

    if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
    if (backdrop)  backdrop.addEventListener('click', closeSidebar);

    /* Notification Dropdown */
    const notifBtn      = document.getElementById('topbarNotifBtn');
    const notifDropdown = document.getElementById('notifDropdown');

    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (userDropdown) userDropdown.classList.remove('show');
            if (dropdownIcon) dropdownIcon.style.transform = '';
            notifDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (!notifDropdown.contains(e.target) && e.target !== notifBtn) {
                notifDropdown.classList.remove('show');
            }
        });
    }

    /* User Dropdown */
    const userTrigger  = document.getElementById('userMenuTrigger');
    const userDropdown = document.getElementById('userDropdown');
    const dropdownIcon = document.getElementById('userDropdownIcon');

    if (userTrigger) {
        userTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            if (notifDropdown) notifDropdown.classList.remove('show');
            var isOpen = userDropdown.classList.toggle('show');
            if (dropdownIcon) dropdownIcon.style.transform = isOpen ? 'rotate(180deg)' : '';
        });

        document.addEventListener('click', function () {
            userDropdown.classList.remove('show');
            if (dropdownIcon) dropdownIcon.style.transform = '';
        });
    }

    /* Toast System */
    window.showToast = function (message, type, title) {
        type = type || 'success';
        var container = document.getElementById('toastContainer');
        var icons = { success: 'fa-circle-check', danger: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
        var titles = { success: 'Berhasil', danger: 'Error', warning: 'Perhatian', info: 'Informasi' };
        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        toast.innerHTML =
            '<div class="toast-icon"><i class="fa-solid ' + (icons[type] || icons.info) + '"></i></div>' +
            '<div class="toast-content">' +
            '<div class="toast-title">' + (title || titles[type] || 'Notifikasi') + '</div>' +
            '<div class="toast-message">' + message + '</div>' +
            '</div>';
        container.appendChild(toast);
        setTimeout(function () {
            toast.classList.add('fade-out');
            toast.addEventListener('animationend', function () { toast.remove(); });
        }, 4500);
    };

    /* Show session flash messages as toasts */
    @if(session('success'))
        window.showToast(@json(session('success')), 'success');
    @endif
    @if(session('error'))
        window.showToast(@json(session('error')), 'danger');
    @endif
    @if(session('warning'))
        window.showToast(@json(session('warning')), 'warning');
    @endif

    /* Modal Helpers */
    window.openModal = function (id) {
        var el = document.getElementById(id);
        if (el) { el.classList.add('show'); document.body.style.overflow = 'hidden'; }
    };

    window.closeModal = function (id) {
        var el = document.getElementById(id);
        if (el) { el.classList.remove('show'); document.body.style.overflow = ''; }
    };

    window.openMkModal = window.openModal;
    window.closeMkModal = window.closeModal;

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('show');
            document.body.style.overflow = '';
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.show').forEach(function (m) {
                m.classList.remove('show');
            });
            document.body.style.overflow = '';
        }
    });

    window.reopenModalIfError = function (modalId, condition) {
        if (condition) { window.openModal(modalId); }
    };

})();
</script>