document.addEventListener('click', async (e) => {
  const a = e.target.closest('.js-open-notif');
  if (!a) return;

  const id = a.getAttribute('data-id');
  const href = a.getAttribute('href');

  // let normal links work if missing id
  if (!id || !href) return;

  e.preventDefault();

  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  try {
    await fetch(`/notifications/${id}/read`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token ?? '',
      },
      credentials: 'same-origin',
    });
  } catch (err) {
    // ignore
  }

  window.location.href = href;
});
