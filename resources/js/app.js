import './bootstrap';
import NProgress from 'nprogress';
import Swal from 'sweetalert2';
import ApexCharts from 'apexcharts';

window.Swal = Swal;
window.NProgress = NProgress;
window.ApexCharts = ApexCharts;

// NProgress Navigation Hooks for Livewire
document.addEventListener('livewire:navstart', () => {
    NProgress.start();
});
document.addEventListener('livewire:navstop', () => {
    NProgress.done();
});
document.addEventListener('livewire:navfail', () => {
    NProgress.done();
});

// Toast SweetAlert helper
window.showToast = (icon, title) => {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: 'rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100'
        }
    });
};

// Global Event Listeners for Livewire SweetAlert notifications
window.addEventListener('swal:toast', (event) => {
    const data = event.detail[0] || event.detail;
    window.showToast(data.type || data.icon || 'success', data.message || data.title);
});

window.addEventListener('swal:confirm', (event) => {
    const data = event.detail[0] || event.detail;
    Swal.fire({
        title: data.title || 'Apakah Anda yakin?',
        text: data.text || 'Tindakan ini tidak dapat dibatalkan.',
        icon: data.icon || 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#64748b',
        confirmButtonText: data.confirmButtonText || 'Ya, Lanjutkan',
        cancelButtonText: data.cancelButtonText || 'Batal',
        customClass: {
            popup: 'rounded-xl shadow-xl bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700'
        }
    }).then((result) => {
        if (result.isConfirmed && data.method) {
            Livewire.dispatch(data.method, data.params || []);
        }
    });
});
