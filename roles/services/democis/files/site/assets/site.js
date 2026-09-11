/* =========================================================================
   STICKY NAV
   ========================================================================= */
const nav = document.getElementById('nav');
function onScroll() {
  const y = window.scrollY;
  if (y > 60) nav.classList.add('scrolled');
  else nav.classList.remove('scrolled');
}
window.addEventListener('scroll', onScroll, { passive: true });
onScroll();

/* =========================================================================
   SCROLLSPY — surligne la section courante dans le menu (façon Uber Eats)
   ========================================================================= */
const spyLinks = Array.from(document.querySelectorAll('.nav-links a[href^="#"]'))
  .filter((a) => a.getAttribute('href').length > 1 && document.querySelector(a.getAttribute('href')));
const spySections = spyLinks.map((a) => document.querySelector(a.getAttribute('href')));

function onScrollSpy() {
  const offset = 120; // hauteur approximative du menu sticky
  let currentIndex = -1;
  spySections.forEach((sec, i) => {
    if (sec.getBoundingClientRect().top <= offset) currentIndex = i;
  });
  // proche du bas de page : forcer la dernière section
  if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 4) {
    currentIndex = spySections.length - 1;
  }
  spyLinks.forEach((a, i) => a.classList.toggle('active', i === currentIndex));
}
window.addEventListener('scroll', onScrollSpy, { passive: true });
onScrollSpy();

/* =========================================================================
   REVEAL ON SCROLL (IntersectionObserver)
   ========================================================================= */
const revealEls = document.querySelectorAll('.reveal, .reveal-stagger');
const io = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('in-view');
      io.unobserve(entry.target);
    }
  });
}, {
  threshold: 0.12,
  rootMargin: '0px 0px -40px 0px'
});
revealEls.forEach((el) => io.observe(el));

/* =========================================================================
   COUNTERS — panel numbers
   ========================================================================= */
function animateCount(el) {
  const target = parseInt(el.dataset.count, 10);
  const duration = 1400;
  const start = performance.now();
  function tick(now) {
    const t = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - t, 3);
    el.textContent = Math.round(target * eased);
    if (t < 1) requestAnimationFrame(tick);
    else el.textContent = target;
  }
  requestAnimationFrame(tick);
}
const counters = document.querySelectorAll('[data-count]');
const counterIO = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      animateCount(entry.target);
      counterIO.unobserve(entry.target);
    }
  });
}, { threshold: 0.6 });
counters.forEach((el) => counterIO.observe(el));

/* =========================================================================
   SMOOTH SCROLL FALLBACK
   ========================================================================= */
document.querySelectorAll('a[href^="#"]').forEach((a) => {
  a.addEventListener('click', (e) => {
    const href = a.getAttribute('href');
    if (href.length > 1) {
      const target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  });
});
