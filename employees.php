document.addEventListener('DOMContentLoaded', function () {

    const sidebar   = document.getElementById('sidebar');
    const content   = document.getElementById('content');
    const toggleBtn = document.getElementById('sidebarCollapse');
    const isMobile  = () => window.innerWidth <= 768;

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            if (isMobile()) {
                sidebar.classList.toggle('mobile-open');
            } else {
                sidebar.classList.toggle('collapsed');
                content.classList.toggle('expanded');
            }
        });
    }

    document.addEventListener('click', (e) => {
        if (isMobile() && sidebar &&
            !sidebar.contains(e.target) &&
            e.target !== toggleBtn) {
            sidebar.classList.remove('mobile-open');
        }
    });

    document.querySelectorAll('.alert.auto-dismiss').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 4000);
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to delete this record?')) {
                e.preventDefault();
            }
        });
    });

    const statusSelect  = document.getElementById('attendance_status');
    const checkInField  = document.getElementById('check_in');
    const checkOutField = document.getElementById('check_out');

    if (statusSelect && checkInField) {
        statusSelect.addEventListener('change', function () {
            if (this.value === 'Absent') {
                checkInField.value = '';
                checkInField.disabled = true;
                if (checkOutField) {
                    checkOutField.value = '';
                    checkOutField.disabled = true;
                }
            } else {
                checkInField.disabled = false;
                if (checkOutField) checkOutField.disabled = false;
            }
        });
        statusSelect.dispatchEvent(new Event('change'));
        if (checkInField.getAttribute('data-value')) {
            checkInField.disabled = false;
            if (checkOutField) checkOutField.disabled = false;
        }
    }
});