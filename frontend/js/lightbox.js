// Lightbox accessible basée sur <dialog> : focus piégé par showModal(), Échap, flèches.
// Sans JS, chaque vignette reste un lien vers l'image.
const ICONS = {
  close: '<path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>',
  prev: '<path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>',
  next: '<path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>',
};

function button(cls, label, icon) {
  const b = document.createElement('button');
  b.type = 'button';
  b.className = `lightbox__btn ${cls}`;
  b.setAttribute('aria-label', label);
  b.innerHTML = `<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">${ICONS[icon]}</svg>`;
  return b;
}

export function initLightbox() {
  const roots = document.querySelectorAll('[data-lightbox-root]');
  if (!roots.length || typeof HTMLDialogElement === 'undefined') return;

  let dialog = null;
  let img = null;
  let caption = null;
  let prevBtn = null;
  let nextBtn = null;
  let links = [];
  let index = 0;
  let opener = null;

  const show = (i) => {
    if (!links.length) return;
    index = (i + links.length) % links.length;
    const a = links[index];
    const thumb = a.querySelector('img');
    const text = a.dataset.caption || (thumb ? thumb.alt : '');
    img.src = a.href;
    img.alt = text;
    caption.textContent = text;
    const many = links.length > 1;
    prevBtn.hidden = !many;
    nextBtn.hidden = !many;
  };

  const build = (root) => {
    dialog = document.createElement('dialog');
    dialog.className = 'lightbox';
    dialog.setAttribute('aria-label', root.dataset.lbLabel || '');

    const stage = document.createElement('div');
    stage.className = 'lightbox__stage';
    img = document.createElement('img');
    img.className = 'lightbox__img';
    img.alt = '';
    stage.appendChild(img);

    caption = document.createElement('p');
    caption.className = 'lightbox__caption';
    caption.setAttribute('aria-live', 'polite');

    const closeBtn = button('lightbox__close', root.dataset.lbClose || 'Close', 'close');
    prevBtn = button('lightbox__prev', root.dataset.lbPrev || 'Previous', 'prev');
    nextBtn = button('lightbox__next', root.dataset.lbNext || 'Next', 'next');

    dialog.append(stage, caption, closeBtn, prevBtn, nextBtn);
    document.body.appendChild(dialog);

    closeBtn.addEventListener('click', () => dialog.close());
    prevBtn.addEventListener('click', () => show(index - 1));
    nextBtn.addEventListener('click', () => show(index + 1));
    // Clic sur le fond (hors image et boutons) : fermeture.
    dialog.addEventListener('click', (e) => {
      if (e.target === dialog || e.target === stage) dialog.close();
    });
    dialog.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') { e.preventDefault(); show(index - 1); }
      if (e.key === 'ArrowRight') { e.preventDefault(); show(index + 1); }
    });
    dialog.addEventListener('close', () => {
      document.body.classList.remove('lightbox-open');
      if (opener && document.contains(opener)) opener.focus();
    });
  };

  roots.forEach((root) => {
    const group = Array.from(root.querySelectorAll('a[data-lightbox]'));
    group.forEach((a) => {
      a.addEventListener('click', (e) => {
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.button === 1) return;
        e.preventDefault();
        if (!dialog) build(root);
        links = group;
        opener = a;
        show(group.indexOf(a));
        document.body.classList.add('lightbox-open');
        dialog.showModal();
      });
    });
  });
}
