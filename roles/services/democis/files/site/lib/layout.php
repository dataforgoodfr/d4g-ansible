<?php
/**
 * Morceaux de page partagés entre l'accueil (index.php), la page « toutes les
 * actualités » (actualites.php) et « qui sommes-nous ? » (qui-sommes-nous.php) :
 * nav, footer, pop-in, cartes.
 */
declare(strict_types=1);

require_once __DIR__ . '/actus.php';
require_once __DIR__ . '/presse.php';
require_once __DIR__ . '/equipe.php';

/**
 * Balises <meta> Open Graph. Si une actu est passée (lien partagé ?actu=…),
 * ce sont son titre, son extrait et sa photo qui apparaissent dans l'aperçu.
 */
function layout_og_tags(string $title, string $description, string $url, ?array $actu = null): void
{
    if ($actu !== null) {
        $title = $actu['titre'] . ' — ' . SITE_NAME;
        $description = actu_excerpt($actu['texte']) ?: $description;
        $url .= (str_contains($url, '?') ? '&' : '?') . 'actu=' . rawurlencode($actu['slug']);
    }
    ?>
<meta property="og:type" content="<?= $actu ? 'article' : 'website' ?>">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($url) ?>">
<?php if ($actu && $actu['image_url']): ?>
<meta property="og:image" content="<?= e(actu_absolute_image_url($actu)) ?>">
<meta name="twitter:card" content="summary_large_image">
<?php else: ?>
<meta name="twitter:card" content="summary">
<?php endif; ?>
<link rel="alternate" type="application/rss+xml" title="Actualités — <?= e(SITE_NAME) ?>" href="rss.php">
<?php
}

/**
 * Barre de navigation. Sur l'accueil les liens de section sont des ancres ;
 * ailleurs ils renvoient vers l'accueil et la nav est affichée d'emblée dans son
 * état « scrolled » (fond blanc), faute de hero derrière elle. $active désigne
 * le lien surligné sur ces autres pages (ex. '#actualites', 'qui-sommes-nous.php').
 */
function layout_nav(bool $is_home, ?string $active = null): void
{
    $home = $is_home ? '' : './';
    $links = [
        '#actualites' => 'Actualités',
        '#projet' => 'Le projet',
        '#panel' => 'Le panel',
        '#calendrier' => 'Calendrier',
        '#gouvernance' => 'Gouvernance',
        'qui-sommes-nous.php' => 'Qui sommes-nous ?',
    ];
    ?>
<nav class="nav<?= $is_home ? '' : ' scrolled' ?>" id="nav">
  <a href="<?= $is_home ? '#' : './' ?>" class="nav-logo" aria-label="Accueil, Convention Citoyenne pour la Démocratie">
    <img src="./images/logocarre.png" alt="Convention Citoyenne pour la Démocratie">
    <span class="nav-logo-text">Convention Citoyenne<br>pour la Démocratie</span>
  </a>
  <div class="nav-links">
<?php foreach ($links as $target => $label): ?>
    <a href="<?= str_starts_with($target, '#') ? $home . $target : $target ?>"<?= (!$is_home && $target === $active) ? ' class="active"' : '' ?>><?= $label ?></a>
<?php endforeach; ?>
    <a href="<?= $home ?>#newsletter" class="nav-cta">Suivre</a>
  </div>
</nav>
<?php
}

