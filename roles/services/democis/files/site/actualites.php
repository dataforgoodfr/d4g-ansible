<?php
require_once __DIR__ . '/lib/layout.php';

$actus = actus_load_all();
$actu_partagee = isset($_GET['actu']) ? actu_load((string) $_GET['actu']) : null;
$page_url = site_url() . '/actualites.php';
$page_title = 'Actualités — ' . SITE_NAME;
$page_description = 'Toutes les actualités de la Convention Citoyenne pour la Démocratie.';
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
<?php layout_og_tags($page_title, $page_description, $page_url, $actu_partagee); ?>
<link rel="icon" type="image/png" href="./images/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hammersmith+One&family=Noto+Serif:ital,wght@0,400;0,600;0,700;1,400&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="./assets/style.css">
</head>

<body>

<!-- ============================ NAV ============================ -->
<?php layout_nav(false); ?>

<!-- ============================ EN-TÊTE DE PAGE ============================ -->
<header class="page-header">
  <div class="wrap">
    <span class="eyebrow">Actualités</span>
    <h1 class="title section-title">Toutes les actualités de la Convention.</h1>
    <?php layout_rss_link(); ?>
  </div>
</header>

<!-- ============================ LISTE DES ACTUALITÉS ============================ -->
<section class="section tight">
  <div class="wrap">
<?php if ($actus === []): ?>
    <p class="actus-empty">Aucune actualité pour le moment. Les premières nouvelles de la Convention arriveront bientôt.</p>
<?php else: ?>
    <div class="actus-list-page-grid reveal-stagger">
<?php foreach ($actus as $actu) {
    layout_actu_tile($actu);
} ?>
    </div>
<?php endif; ?>
  </div>
</section>

<!-- ============================ FOOTER ============================ -->
<?php layout_footer(false); ?>

<!-- ============================ POP-IN ACTUALITÉ ============================ -->
<?php layout_actu_modal(); ?>

<script src="./assets/site.js"></script>
<script src="./assets/actus.js"></script>

</body>
</html>
