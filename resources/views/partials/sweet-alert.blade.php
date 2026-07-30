<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.alert-success, .alert-warning, .err-box').forEach((element) => element.remove());

  const flash = {
    success: @json(session('success')),
    error: @json(session('error')),
    db_error: @json(session('db_error')),
    force_ganti_password: @json(session('force_ganti_password')),
  };

  const notification = flash.success
    ? { icon: 'success', title: 'Berhasil', text: flash.success }
    : flash.error
      ? { icon: 'warning', title: 'Perhatian', text: flash.error }
      : flash.db_error
        ? { icon: 'error', title: 'Terjadi kesalahan', text: flash.db_error }
        : flash.force_ganti_password
          ? { icon: 'info', title: 'Ganti password', text: flash.force_ganti_password }
          : null;

  if (notification) {
    Swal.fire({ ...notification, confirmButtonText: 'OK' });
  }

  @if ($errors->any())
    Swal.fire({
      icon: 'error',
      title: 'Data belum valid',
      text: @json($errors->first()),
      confirmButtonText: 'Perbaiki',
    });
  @endif
});

document.addEventListener('submit', (event) => {
  const form = event.target;
  const inlineHandler = form.getAttribute('onsubmit') || '';
  const message = form.dataset.confirm || inlineHandler.match(/confirm\(['\"](.+?)['\"]\)/)?.[1];

  if (!message || form.dataset.sweetAlertConfirmed === 'true') return;

  event.preventDefault();
  event.stopImmediatePropagation();

  Swal.fire({
    icon: 'warning',
    title: 'Konfirmasi tindakan',
    text: message,
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    confirmButtonText: 'Ya, lanjutkan',
    cancelButtonText: 'Batal',
  }).then((result) => {
    if (result.isConfirmed) {
      form.dataset.sweetAlertConfirmed = 'true';
      form.submit();
    }
  });
}, true);

document.addEventListener('click', (event) => {
  const button = event.target.closest('button[onclick*="confirm"]');
  if (!button) return;

  const message = button.getAttribute('onclick').match(/confirm\(['\"](.+?)['\"]\)/)?.[1];
  const form = button.closest('form');
  if (!message || !form) return;

  event.preventDefault();
  event.stopImmediatePropagation();
  Swal.fire({
    icon: 'warning',
    title: 'Konfirmasi tindakan',
    text: message,
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    confirmButtonText: 'Ya, lanjutkan',
    cancelButtonText: 'Batal',
  }).then((result) => {
    if (result.isConfirmed) form.submit();
  });
}, true);
</script>