function layout_footer(bool $is_home): void
{
    $home = $is_home ? '' : './';
    ?>
<footer class="footer">
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="footer-logo">
        <img src="./images/logo_sur_fond_fonce.png" alt="Convention Citoyenne pour la Démocratie">
        <span>Convention Citoyenne<br>pour la Démocratie</span>
      </div>
      <p>Une convention citoyenne inédite réunissant citoyen.nes, parlementaires et société civile, dans le cadre du projet de recherche DemoCIS · France 2030.</p>
    </div>
    <div class="footer-col">
      <h4>Le projet</h4>
      <ul>
        <li><a href="<?= $home ?>#actualites">Actualités</a></li>
        <li><a href="<?= $home ?>#projet">Pourquoi</a></li>
        <li><a href="<?= $home ?>#panel">Le panel</a></li>
        <li><a href="<?= $home ?>#calendrier">Calendrier</a></li>
        <li><a href="<?= $home ?>#gouvernance">Gouvernance</a></li>
        <li><a href="qui-sommes-nous.php">Qui sommes-nous ?</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Contact</h4>
      <ul>
        <!-- <wbr> : point de césure privilégié après le @, pour éviter une coupure au milieu du domaine -->
        <li><a href="mailto:contact@conventioncitoyennepourlademocratie.fr">contact@<wbr>conventioncitoyennepourlademocratie.fr</a></li>
        <li class="footer-contact-role">
          <span>Contact presse</span>
          <a href="mailto:contactpresseccd@gmail.com">contactpresseccd@<wbr>gmail.com</a>
        </li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© 2026 Convention Citoyenne pour la Démocratie</span>
    <span>
      <a href="https://democis.fr/" target="_blank" rel="noopener">Projet DemoCIS</a>
      ·
      <a href="https://www.info.gouv.fr/france-2030" target="_blank" rel="noopener">France 2030</a>
      ·
      design <a href="https://geoffreydorne.com" target="_blank" rel="noopener">Geoffrey Dorne</a>
    </span>
  </div>
</footer>
<?php
}

/** Pop-in d'actualité (remplie par assets/actus.js via get-actu.php). */
function layout_actu_modal(): void
{
    ?>
<div class="actu-modal-overlay" id="actu-modal-overlay">
  <div class="actu-modal" id="actu-modal" role="dialog" aria-modal="true" aria-labelledby="actu-modal-title">
    <button type="button" class="actu-modal-close" id="actu-modal-close" aria-label="Fermer">×</button>
    <div id="actu-modal-content">
      <div class="actu-modal-loading">Chargement…</div>
    </div>
  </div>
</div>
<?php
}

function layout_rss_link(): void
{
    ?>
<a href="rss.php" class="rss-link" target="_blank" rel="noopener">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg>
  Suivre les actualités en RSS
</a>
<?php
}

/**
 * Image d'une vignette. Une image en portrait (miniature de Short…) est
 * affichée entière, sur un fond flou tiré d'elle-même, au lieu d'être rognée.
 */
function layout_thumb_img(array $actu): void
{
    if ($actu['image_vertical']): ?>
<img class="actu-img-backdrop" src="<?= e($actu['image_url']) ?>" alt="" aria-hidden="true">
<?php endif; ?>
<img src="<?= e($actu['image_url']) ?>" alt="<?= e($actu['titre']) ?>"<?= $actu['image_vertical'] ? ' class="actu-img-vertical"' : '' ?>>
<?php
}

/** Pastille « lecture » posée sur la vignette d'une actu vidéo. */
function layout_play_badge(array $actu): void
{
    if ($actu['youtube_id'] === null) {
        return;
    }
    ?>
<span class="actu-play" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
<?php
}

