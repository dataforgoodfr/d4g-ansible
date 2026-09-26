/* =========================================================================
   PAGE « LES CITOYEN.NES » — le reste (apparitions, compteurs) est géré par
   site.js via les classes .reveal et les attributs data-count.
   ========================================================================= */

/* =========================================================================
   SOMMAIRE COLLANT — surligne le critère en cours de lecture
   ========================================================================= */
const sommaireLinks = Array.from(document.querySelectorAll('.cit-sommaire a[href^="#"]'));
const sommaireSections = sommaireLinks.map((a) => document.querySelector(a.getAttribute('href')));

function onSommaireSpy() {
  const offset = 200; // nav + sommaire collants, avec un peu de marge
  let current = -1;
  sommaireSections.forEach((sec, i) => {
    if (sec && sec.getBoundingClientRect().top <= offset) current = i;
  });
  if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 4) {
    current = sommaireSections.length - 1;
  }
  sommaireLinks.forEach((a, i) => {
    const active = i === current;
    if (a.classList.contains('active') === active) return;
    a.classList.toggle('active', active);
    if (active) {
      a.setAttribute('aria-current', 'true');
      // Sur mobile le sommaire défile horizontalement : garder l'entrée visible.
      const list = a.closest('ul');
      const left = a.offsetLeft - (list.clientWidth - a.offsetWidth) / 2;
      list.scrollTo({ left, behavior: 'smooth' });
    } else {
      a.removeAttribute('aria-current');
    }
  });
}
window.addEventListener('scroll', onSommaireSpy, { passive: true });
onSommaireSpy();

/* =========================================================================
   CARTE — survol d'une zone ou de sa ligne de légende : les deux s'allument
   ========================================================================= */
const carte = document.querySelector('.cit-carte');
if (carte) {
  const setHot = (zone) => {
    carte.querySelectorAll('[data-zone]').forEach((el) => {
      el.classList.toggle('is-hot', el.dataset.zone === zone);
    });
  };
  carte.querySelectorAll('.cit-zone, .cit-zones-item').forEach((el) => {
    el.addEventListener('mouseenter', () => setHot(el.dataset.zone));
    el.addEventListener('mouseleave', () => setHot(null));
    el.addEventListener('focus', () => setHot(el.dataset.zone));
    el.addEventListener('blur', () => setHot(null));
  });
}
