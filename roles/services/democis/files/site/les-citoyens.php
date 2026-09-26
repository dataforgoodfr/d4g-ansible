<?php
require_once __DIR__ . '/lib/layout.php';
require_once __DIR__ . '/lib/citoyens.php';

$page_url = site_url() . '/les-citoyens.php';
$page_title = 'Qui sont les ' . CITOYENS_TOTAL . ' citoyen.nes tiré.es au sort ? — ' . SITE_NAME;
$page_description = 'Genre, âge, diplôme, région, territoire, profession, rapport à la politique : la composition du panel de citoyen.nes tiré.es au sort de la Convention.';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<script defer data-domain="conventioncitoyennepourlademocratie.fr" src="https://plausible.services.dataforgood.fr/js/script.file-downloads.hash.outbound-links.pageview-props.tagged-events.js"></script>
<script>window.plausible = window.plausible || function() { (window.plausible.q = window.plausible.q || []).push(arguments) }</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= e($page_description) ?>">
<title><?= e($page_title) ?></title>
<?php layout_og_tags($page_title, $page_description, $page_url); ?>
<link rel="icon" type="image/png" href="./images/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hammersmith+One&family=Noto+Serif:ital,wght@0,400;0,600;0,700;1,400&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="./assets/style.css">
</head>

<body class="page-citoyens">

<?php citoyens_glyph_symbol(); ?>

<!-- ============================ NAV ============================ -->
<?php layout_nav(false, '#panel'); ?>

<!-- ============================ EN-TÊTE DE PAGE ============================ -->
<header class="page-header cit-hero">
  <div class="wrap cit-hero-grid">
    <div class="cit-hero-text">
      <span class="eyebrow">Le panel</span>
      <h1 class="title section-title">Qui sont les <span class="cit-hl"><?= CITOYENS_TOTAL ?></span> citoyen.nes tiré.es au sort&nbsp;?</h1>
      <p class="lead">Un panel à l’image de la société française, tiré au sort pour délibérer au sein de la Convention.</p>
    </div>
    <div class="cit-hero-glyphs reveal">
      <?= citoyens_hero_glyphs() ?>
    </div>
  </div>
</header>

<!-- ============================ INTRODUCTION ============================ -->
<section class="section tight cit-intro">
  <div class="wrap">
    <div class="intro-narrative reveal">
      <p>Pour que la Convention reflète la diversité de la société française, ses citoyen.nes ont été tiré.es au sort par téléphone, puis sélectionné.es selon plusieurs critères socio-démographiques. Chacun.e était libre d’accepter.</p>
      <p>Genre, âge, diplôme, lieu de vie, profession, rapport à la politique&nbsp;: voici, critère par critère, qui sont les <?= CITOYENS_TOTAL ?> citoyen.nes de la Convention.</p>
    </div>
  </div>
</section>

<!-- ============================ SOMMAIRE (COLLANT) ============================ -->
<nav class="cit-sommaire" aria-label="Explorer la composition du panel par critère">
  <div class="wrap">
    <span class="cit-sommaire-label tech">Explorer par</span>
    <ul>
<?php foreach (CITOYENS_SOMMAIRE as $anchor => $label): ?>
      <li><a href="#<?= $anchor ?>"><?= e($label) ?></a></li>
<?php endforeach; ?>
    </ul>
  </div>
</nav>

<main class="cit-main">

  <!-- ============================ 1. GENRE ============================ -->
  <section class="cit-section" id="genre">
    <div class="wrap">
      <?php citoyens_section_header(1, 'Genre', 'Une stricte parité entre femmes et hommes.'); ?>
      <div class="cit-rows">
<?php foreach (CITOYENS_GENRE as $item) {
    citoyens_row($item, 'genre-' . $item[1], $item[3]);
} ?>
      </div>
    </div>
  </section>

  <!-- ============================ 2. ÂGE ============================ -->
  <section class="cit-section" id="age" style="--accent: var(--bleu)">
    <div class="wrap">
      <?php citoyens_section_header(2, 'Âge', 'Des plus jeunes majeur.es aux retraité.es.'); ?>
      <div class="cit-rows">
<?php foreach (CITOYENS_AGE as $item) {
    citoyens_row($item, 'age-' . $item[1]);
} ?>
      </div>
    </div>
  </section>

  <!-- ============================ 3. DIPLÔME ============================ -->
  <section class="cit-section" id="diplome">
    <div class="wrap">
      <?php citoyens_section_header(3, 'Niveau de diplôme'); ?>
      <div class="cit-rows">
<?php foreach (CITOYENS_DIPLOME as $item) {
    citoyens_row($item, 'diplome-' . $item[1]);
} ?>
      </div>
    </div>
  </section>

  <!-- ============================ 4. RÉGION ============================ -->
  <section class="cit-section" id="region" style="--accent: var(--bleu)">
    <div class="wrap">
      <?php citoyens_section_header(4, 'Région', 'Le tirage au sort a été réalisé parmi les habitant.es de France métropolitaine, découpée en 5 zones.'); ?>
      <?php citoyens_carte(); ?>
    </div>
  </section>

  <!-- ============================ 5. TERRITOIRE ============================ -->
  <section class="cit-section" id="territoire">
    <div class="wrap">
      <?php citoyens_section_header(5, 'Type de territoire', 'Des campagnes isolées aux grandes agglomérations.'); ?>
      <div class="cit-rows">
<?php foreach (CITOYENS_TERRITOIRE as $item) {
    citoyens_row($item, 'territoire-' . $item[1]);
} ?>
      </div>
      <div class="cit-callout reveal">
        <span class="cit-callout-num"><?= e(CITOYENS_QPV) ?></span>
        <p>d’entre elles et eux habitent dans un quartier prioritaire de la politique de la ville (QPV).</p>
      </div>
    </div>
  </section>

  <!-- ============================ 6. PROFESSION ============================ -->
  <section class="cit-section" id="profession" style="--accent: var(--bleu)">
    <div class="wrap">
      <?php citoyens_section_header(6, 'Situation socio-professionnelle'); ?>
      <div class="cit-rows">
<?php foreach (CITOYENS_SOCIOPRO as $item) {
    citoyens_row($item, 'sociopro-' . $item[1]);
} ?>
      </div>
      <details class="cit-metiers reveal">
        <summary>
          <span>Voir le détail des situations professionnelles</span>
          <span class="cit-metiers-icon" aria-hidden="true"></span>
        </summary>
        <ul class="cit-chips">
<?php foreach (CITOYENS_METIERS as $i => $metier): ?>
          <li style="--i: <?= $i ?>"><?= e($metier) ?></li>
<?php endforeach; ?>
        </ul>
      </details>
    </div>
  </section>

  <!-- ============================ 7. POLITIQUE ============================ -->
  <section class="cit-section" id="politique">
    <div class="wrap">
      <?php citoyens_section_header(7, 'Intérêt pour la politique', 'Le rapport à la politique faisait partie des critères : le panel réunit des personnes qui s’y intéressent beaucoup, et d’autres pas du tout.'); ?>
      <?php citoyens_opinion(); ?>
    </div>
  </section>

</main>

<!-- ============================ FOOTER ============================ -->
<?php layout_footer(false); ?>

<script src="./assets/site.js"></script>
<script src="./assets/citoyens.js"></script>

</body>
</html>
