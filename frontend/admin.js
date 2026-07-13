// Espace membres : tout fonctionne sans JavaScript ; ce script n'ajoute que du confort.
import './css/admin.css';

// Évite les doubles envois : désactive le bouton principal après le premier envoi valide.
document.addEventListener('submit', (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'post') {
    return;
  }
  const submitter = event.submitter;
  if (submitter instanceof HTMLButtonElement && submitter.classList.contains('btn-primary')) {
    // Différé d'un tick : le bouton doit encore être « actif » au moment de l'envoi.
    window.setTimeout(() => {
      submitter.disabled = true;
    }, 0);
  }
});

// Le lien « Aller au contenu » déplace réellement le focus vers <main>.
document.querySelector('.skip-link')?.addEventListener('click', () => {
  document.getElementById('contenu')?.focus();
});
