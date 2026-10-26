/**
 * Page « Qui sommes-nous ? » : onglets (chercheur.euses / CODAC / élu.es) et
 * pop-in « Pourquoi je m'engage ».
 *
 * Sans JavaScript, la barre d'onglets reste masquée et les trois groupes
 * s'affichent l'un sous l'autre. L'onglet ouvert est reflété dans l'ancre de
 * l'URL (#chercheurs, #codac, #elus) pour pouvoir partager un lien direct.
 */
(function () {
  /* =========================================================================
     ONGLETS
     ========================================================================= */
  var section = document.querySelector('.equipe');
  var tablist = document.querySelector('.equipe-tabs');
  if (!section || !tablist) return;
  var tabs = Array.prototype.slice.call(tablist.querySelectorAll('[role="tab"]'));
  var panels = tabs.map(function (tab) {
    return document.getElementById(tab.getAttribute('aria-controls'));
  });

  function select(index, focus) {
    tabs.forEach(function (tab, i) {
      var on = i === index;
      tab.setAttribute('aria-selected', on ? 'true' : 'false');
      tab.tabIndex = on ? 0 : -1;
      panels[i].hidden = !on;
    });
    if (focus) tabs[index].focus();
  }

  function indexFromHash() {
    var i = panels.findIndex(function (p) { return '#' + p.id === window.location.hash; });
    return i < 0 ? 0 : i;
  }

  tabs.forEach(function (tab, i) {
    tab.addEventListener('click', function () {
      select(i, false);
      history.replaceState(null, '', '#' + panels[i].id);
    });
    tab.addEventListener('keydown', function (e) {
      var next = null;
      if (e.key === 'ArrowRight') next = (i + 1) % tabs.length;
      else if (e.key === 'ArrowLeft') next = (i - 1 + tabs.length) % tabs.length;
      else if (e.key === 'Home') next = 0;
      else if (e.key === 'End') next = tabs.length - 1;
      if (next === null) return;
      e.preventDefault();
      select(next, true);
      history.replaceState(null, '', '#' + panels[next].id);
    });
  });
  window.addEventListener('hashchange', function () { select(indexFromHash(), false); });

  section.classList.add('has-tabs');
  tablist.hidden = false;
  select(indexFromHash(), false);
  // Ancre présente au chargement : le navigateur a fait défiler jusqu'au
  // panneau ; on remonte en haut de page pour garder l'en-tête et les onglets.
  if (window.location.hash) window.scrollTo(0, 0);

  /* =========================================================================
     POP-IN « POURQUOI JE M'ENGAGE »
     ========================================================================= */
  var overlay = document.getElementById('personne-modal-overlay');
  var closeBtn = document.getElementById('personne-modal-close');
  var photo = document.getElementById('personne-modal-photo');
  var nom = document.getElementById('personne-modal-nom');
  var role = document.getElementById('personne-modal-role');
  var texte = document.getElementById('personne-modal-texte');
  var lastTrigger = null;

  function openModal(card, trigger) {
    var source = card.querySelector('.personne-photo').firstElementChild;
    photo.innerHTML = '';
    if (source) photo.appendChild(source.cloneNode(true));
    nom.textContent = card.querySelector('.personne-nom').textContent;
    var r = card.querySelector('.personne-role');
    role.textContent = r ? r.textContent : '';
    texte.textContent = card.querySelector('.personne-motivation').textContent;
    lastTrigger = trigger;
    overlay.classList.add('open');
    document.body.classList.add('modal-open');
    closeBtn.focus();
  }

  function closeModal() {
    overlay.classList.remove('open');
    document.body.classList.remove('modal-open');
    if (lastTrigger) lastTrigger.focus();
  }

  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('.personne-lire');
    if (trigger) openModal(trigger.closest('.personne'), trigger);
  });
  closeBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && overlay.classList.contains('open')) closeModal();
  });
})();
