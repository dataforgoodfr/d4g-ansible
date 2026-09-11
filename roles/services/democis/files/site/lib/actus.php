<?php
/**
 * Actualités : stockage et helpers partagés par le site public et le back-office.
 *
 * Chaque actualité vit dans son propre dossier actus/data/<slug>/ :
 *   - actu.json   : titre, date (AAAA-MM-JJ), texte brut, image, à la une
 *   - photo.<ext> : la photo (facultative)
 * Le slug (<date>-<titre-simplifié>) est fixé à la création : il sert de lien
 * de partage (?actu=<slug>) et ne doit donc plus changer ensuite.
 *
 * Aucune base de données : un simple dossier, sauvegardé tel quel.
 */
declare(strict_types=1);

const ACTUS_DATA_DIR = __DIR__ . '/../actus/data';
const ACTUS_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
const ACTUS_HOME_SECONDARY = 3;
const ACTUS_EXCERPT_LENGTH = 160;

const SITE_NAME = 'Convention Citoyenne pour la Démocratie';
const SITE_DESCRIPTION = 'Convention Citoyenne pour la Démocratie. Près de 80 citoyens, parlementaires et membres de la société civile délibèrent à Lille pour répondre à la crise démocratique avant l’élection présidentielle de 2027.';

/** Échappement HTML court, utilisé partout dans les templates. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * URL absolue du site, sans slash final. Fixée par SITE_URL (docker), sinon
 * déduite de la requête (Traefik transmet X-Forwarded-Proto).
 */
function site_url(): string
{
    $env = getenv('SITE_URL');
    if ($env !== false && $env !== '') {
        return rtrim($env, '/');
    }
    $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? (($_SERVER['HTTPS'] ?? '') !== '' ? 'https' : 'http');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $proto . '://' . $host;
}

function actu_dir(string $slug): string
{
    return ACTUS_DATA_DIR . '/' . $slug;
}

/** Un slug ne contient que [a-z0-9-] : c'est aussi la garde anti path traversal. */
function actu_slug_is_valid(string $slug): bool
{
    return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
}

function slugify(string $s): string
{
    $s = mb_strtolower($s, 'UTF-8');
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
    if ($ascii === false) {
        // iconv indisponible ou caractères non translittérables : on retire les accents les plus courants.
        $ascii = strtr($s, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ç' => 'c', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'í' => 'i', 'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ú' => 'u', 'ÿ' => 'y', 'œ' => 'oe', 'æ' => 'ae', 'ñ' => 'n',
        ]);
    }
    $ascii = preg_replace('/[^a-z0-9]+/', '-', $ascii);
    return trim($ascii, '-');
}

/** Slug unique pour une nouvelle actualité : <date>-<titre>, suffixé si déjà pris. */
function actu_new_slug(string $date, string $titre): string
{
    $base = $date . '-' . (slugify($titre) ?: 'actualite');
    $base = mb_substr($base, 0, 90);
    $base = trim($base, '-');
    $slug = $base;
    for ($i = 2; is_dir(actu_dir($slug)); $i++) {
        $slug = $base . '-' . $i;
    }
    return $slug;
}

/** Nom du fichier photo présent dans le dossier, ou null. */
function actu_find_image(string $slug): ?string
{
    foreach (ACTUS_IMAGE_EXTENSIONS as $ext) {
        if (is_file(actu_dir($slug) . '/photo.' . $ext)) {
            return 'photo.' . $ext;
        }
    }
    return null;
}

/**
 * Charge une actualité. Retourne null si le slug est invalide ou inconnu.
 * Le tableau contient toujours : slug, titre, date, texte, une, image, image_url.
 */
function actu_load(string $slug): ?array
{
    if (!actu_slug_is_valid($slug)) {
        return null;
    }
    $file = actu_dir($slug) . '/actu.json';
    if (!is_file($file)) {
        return null;
    }
    $data = json_decode((string) file_get_contents($file), true);
    if (!is_array($data)) {
        return null;
    }
    $image = actu_find_image($slug);
    return [
        'slug' => $slug,
        'titre' => (string) ($data['titre'] ?? ''),
        'date' => (string) ($data['date'] ?? ''),
        'texte' => (string) ($data['texte'] ?? ''),
        'une' => (bool) ($data['une'] ?? false),
        'image' => $image,
        'image_url' => $image ? 'actus/data/' . $slug . '/' . $image : null,
    ];
}

/** Toutes les actualités, de la plus récente à la plus ancienne. */
function actus_load_all(): array
{
    if (!is_dir(ACTUS_DATA_DIR)) {
        return [];
    }
    $actus = [];
    foreach (scandir(ACTUS_DATA_DIR) as $entry) {
        if ($entry[0] === '.' || !is_dir(ACTUS_DATA_DIR . '/' . $entry)) {
            continue;
        }
        $actu = actu_load($entry);
        if ($actu !== null) {
            $actus[] = $actu;
        }
    }
    usort($actus, static function (array $a, array $b): int {
        return [$b['date'], $b['slug']] <=> [$a['date'], $a['slug']];
    });
    return $actus;
}

