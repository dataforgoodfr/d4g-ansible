<?php
/**
 * « On en parle » : articles de presse sur la Convention, affichés sous les
 * actualités de l'accueil et gérés depuis le back-office (admin/presse.php).
 *
 * Tout tient dans actus/presse/presse.json, dans l'ordre d'affichage :
 *   [{id, media, titre, url, date (AAAA-MM-JJ ou vide), logo}, …]
 * logo est un chemin relatif à la racine du site : un logo envoyé depuis le
 * back-office (actus/presse/logo-<id>.<ext>) ou un logo livré avec le site
 * (images/presse/…).
 *
 * Tant que presse.json n'existe pas, la liste de départ lib/presse-defaut.json
 * est utilisée ; le premier enregistrement depuis le back-office la recopie.
 */
declare(strict_types=1);

require_once __DIR__ . '/actus.php';

const PRESSE_DIR = __DIR__ . '/../actus/presse';
const PRESSE_FILE = PRESSE_DIR . '/presse.json';
const PRESSE_DEFAULT_FILE = __DIR__ . '/presse-defaut.json';
/** Préfixe des logos envoyés depuis le back-office (les seuls qu'on supprime). */
const PRESSE_UPLOAD_PREFIX = 'actus/presse/';

/** Tous les articles, dans l'ordre d'affichage. */
function presse_load_all(): array
{
    $file = is_file(PRESSE_FILE) ? PRESSE_FILE : PRESSE_DEFAULT_FILE;
    $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
    if (!is_array($data)) {
        return [];
    }
    $items = [];
    foreach ($data as $row) {
        if (!is_array($row) || !presse_id_is_valid((string) ($row['id'] ?? ''))) {
            continue;
        }
        $items[] = [
            'id' => (string) $row['id'],
            'media' => (string) ($row['media'] ?? ''),
            'titre' => (string) ($row['titre'] ?? ''),
            'url' => (string) ($row['url'] ?? ''),
            'date' => (string) ($row['date'] ?? ''),
            'logo' => (string) ($row['logo'] ?? ''),
        ];
    }
    return $items;
}

function presse_save_all(array $items): void
{
    if (!is_dir(PRESSE_DIR) && !mkdir(PRESSE_DIR, 0775, true) && !is_dir(PRESSE_DIR)) {
        throw new RuntimeException('Impossible de créer le dossier de la presse');
    }
    $json = json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (file_put_contents(PRESSE_FILE, $json . "\n", LOCK_EX) === false) {
        throw new RuntimeException('Impossible d’enregistrer la liste des articles');
    }
}

function presse_id_is_valid(string $id): bool
{
    return (bool) preg_match('/^[a-z0-9-]{1,40}$/', $id);
}

/** Position d'un article dans la liste, ou null. */
function presse_find(array $items, string $id): ?int
{
    foreach ($items as $i => $item) {
        if ($item['id'] === $id) {
            return $i;
        }
    }
    return null;
}

function presse_new_id(): string
{
    return bin2hex(random_bytes(6));
}

/** Supprime un logo envoyé depuis le back-office (jamais ceux livrés avec le site). */
function presse_remove_logo(string $logo): void
{
    if (!str_starts_with($logo, PRESSE_UPLOAD_PREFIX)) {
        return;
    }
    $path = PRESSE_DIR . '/' . basename($logo);
    if (is_file($path)) {
        unlink($path);
    }
}

/** Seules les adresses http(s) sont acceptées comme lien d'article. */
function presse_url_is_valid(string $url): bool
{
    return (bool) preg_match('~^https?://[^\s/$.?#][^\s]*$~i', $url) && filter_var($url, FILTER_VALIDATE_URL) !== false;
}
