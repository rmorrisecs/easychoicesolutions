document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  const submitButton = document.querySelector('button[type="submit"]');

  form.addEventListener('submit', () => {
    submitButton.disabled = true;
    submitButton.textContent = 'Sending...';
  });
});
