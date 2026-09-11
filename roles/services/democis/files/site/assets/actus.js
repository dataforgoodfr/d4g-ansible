/**
 * Pop-in d'actualité : ouverte au clic sur une actu principale, secondaire,
 * ou une vignette de la page "toutes les actualités".
 * Chaque élément cliquable doit porter l'attribut data-actu-slug="...".
 *
 * L'URL de la page est mise à jour (History API) à l'ouverture et à la
 * fermeture de la pop-in, pour permettre de copier/partager le lien direct
 * d'une actualité, et pour que les boutons précédent/suivant du navigateur
 * ouvrent/ferment la pop-in correctement.
 */
(function () {
  var overlay = document.getElementById('actu-modal-overlay');
  var content = document.getElementById('actu-modal-content');
  var closeBtn = document.getElementById('actu-modal-close');
  if (!overlay || !content) return;

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  // -----------------------------------------------------------------------
  // Construction des URLs (lien de la pop-in / lien de partage)
  // -----------------------------------------------------------------------
  function buildUrlForSlug(slug) {
    var url = new URL(window.location.href);
    url.searchParams.set('actu', slug);
    return url;
  }

  function buildBaseUrl() {
    var url = new URL(window.location.href);
    url.searchParams.delete('actu');
    return url;
  }

  // -----------------------------------------------------------------------
  // Partage : réseaux sociaux + copie du lien
  // -----------------------------------------------------------------------
  function buildShareBar(actu, slug) {
    var shareUrl = buildUrlForSlug(slug).toString();
    var encodedUrl = encodeURIComponent(shareUrl);
    var encodedTitle = encodeURIComponent(actu.titre);

    return '' +
      '<div class="actu-modal-share">' +
        '<span class="actu-modal-share-label">Partager cette actualité</span>' +
        '<div class="actu-modal-share-links">' +
          '<a class="actu-share-link" href="https://www.linkedin.com/sharing/share-offsite/?url=' + encodedUrl + '" target="_blank" rel="noopener">LinkedIn</a>' +
          '<a class="actu-share-link" href="https://twitter.com/intent/tweet?url=' + encodedUrl + '&amp;text=' + encodedTitle + '" target="_blank" rel="noopener">X / Twitter</a>' +
          '<a class="actu-share-link" href="https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl + '" target="_blank" rel="noopener">Facebook</a>' +
          '<a class="actu-share-link" href="https://api.whatsapp.com/send?text=' + encodedTitle + '%20' + encodedUrl + '" target="_blank" rel="noopener">WhatsApp</a>' +
          '<a class="actu-share-link" href="mailto:?subject=' + encodedTitle + '&amp;body=' + encodedUrl + '">Email</a>' +
          '<button type="button" class="actu-share-copy" id="actu-modal-share-copy" data-share-url="' + escapeHtml(shareUrl) + '">Copier le lien</button>' +
        '</div>' +
      '</div>';
  }

  function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.position = 'fixed';
    ta.style.top = '0';
    ta.style.left = '0';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    ta.setSelectionRange(0, ta.value.length);
    var ok = false;
    try {
      ok = document.execCommand('copy');
    } catch (e) {
      ok = false;
    }
    document.body.removeChild(ta);
    return ok ? Promise.resolve() : Promise.reject(new Error('copy failed'));
  }

  function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(text).catch(function () {
        return fallbackCopy(text);
      });
    }
    return fallbackCopy(text);
  }

  function wireShareBar() {
    var copyBtn = document.getElementById('actu-modal-share-copy');
    if (!copyBtn) return;
    copyBtn.addEventListener('click', function () {
      var url = copyBtn.getAttribute('data-share-url');
      copyToClipboard(url).then(function () {
        var original = copyBtn.textContent;
        copyBtn.textContent = 'Lien copié !';
        copyBtn.classList.add('copied');
        setTimeout(function () {
          copyBtn.textContent = original;
          copyBtn.classList.remove('copied');
        }, 2000);
      });
    });
  }

  // -----------------------------------------------------------------------
  // Ouverture / fermeture de la pop-in
  // -----------------------------------------------------------------------
  function openModal(slug, pushUrl) {
    overlay.classList.add('open');
    document.body.classList.add('modal-open');
    content.innerHTML = '<div class="actu-modal-loading">Chargement…</div>';

    fetch('get-actu.php?slug=' + encodeURIComponent(slug))
      .then(function (res) {
        if (!res.ok) throw new Error('not found');
        return res.json();
      })
      .then(function (actu) {
        var imgHtml = actu.image_url
          ? '<div class="actu-modal-img"><img src="' + actu.image_url + '" alt="' + escapeHtml(actu.titre) + '"></div>'
          : '';
        content.innerHTML =
          imgHtml +
          '<div class="actu-modal-body">' +
          '<span class="actu-modal-date">' + escapeHtml(actu.date_formatee) + '</span>' +
          '<h2 class="actu-modal-title" id="actu-modal-title">' + escapeHtml(actu.titre) + '</h2>' +
          '<div class="actu-modal-text">' + actu.texte_html + '</div>' +
          buildShareBar(actu, slug) +
          '</div>';
        wireShareBar();
      })
      .catch(function () {
        content.innerHTML = '<div class="actu-modal-error">Impossible de charger cette actualité.</div>';
      });

    if (pushUrl) {
      var url = buildUrlForSlug(slug);
      history.pushState({ actuModal: true, actuSlug: slug }, '', url.pathname + url.search + url.hash);
    }
  }

  function closeModal(fromPopstate) {
    overlay.classList.remove('open');
    document.body.classList.remove('modal-open');
    if (fromPopstate) return;

    var state = history.state;
    if (state && state.actuModal) {
      // On revient dans l'historique plutôt que d'empiler une nouvelle entrée,
      // pour que le bouton "précédent" du navigateur reste cohérent.
      history.back();
    } else {
      var url = buildBaseUrl();
      history.replaceState({}, '', url.pathname + url.search + url.hash);
    }
  }

  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-actu-slug]');
    if (trigger) {
      e.preventDefault();
      openModal(trigger.getAttribute('data-actu-slug'), true);
    }
  });

  closeBtn.addEventListener('click', function () {
    closeModal(false);
  });
  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) closeModal(false);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && overlay.classList.contains('open')) closeModal(false);
  });

  // Navigation précédent/suivant du navigateur.
  window.addEventListener('popstate', function (e) {
    var state = e.state;
    if (state && state.actuModal && state.actuSlug) {
      openModal(state.actuSlug, false);
      return;
    }
    var params = new URLSearchParams(window.location.search);
    var slug = params.get('actu');
    if (slug) {
      openModal(slug, false);
    } else {
      closeModal(true);
    }
  });

  // Permet d'ouvrir directement une actu si l'URL contient ?actu=slug
  // (lien partagé). On remplace l'entrée d'historique initiale pour que le
  // premier "précédent" ferme bien la pop-in au lieu de quitter la page.
  var initialParams = new URLSearchParams(window.location.search);
  var initialSlug = initialParams.get('actu');
  if (initialSlug) {
    var initialUrl = buildUrlForSlug(initialSlug);
    history.replaceState({ actuModal: true, actuSlug: initialSlug }, '', initialUrl.pathname + initialUrl.search + initialUrl.hash);
    openModal(initialSlug, false);
  }
})();
