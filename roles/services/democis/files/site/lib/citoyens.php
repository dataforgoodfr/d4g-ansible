<?php
/**
 * Page « Les citoyen.nes » : composition du panel tiré au sort, critère par
 * critère. Les données sont ici ; le rendu (pictogrammes, carte, barre
 * d'opinion) est animé en CSS quand la ligne entre dans l'écran (classe
 * .in-view posée par assets/site.js sur les éléments .reveal).
 */
declare(strict_types=1);

require_once __DIR__ . '/carte-zones.php';

const CITOYENS_TOTAL = 52;

/*
 * Chaque critère : liste de [effectif, libellé, pourcentage affiché].
 * Les pourcentages sont ceux communiqués (arrondis), pas recalculés.
 */
const CITOYENS_GENRE = [
    [26, 'femmes', '50 %', 'rouge'],
    [26, 'hommes', '50 %', 'bleu'],
];

const CITOYENS_AGE = [
    [7, '18 – 24 ans', '13,5 %'],
    [6, '25 – 34 ans', '11,5 %'],
    [17, '35 – 49 ans', '33 %'],
    [12, '50 – 64 ans', '23 %'],
    [10, '65 ans et plus', '19 %'],
];

const CITOYENS_DIPLOME = [
    [4, 'Sans diplôme, CEP ou brevet', '8 %'],
    [9, 'CAP ou BEP', '17 %'],
    [14, 'Baccalauréat', '27 %'],
    [25, 'Diplôme supérieur au bac', '48 %'],
];

/* Zones : identifiant de contour (lib/carte-zones.php) => données. x/y :
   position de la pastille dans la viewBox de la carte (centre de la zone). */
const CITOYENS_ZONES = [
    'no'  => ['nom' => 'Nord-Ouest', 'n' => 11, 'pct' => '21 %', 'x' => 180, 'y' => 190],
    'ne'  => ['nom' => 'Nord-Est', 'n' => 11, 'pct' => '21 %', 'x' => 392, 'y' => 170],
    'idf' => ['nom' => 'Île-de-France', 'n' => 11, 'pct' => '21 %', 'x' => 290, 'y' => 143],
    'so'  => ['nom' => 'Sud-Ouest', 'n' => 11, 'pct' => '21 %', 'x' => 228, 'y' => 392],
    'se'  => ['nom' => 'Sud-Est', 'n' => 8, 'pct' => '15 %', 'x' => 410, 'y' => 380],
];

const CITOYENS_TERRITOIRE = [
    [9, 'Rural isolé', '17 %'],
    [5, 'Rural intermédiaire', '10 %'],
    [12, 'Urbain intermédiaire', '23 %'],
    [26, 'Urbain dense', '50 %'],
];
const CITOYENS_QPV = '27 %';

const CITOYENS_SOCIOPRO = [
    [7, 'Agriculteur.rices, artisan.es, commerçant.es, chef.fes d’entreprise', '13,5 %'],
    [4, 'Cadres supérieur.es et professions libérales', '8 %'],
    [7, 'Professions intermédiaires', '13,5 %'],
    [8, 'Employé.es et ouvrier.es', '15 %'],
    [12, 'Retraité.es', '23 %'],
    [14, 'Sans activité professionnelle', '27 %'],
];

const CITOYENS_METIERS = [
    'Aiguilleur de rail à la SNCF',
    'Artisan menuisier',
    'Auto-entrepreneur dans l’informatique',
    'BTP (électricien, ouvrier, chef d’entreprise)',
    'Chargé de projet',
    'Chargée d’administration et de production pour un festival de musique',
    'Chargée de mission pour les arts visuels pour une commune',
    'Coach sportive',
    'Comptable',
    'Consultant immobilier',
    'Coordinatrice AESH',
    'CPE',
    'Directeur de restaurant',
    'Étudiant.es',
    'Fonctionnaire',
    'Gérant d’une SARL',
    'Hypnothérapeute',
    'Infirmière',
    'Ingénieur',
    'Mannequin',
    'Orthophoniste',
    'Professeure des écoles',
    'Recherche d’emploi',
    'Référents sécurité sur un campus universitaire',
    'Responsable d’équipe',
    'Retraité.es',
    'Salarié',
];

/* Intérêt pour la politique, du « non » le plus franc au « oui » le plus net :
   la barre diverge autour de la frontière non / oui. */
