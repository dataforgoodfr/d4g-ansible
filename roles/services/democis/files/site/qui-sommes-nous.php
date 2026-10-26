<?php
require_once __DIR__ . '/lib/layout.php';

$equipe = equipe_load();
$nb_chercheurs = array_sum(array_map(fn(array $projet) => count($projet['membres']), $equipe['chercheurs']));
// id du panneau => [libellé, libellé court (mobile), nombre de personnes]
$onglets = [
    'chercheurs' => ['Chercheur.euses', 'Chercheur.euses', $nb_chercheurs],
    'codac' => ['Comité d’accompagnement', 'CODAC', count($equipe['codac'])],
    'elus' => ['Élu.es', 'Élu.es', count($equipe['elus'])],
];

$page_url = site_url() . '/qui-sommes-nous.php';
$page_title = 'Qui sommes-nous ? — ' . SITE_NAME;
$page_description = 'Les chercheur.euses, le comité d’accompagnement et les élu.es qui organisent et font vivre la Convention Citoyenne pour la Démocratie.';
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

<body>

<!-- ============================ NAV ============================ -->
<?php layout_nav(false, 'qui-sommes-nous.php'); ?>

<!-- ============================ EN-TÊTE DE PAGE ============================ -->
<header class="page-header equipe-header">
  <div class="wrap">
    <span class="eyebrow">Qui sommes-nous ?</span>
    <h1 class="title section-title">Celles et ceux qui font la Convention.</h1>
    <p class="lead">La Convention Citoyenne pour la Démocratie est portée par des équipes de recherche du projet DemoCIS, suivie par un comité d’organisation et d’accompagnement, et réunit des parlementaires de différents groupes aux côtés des citoyen.nes tiré.es au sort.</p>
  </div>
</header>

<!-- ============================ ONGLETS ============================ -->
<section class="equipe">
  <div class="equipe-tabs-bar">
    <div class="wrap">
      <div class="equipe-tabs" role="tablist" aria-label="Qui sommes-nous ?" hidden>
<?php foreach ($onglets as $id => [$label, $court, $nb]): ?>
        <button type="button" class="equipe-tab" role="tab" id="onglet-<?= $id ?>" aria-controls="<?= $id ?>" aria-selected="false" tabindex="-1">
<?php if ($court === $label): ?>
          <?= e($label) ?>
<?php else: ?>
          <span class="equipe-tab-long"><?= e($label) ?></span><abbr class="equipe-tab-court" title="<?= e($label) ?>"><?= e($court) ?></abbr>
<?php endif; ?>
          <span class="equipe-tab-nb tech"><?= $nb ?></span>
        </button>
<?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="wrap">
    <!-- Chercheur.euses, par projet de recherche -->
    <div class="equipe-panel" id="chercheurs" role="tabpanel" aria-labelledby="onglet-chercheurs">
      <h2 class="equipe-panel-title">Chercheur.euses</h2>
      <p class="equipe-intro"><?= $nb_chercheurs ?> chercheur.euses, réuni.es en <?= count($equipe['chercheurs']) ?> projets de recherche, étudient la Convention : chaque équipe y pose sa propre question.</p>
<?php foreach ($equipe['chercheurs'] as $i => $projet): ?>
      <div class="equipe-projet">
        <div class="equipe-projet-info">
          <span class="equipe-projet-num tech">Projet <?= sprintf('%02d', $i + 1) ?></span>
          <h3 class="equipe-projet-titre"><?= e($projet['titre']) ?></h3>
<?php if ($projet['presentation'] !== ''): ?>
          <details class="equipe-projet-desc">
            <summary>Présentation du projet</summary>
            <p><?= e($projet['presentation']) ?></p>
          </details>
<?php endif; ?>
        </div>
        <div class="equipe-grid equipe-grid-projet">
<?php foreach ($projet['membres'] as $p) {
    layout_personne($p, [$p['affiliation']]);
} ?>
        </div>
      </div>
<?php endforeach; ?>
    </div>

    <!-- CODAC -->
    <div class="equipe-panel" id="codac" role="tabpanel" aria-labelledby="onglet-codac">
      <h2 class="equipe-panel-title">Comité d’organisation et d’accompagnement</h2>
      <p class="equipe-intro">Le comité d’organisation et d’accompagnement (CODAC) réunit des chercheur.euses, des professionnel.les de la participation citoyenne et des acteurs associatifs, qui suivent et accompagnent la Convention tout au long de ses travaux.</p>
      <div class="equipe-grid">
<?php foreach ($equipe['codac'] as $p) {
    layout_personne($p, [$p['statut']], $p['motivation']);
} ?>
      </div>
    </div>

    <!-- Élu.es -->
    <div class="equipe-panel" id="elus" role="tabpanel" aria-labelledby="onglet-elus">
      <h2 class="equipe-panel-title">Élu.es</h2>
      <p class="equipe-intro">Les <?= count($equipe['elus']) + count($equipe['groupes_absents']) ?> groupes parlementaires de l’Assemblée nationale ont été invités à désigner un.e élu.e. Les parlementaires qui ont répondu sont membres à part entière de la Convention : ils et elles délibèrent à égalité avec les citoyen.nes tiré.es au sort et les membres de la société civile.</p>
      <div class="equipe-grid">
<?php foreach ($equipe['elus'] as $p) {
    layout_personne($p, [$p['mandat'], $p['groupe']]);
}
foreach ($equipe['groupes_absents'] as $g) {
    layout_groupe_absent($g);
} ?>
      </div>
    </div>
  </div>
</section>

<!-- ============================ FOOTER ============================ -->
<?php layout_footer(false); ?>

<!-- ============================ POP-IN MOTIVATION ============================ -->
<div class="actu-modal-overlay" id="personne-modal-overlay">
  <div class="actu-modal personne-modal" role="dialog" aria-modal="true" aria-labelledby="personne-modal-nom">
    <button type="button" class="actu-modal-close" id="personne-modal-close" aria-label="Fermer">×</button>
    <div class="personne-modal-body">
      <div class="personne-modal-photo" id="personne-modal-photo"></div>
      <div>
        <h2 class="personne-modal-nom" id="personne-modal-nom"></h2>
        <p class="personne-modal-role" id="personne-modal-role"></p>
        <span class="personne-modal-label">Pourquoi je m’engage</span>
        <p class="personne-modal-texte" id="personne-modal-texte"></p>
      </div>
    </div>
  </div>
</div>

<script src="./assets/site.js"></script>
<script src="./assets/equipe.js"></script>

</body>
</html>
