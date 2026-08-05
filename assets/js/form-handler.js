document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  const submitButton = document.querySelector('button[type="submit"]');

  if (!form || !submitButton) {
    return;
  }

  form.addEventListener('submit', () => {
    submitButton.disabled = true;
    submitButton.textContent = 'Sending...';
    submitButton.style.opacity = '0.7';
    submitButton.style.cursor = 'not-allowed';
  });
});