const CITOYENS_POLITIQUE = [
    [3, 'Non, pas du tout', '6 %', 'non'],
    [13, 'Non, plutôt pas', '25 %', 'non'],
    [24, 'Oui, plutôt', '46 %', 'oui'],
    [12, 'Oui, beaucoup', '23 %', 'oui'],
];

/** Critères, dans l'ordre de la page : ancre => libellé du sommaire. */
const CITOYENS_SOMMAIRE = [
    'genre' => 'Genre',
    'age' => 'Âge',
    'diplome' => 'Diplôme',
    'region' => 'Région',
    'territoire' => 'Territoire',
    'profession' => 'Profession',
    'politique' => 'Politique',
];

/**
 * Pictogramme « personne », déclaré une fois et réutilisé par chaque
 * pastille (<use>). Le disque prend la couleur courante (currentColor).
 */
function citoyens_glyph_symbol(): void
{
    ?>
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <symbol id="glyph-personne" viewBox="0 0 26 26">
    <circle cx="13" cy="13" r="13" fill="currentColor"/>
    <circle cx="13" cy="9.6" r="3.7" fill="#fff"/>
    <path d="M6.4 20.4c.5-3.8 3.1-6.2 6.6-6.2s6.1 2.4 6.6 6.2z" fill="#fff"/>
  </symbol>
</svg>
<?php
}

/**
 * Grille de $n pictogrammes, $cols par ligne. Chaque pictogramme reçoit un
 * délai d'apparition pseudo-aléatoire (graine fixe tirée de $key : le rendu
 * est identique d'un chargement à l'autre). $colors, facultatif, donne une
 * classe de couleur par pictogramme (sinon : couleur d'accent de la section).
 */
function citoyens_glyphs(int $n, string $key, int $cols = 13, array $colors = [], string $class = ''): string
{
    $pitch = 30;
    $size = 26;
    $used_cols = min($n, $cols);
    $rows = (int) ceil($n / $cols);
    $w = $used_cols * $pitch - ($pitch - $size);
    $h = $rows * $pitch - ($pitch - $size);

    mt_srand(crc32($key));
    $out = sprintf(
        '<svg class="cit-glyphs%s" viewBox="0 0 %d %d" width="%d" height="%d" aria-hidden="true" focusable="false">',
        $class !== '' ? ' ' . $class : '',
        $w, $h, $w, $h
    );
    for ($i = 0; $i < $n; $i++) {
        $x = ($i % $cols) * $pitch;
        $y = intdiv($i, $cols) * $pitch;
        $delay = mt_rand(0, 60) * 10 + intdiv($i, $cols) * 60;
        $color = isset($colors[$i]) ? ' c-' . $colors[$i] : '';
        $out .= sprintf(
            '<g transform="translate(%d %d)"><g class="cit-glyph%s" style="--d:%dms"><use href="#glyph-personne" width="%d" height="%d"/></g></g>',
            $x, $y, $color, $delay, $size, $size
        );
    }
    mt_srand();
    return $out . '</svg>';
}

/**
 * Une ligne « chiffre surligné + libellé + pourcentage | pictogrammes ».
 * $color : couleur des pictogrammes si elle diffère de l'accent de la section.
 */
function citoyens_row(array $item, string $key, ?string $color = null): void
{
    [$n, $label, $pct] = $item;
    ?>
      <div class="cit-row reveal"<?= $color ? ' style="--accent: var(--' . e($color) . ')"' : '' ?>>
        <p class="cit-row-text">
          <span class="cit-num"><span class="cit-hl" data-count="<?= $n ?>"><?= $n ?></span></span>
          <span class="cit-label"><?= e($label) ?></span>
          <span class="cit-pct tech"><?= e($pct) ?></span>
        </p>
        <?= citoyens_glyphs($n, $key) ?>
      </div>
<?php
}

/** En-tête d'une section de critère. */
function citoyens_section_header(int $num, string $titre, string $intro = ''): void
{
    ?>
    <div class="cit-section-header reveal">
      <span class="cit-section-num tech"><?= sprintf('%02d', $num) ?> / <?= sprintf('%02d', count(CITOYENS_SOMMAIRE)) ?></span>
      <h2 class="title sub-title"><?= e($titre) ?></h2>
<?php if ($intro !== ''): ?>
      <p class="cit-section-intro"><?= e($intro) ?></p>
<?php endif; ?>
    </div>
<?php
}

