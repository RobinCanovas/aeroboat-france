// Formulaires : après une validation serveur, place le focus sur le résumé d'erreurs ou le message d'envoi.
export function initForms() {
  const target = document.querySelector('[data-error-summary], [data-form-sent]');
  if (target) {
    target.focus();
  }
}