/** Écrit actu.json (la photo est gérée à part, voir le back-office). */
function actu_save(string $slug, array $data): void
{
    if (!actu_slug_is_valid($slug)) {
        throw new InvalidArgumentException('Slug invalide');
    }
    $dir = actu_dir($slug);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Impossible de créer le dossier de l’actualité');
    }
    $json = json_encode([
        'titre' => (string) $data['titre'],
        'date' => (string) $data['date'],
        'texte' => (string) $data['texte'],
        'une' => (bool) $data['une'],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (file_put_contents($dir . '/actu.json', $json . "\n", LOCK_EX) === false) {
        throw new RuntimeException('Impossible d’enregistrer l’actualité');
    }
}

function actu_delete(string $slug): void
{
    if (!actu_slug_is_valid($slug)) {
        throw new InvalidArgumentException('Slug invalide');
    }
    $dir = actu_dir($slug);
    if (!is_dir($dir)) {
        return;
    }
    foreach (scandir($dir) as $entry) {
        if ($entry !== '.' && $entry !== '..') {
            unlink($dir . '/' . $entry);
        }
    }
    rmdir($dir);
}

/**
 * Sélection pour l'accueil : l'actu « à la une » (la plus récente si plusieurs)
 * en principal, sinon la plus récente ; puis les suivantes en secondaires.
 */
function actus_home_selection(array $actus): array
{
    if ($actus === []) {
        return ['main' => null, 'secondary' => []];
    }
    $main = null;
    foreach ($actus as $actu) {
        if ($actu['une']) {
            $main = $actu;
            break;
        }
    }
    $main ??= $actus[0];
    $secondary = array_values(array_filter($actus, static fn(array $a): bool => $a['slug'] !== $main['slug']));
    return ['main' => $main, 'secondary' => array_slice($secondary, 0, ACTUS_HOME_SECONDARY)];
}

/** « 20 juillet 2026 » à partir de « 2026-07-20 ». */
function date_fr(string $iso): string
{
    static $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $iso, $m)) {
        return $iso;
    }
    $jour = (int) $m[3];
    return ($jour === 1 ? '1er' : (string) $jour) . ' ' . $mois[((int) $m[2]) - 1] . ' ' . $m[1];
}

/** Date RFC 2822 pour le flux RSS. */
function date_rss(string $iso): string
{
    $ts = strtotime($iso . ' 00:00:00 UTC');
    return gmdate('D, d M Y H:i:s', $ts === false ? 0 : $ts) . ' +0000';
}

/** Extrait en texte brut, coupé au mot le plus proche, terminé par « … ». */
function actu_excerpt(string $texte, int $max = ACTUS_EXCERPT_LENGTH): string
{
    $flat = trim((string) preg_replace('/\s+/u', ' ', $texte));
    if (mb_strlen($flat, 'UTF-8') <= $max) {
        return $flat;
    }
    $cut = mb_substr($flat, 0, $max, 'UTF-8');
    $space = mb_strrpos($cut, ' ', 0, 'UTF-8');
    if ($space !== false && $space > $max / 2) {
        $cut = mb_substr($cut, 0, $space, 'UTF-8');
    }
    return rtrim($cut, " ,;:") . '…';
}

/**
 * Texte brut → HTML : une ligne vide sépare les paragraphes, un simple retour
 * à la ligne devient <br>, les URLs deviennent des liens. Tout est échappé :
 * le back-office ne permet pas de saisir du HTML.
 */
function texte_to_html(string $texte): string
{
    $texte = str_replace(["\r\n", "\r"], "\n", trim($texte));
    if ($texte === '') {
        return '';
    }
    $html = '';
    foreach (preg_split('/\n{2,}/', $texte) as $para) {
        // Découpe URL / texte avant l'échappement, pour que la ponctuation
        // finale (« . », « ) »…) reste hors du lien.
        $parts = preg_split('~(https?://[^\s]+[^\s.,;:!?)»"\'])~u', trim($para), -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = '';
        foreach ($parts as $i => $part) {
            $out .= $i % 2 === 1
                ? '<a href="' . e($part) . '" target="_blank" rel="noopener">' . e($part) . '</a>'
                : e($part);
        }
        $html .= '<p>' . nl2br($out, false) . "</p>\n";
    }
    return $html;
}

/** Représentation d'une actu pour get-actu.php (la pop-in). */
function actu_to_json(array $actu): array
{
    return [
        'titre' => $actu['titre'],
        'date' => $actu['date'],
        'date_formatee' => date_fr($actu['date']),
        'image_url' => $actu['image_url'],
        'texte_html' => texte_to_html($actu['texte']),
    ];
}
