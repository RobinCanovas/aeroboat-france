// Menu mobile : les liens restent visibles sans JS ; ici on les replie derrière un bouton.
export function initNav() {
  const btn = document.querySelector('[data-nav-toggle]');
  const nav = document.getElementById('site-nav');
  if (!btn || !nav) return;

  const label = btn.querySelector('[data-nav-label]');
  const labelOpen = btn.dataset.labelOpen || '';
  const labelClose = btn.dataset.labelClose || '';
  const wide = window.matchMedia('(min-width: 80em)');

  const set = (open, returnFocus = false) => {
    nav.classList.toggle('is-open', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (label) label.textContent = open ? labelClose : labelOpen;
    if (!open && returnFocus) btn.focus();
  };

  btn.hidden = false;
  set(false);

  btn.addEventListener('click', () => set(btn.getAttribute('aria-expanded') !== 'true'));

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && btn.getAttribute('aria-expanded') === 'true') {
      set(false, true);
    }
  });

  document.addEventListener('click', (e) => {
    if (btn.getAttribute('aria-expanded') !== 'true') return;
    if (!nav.contains(e.target) && !btn.contains(e.target)) set(false);
  });

  wide.addEventListener('change', () => { if (wide.matches) set(false); });
}