/** Bloc « Actualités » de l'accueil : une actu principale + trois secondaires. */
function layout_actus_home(array $actus): void
{
    ['main' => $main, 'secondary' => $secondary] = actus_home_selection($actus);
    ?>
<hr class="full-hr">

<!-- ============================ ACTUALITÉS ============================ -->
<section class="section" id="actualites">
  <div class="wrap">
    <div class="actus-header reveal">
      <div>
        <span class="eyebrow">Actualités</span>
        <h2 class="title section-title">Ce qui se passe autour de la Convention.</h2>
      </div>
    </div>

<?php if ($main === null): ?>
    <p class="actus-empty reveal">Les premières actualités de la Convention arriveront bientôt.</p>
<?php else: ?>
    <div class="actus-grid reveal">
      <a class="actu-main" data-actu-slug="<?= e($main['slug']) ?>" href="actualites.php?actu=<?= e($main['slug']) ?>">
<?php if ($main['image_url']): ?>
        <div class="actu-main-img">
          <?php layout_thumb_img($main); ?>
          <?php layout_play_badge($main); ?>
        </div>
<?php endif; ?>
        <div class="actu-main-body">
          <span class="actu-date"><?= e(date_fr($main['date'])) ?></span>
          <h3 class="actu-main-title"><?= e($main['titre']) ?></h3>
<?php if ($main['texte'] !== ''): ?>
          <p class="actu-main-excerpt"><?= e(actu_excerpt($main['texte'])) ?></p>
<?php endif; ?>
        </div>
      </a>

<?php if ($secondary !== []): ?>
      <div class="actus-secondary-list">
<?php foreach ($secondary as $actu): ?>
        <a class="actu-card" data-actu-slug="<?= e($actu['slug']) ?>" href="actualites.php?actu=<?= e($actu['slug']) ?>">
<?php if ($actu['image_url']): ?>
          <div class="actu-card-img">
            <?php layout_thumb_img($actu); ?>
            <?php layout_play_badge($actu); ?>
          </div>
<?php endif; ?>
          <div>
            <span class="actu-date"><?= e(date_fr($actu['date'])) ?></span>
            <div class="actu-card-title"><?= e($actu['titre']) ?></div>
          </div>
        </a>
<?php endforeach; ?>
      </div>
<?php endif; ?>
    </div>

    <div class="actus-cta reveal">
      <a href="actualites.php" class="btn btn-ghost">Afficher toutes les actualités</a>
      <?php layout_rss_link(); ?>
    </div>
<?php endif; ?>
  </div>
</section>

<hr class="full-hr">
<?php
}

/**
 * Section « On en parle » de l'accueil : une liste de médias, chaque logo
 * menant à l'article. Le titre de l'article n'apparaît qu'en infobulle (les
 * articles sont souvent payants : c'est le média qui compte). Rien n'est
 * affiché tant que la liste est vide.
 */
function layout_presse_home(array $items): void
{
    if ($items === []) {
        return;
    }
    $n = count($items);
    $cols = sprintf(
        '--cols: %d; --cols-t: %d; --cols-m: %d',
        presse_colonnes($n, 5),
        presse_colonnes($n, 4),
        min($n, 2)
    );
    ?>
<!-- ============================ ON EN PARLE ============================ -->
<section class="section" id="presse">
  <div class="wrap">
    <div class="presse-header reveal">
      <span class="eyebrow">On en parle</span>
      <h2 class="title sub-title">Ils parlent de la Convention.</h2>
    </div>
    <ul class="presse-list reveal-stagger" style="<?= $cols ?>">
<?php foreach ($items as $item):
    $label = 'Lire l’article de ' . $item['media'] . ($item['titre'] !== '' ? ' : ' . $item['titre'] : '');
?>
      <li>
        <a class="presse-card" href="<?= e($item['url']) ?>" target="_blank" rel="noopener" title="<?= e($label) ?>" aria-label="<?= e($label) ?>">
          <span class="presse-logo">
<?php if ($item['logo'] !== ''): ?>
            <img src="<?= e($item['logo']) ?>" alt="<?= e($item['media']) ?>" loading="lazy">
<?php else: ?>
            <span class="presse-logo-text"><?= e($item['media']) ?></span>
<?php endif; ?>
          </span>
          <span class="presse-lire" aria-hidden="true">Lire l’article <span class="presse-arrow" aria-hidden="true">↗</span></span>
        </a>
      </li>
<?php endforeach; ?>
    </ul>
  </div>
</section>

<hr class="full-hr">
<?php
}

/**
 * Nombre de colonnes de la liste « On en parle », $max au plus : celui qui
 * laisse le moins de cases vides sur la dernière ligne (à égalité, le plus
 * grand), sans descendre sous 3 colonnes pour ne pas multiplier les lignes.
 * Ex. avec $max = 5 : 5 → 5, 6 → 3 (3 + 3), 7 → 4 (4 + 3), 9 → 3 (3 × 3).
 */
