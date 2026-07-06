import './css/main.css';
import { initNav } from './js/nav.js';
import { initReveal } from './js/reveal.js';
import { initLightbox } from './js/lightbox.js';
import { initForms } from './js/forms.js';

// Le site reste entièrement utilisable sans JS : ce fichier n'ajoute que du confort.
document.documentElement.classList.add('js');

const start = () => {
  initNav();
  initReveal();
  initLightbox();
  initForms();
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', start, { once: true });
} else {
  start();
}
