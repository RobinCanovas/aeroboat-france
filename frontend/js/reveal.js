// Apparition discrète au défilement. Désactivée sous prefers-reduced-motion (le CSS ne masque alors rien).
export function initReveal() {
  const items = document.querySelectorAll('.reveal');
  if (!items.length) return;

  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce || !('IntersectionObserver' in window)) {
    items.forEach((el) => el.classList.add('is-visible'));
    return;
  }

  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        io.unobserve(entry.target);
      }
    });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

  items.forEach((el) => io.observe(el));

  // Filet de sécurité : rien ne doit rester masqué si l'observateur n'a pas répondu.
  window.setTimeout(() => items.forEach((el) => el.classList.add('is-visible')), 4000);
}