function presse_colonnes(int $n, int $max): int
{
    if ($n <= $max) {
        return max($n, 1);
    }
    $best = $max;
    $best_vides = PHP_INT_MAX;
    for ($c = $max; $c >= 3; $c--) {
        $vides = ($c - $n % $c) % $c;
        if ($vides < $best_vides) {
            $best = $c;
            $best_vides = $vides;
        }
    }
    return $best;
}

/** Vignette de la page « toutes les actualités ». */
function layout_actu_tile(array $actu): void
{
    ?>
<a class="actu-tile" data-actu-slug="<?= e($actu['slug']) ?>" href="?actu=<?= e($actu['slug']) ?>">
<?php if ($actu['image_url']): ?>
  <div class="actu-tile-img">
    <?php layout_thumb_img($actu); ?>
    <?php layout_play_badge($actu); ?>
  </div>
<?php endif; ?>
  <div class="actu-tile-body">
    <span class="actu-date"><?= e(date_fr($actu['date'])) ?></span>
    <div class="actu-tile-title"><?= e($actu['titre']) ?></div>
<?php if ($actu['texte'] !== ''): ?>
    <p class="actu-tile-excerpt"><?= e(actu_excerpt($actu['texte'])) ?></p>
<?php endif; ?>
  </div>
</a>
<?php
}

/**
 * Carte d'une personne (page « qui sommes-nous ? ») : photo, nom, puis les
 * lignes de $lignes (rôle, affiliation…) ; les vides sont ignorées. Une
 * motivation non vide ajoute un bouton qui l'ouvre en pop-in
 * (assets/equipe.js) ; elle est aussi présente, masquée, dans la page.
 */
function layout_personne(array $p, array $lignes, string $motivation = ''): void
{
    $nom = trim($p['prenom'] . ' ' . $p['nom']);
    $lignes = array_values(array_filter($lignes, fn(string $l) => $l !== ''));
    ?>
<article class="personne<?= $motivation !== '' ? ' personne-ouvrable' : '' ?>">
  <div class="personne-photo">
<?php if ($p['photo'] !== ''): ?>
    <img src="<?= e($p['photo']) ?>" alt="<?= e($nom) ?>" loading="lazy" width="480" height="480">
<?php else: ?>
    <span class="personne-initiales" aria-hidden="true"><?= e(equipe_initiales($p)) ?></span>
<?php endif; ?>
  </div>
  <h3 class="personne-nom"><?= e($nom) ?></h3>
<?php foreach ($lignes as $i => $ligne): ?>
  <p class="<?= $i === 0 ? 'personne-role' : 'personne-meta' ?>"><?= e($ligne) ?></p>
<?php endforeach; ?>
<?php if ($motivation !== ''): ?>
  <button type="button" class="personne-lire" aria-haspopup="dialog">Pourquoi je m’engage&nbsp;<span aria-hidden="true">→</span></button>
  <div class="personne-motivation" hidden><?= e($motivation) ?></div>
<?php endif; ?>
</article>
<?php
}

/** Carte d'un groupe parlementaire invité qui n'a désigné personne : photo vierge. */
function layout_groupe_absent(array $g): void
{
    ?>
<article class="personne personne-absente">
  <div class="personne-photo">
    <svg class="personne-silhouette" viewBox="0 0 100 100" aria-hidden="true"><circle cx="50" cy="38" r="17"/><path d="M18 92c2-19 15-31 32-31s30 12 32 31z"/></svg>
  </div>
  <h3 class="personne-nom"><?= e($g['groupe']) ?><?= $g['sigle'] !== '' ? ' (' . e($g['sigle']) . ')' : '' ?></h3>
  <p class="personne-role">Ce groupe parlementaire n’a pas désigné d’élu.e pour participer à la Convention.</p>
</article>
<?php
}