/** Carte des 5 zones, pastilles proportionnelles à l'effectif, et légende. */
function citoyens_carte(): void
{
    $label = implode(', ', array_map(
        fn ($z) => $z['nom'] . ' : ' . $z['n'],
        CITOYENS_ZONES
    ));
    $i = 0;
    ?>
      <div class="cit-carte reveal">
        <svg class="cit-carte-svg" viewBox="<?= CARTE_ZONES_VIEWBOX ?>" role="img" aria-label="<?= e('Carte de la France métropolitaine en 5 zones. ' . $label) ?>">
<?php foreach (CARTE_ZONES_PATHS as $id => $d): ?>
          <path class="cit-zone" data-zone="<?= $id ?>" d="<?= $d ?>" fill-rule="evenodd"/>
<?php endforeach; ?>
<?php foreach (CITOYENS_ZONES as $id => $z):
    // Aire du disque proportionnelle à l'effectif.
    $r = round(9 * sqrt($z['n']), 1);
?>
          <g class="cit-pastille" data-zone="<?= $id ?>" style="--d:<?= 150 + $i++ * 140 ?>ms">
            <circle cx="<?= $z['x'] ?>" cy="<?= $z['y'] ?>" r="<?= $r ?>"/>
            <text x="<?= $z['x'] ?>" y="<?= $z['y'] ?>" text-anchor="middle" dominant-baseline="central"><?= $z['n'] ?></text>
          </g>
<?php endforeach; ?>
        </svg>

        <ul class="cit-zones">
<?php foreach (CITOYENS_ZONES as $id => $z): ?>
          <li class="cit-zones-item" data-zone="<?= $id ?>" tabindex="0">
            <span class="cit-num"><span class="cit-hl" data-count="<?= $z['n'] ?>"><?= $z['n'] ?></span></span>
            <span class="cit-label"><?= e($z['nom']) ?></span>
            <span class="cit-pct tech"><?= e($z['pct']) ?></span>
          </li>
<?php endforeach; ?>
        </ul>
      </div>
<?php
}

/** Barre divergente « non / oui » de l'intérêt pour la politique. */
function citoyens_opinion(): void
{
    $non = $oui = 0;
    foreach (CITOYENS_POLITIQUE as [$n, , , $camp]) {
        if ($camp === 'non') {
            $non += $n;
        } else {
            $oui += $n;
        }
    }
    $pct = fn (int $n) => round($n / CITOYENS_TOTAL * 100);
    ?>
      <div class="cit-opinion reveal">
        <div class="cit-opinion-axis tech">
          <span>Non · <?= $pct($non) ?> %</span>
          <span>Oui · <?= $pct($oui) ?> %</span>
        </div>
        <div class="cit-opinion-bar" style="--non: <?= $non / CITOYENS_TOTAL * 100 ?>%" aria-hidden="true">
<?php foreach (CITOYENS_POLITIQUE as $i => [$n, $label, , $camp]): ?>
          <span class="cit-opinion-seg o<?= $i + 1 ?> <?= $camp ?>" style="flex-grow: <?= $n ?>; --d: <?= abs($i - 1.5) * 220 ?>ms"></span>
<?php endforeach; ?>
        </div>
        <ul class="cit-opinion-legend">
<?php foreach (CITOYENS_POLITIQUE as $i => [$n, $label, $p, $camp]): ?>
          <li style="--accent: var(--<?= $camp === 'non' ? 'bleu' : 'rouge' ?>)">
            <span class="cit-swatch o<?= $i + 1 ?>" aria-hidden="true"></span>
            <span class="cit-num"><span class="cit-hl" data-count="<?= $n ?>"><?= $n ?></span></span>
            <span class="cit-label"><?= e($label) ?></span>
            <span class="cit-pct tech"><?= e($p) ?></span>
          </li>
<?php endforeach; ?>
        </ul>
      </div>
<?php
}

/**
 * Pictogrammes de l'en-tête : les 52 citoyen.nes, en rouge, bleu et encre
 * mêlés (répartition pseudo-aléatoire mais stable).
 */
function citoyens_hero_glyphs(): string
{
    mt_srand(crc32('hero-couleurs'));
    $palette = ['rouge', 'bleu', 'encre'];
    $colors = [];
    for ($i = 0; $i < CITOYENS_TOTAL; $i++) {
        $colors[] = $palette[mt_rand(0, 2)];
    }
    return citoyens_glyphs(CITOYENS_TOTAL, 'hero', 13, $colors, 'cit-glyphs-hero');
}
