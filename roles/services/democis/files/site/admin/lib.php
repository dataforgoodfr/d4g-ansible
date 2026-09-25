<?php
/**
 * Back-office actualités : session, authentification, CSRF et gabarit.
 *
 * Un seul mot de passe, fourni par la variable d'environnement
 * DEMOCIS_ADMIN_PASSWORD (voir templates/democis.env.j2 du rôle Ansible).
 * Sans cette variable, le back-office refuse toute connexion.
 */
declare(strict_types=1);

require_once __DIR__ . '/../lib/actus.php';
require_once __DIR__ . '/../lib/presse.php';

const ADMIN_MAX_UPLOAD_BYTES = 8 * 1024 * 1024;

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || ($_SERVER['HTTPS'] ?? '') !== '';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => $https,
        'samesite' => 'Lax',
    ]);
    session_name('democis_admin');
    session_start();
}

function admin_password_configured(): bool
{
    $pw = getenv('DEMOCIS_ADMIN_PASSWORD');
    return $pw !== false && $pw !== '';
}

function admin_check_password(string $candidate): bool
{
    if (!admin_password_configured()) {
        return false;
    }
    return hash_equals((string) getenv('DEMOCIS_ADMIN_PASSWORD'), $candidate);
}

function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function admin_login(): void
{
    session_regenerate_id(true);
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $p['path'],
            'domain' => $p['domain'],
            'secure' => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'],
        ]);
    }
    session_destroy();
}

/** Redirige vers la page de connexion si besoin. */
function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function admin_csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function admin_csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(admin_csrf_token()) . '">';
}

/** À appeler sur tout POST authentifié : 403 si le jeton ne correspond pas. */
function admin_require_csrf(): void
{
    $token = (string) ($_POST['csrf'] ?? '');
    if ($token === '' || !hash_equals(admin_csrf_token(), $token)) {
        http_response_code(403);
        exit('Jeton de sécurité invalide, veuillez recharger la page et réessayer.');
    }
}

/** Message affiché une seule fois sur la page suivante (après enregistrement, suppression…). */
function admin_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function admin_take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function admin_redirect(string $to): never
{
    header('Location: ' . $to);
    exit;
}

/**
 * Vérifie une image envoyée ($label : « la photo », « le logo »…). Retourne
 * [extension, null] si elle est valable, [null, message d'erreur] sinon, et
 * [null, null] quand aucun fichier n'a été envoyé.
 */
function admin_check_image_upload(array $file, string $label): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    $too_big = ucfirst($label) . ' est trop lourd' . ($label === 'la photo' ? 'e' : '') . ' (8 Mo maximum).';
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return [null, $too_big];
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return [null, 'L’envoi de ' . $label . ' a échoué, veuillez réessayer.'];
    }
    if ($file['size'] > ADMIN_MAX_UPLOAD_BYTES) {
        return [null, $too_big];
    }
    $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if ($ext === null) {
        return [null, 'Format non pris en charge pour ' . $label . ' : utilisez une image JPEG, PNG ou WebP.'];
    }
    return [$ext, null];
}

/**
 * Enregistre la photo envoyée dans le dossier de l'actu (photo.<ext>), en
 * remplaçant l'éventuelle photo précédente. Retourne un message d'erreur, ou
 * null si tout s'est bien passé (y compris quand aucun fichier n'est envoyé).
 */
function admin_store_upload(string $slug, array $file): ?string
{
    [$ext, $error] = admin_check_image_upload($file, 'la photo');
    if ($ext === null) {
        return $error;
    }
    admin_remove_image($slug);
    if (!move_uploaded_file($file['tmp_name'], actu_dir($slug) . '/photo.' . $ext)) {
        return 'Impossible d’enregistrer la photo sur le serveur.';
    }
    return null;
}

function admin_remove_image(string $slug): void
{
    foreach (ACTUS_IMAGE_EXTENSIONS as $ext) {
        $path = actu_dir($slug) . '/photo.' . $ext;
        if (is_file($path)) {
            unlink($path);
        }
    }
}

/**
 * Télécharge la miniature de la vidéo dans le dossier de l'actu
 * (video-<id>-<n>.jpg) et supprime les précédentes. On essaie d'abord la
 * miniature verticale d'un Short (oar<n>), puis les miniatures 16:9 et 4:3.
 * Sans accès à YouTube, le site se rabat sur la miniature servie par YouTube.
 */
function admin_sync_video_thumb(string $slug, ?string $youtube_id, int $frame): void
{
    $keep = $youtube_id !== null ? actu_video_thumb_name($youtube_id, $frame) : null;
    foreach (glob(actu_dir($slug) . '/video-*.jpg') ?: [] as $path) {
        if (basename($path) !== $keep) {
            unlink($path);
        }
    }
    if ($keep === null || is_file(actu_dir($slug) . '/' . $keep)) {
        return;
    }
    $context = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
    foreach (['oar', 'maxres', 'hq'] as $prefix) {
        $variant = $prefix . $frame;
        $url = 'https://i.ytimg.com/vi/' . $youtube_id . '/' . $variant . '.jpg';
        $data = @file_get_contents($url, false, $context);
        $status = $http_response_header[0] ?? '';
        // YouTube répond 404 avec une image grise de 120×90 quand la variante n'existe pas.
        if ($data === false || !str_contains($status, ' 200')) {
            continue;
        }
        $size = @getimagesizefromstring($data);
        if ($size === false || $size[2] !== IMAGETYPE_JPEG || $size[0] <= 120) {
            continue;
        }
        file_put_contents(actu_dir($slug) . '/' . $keep, $data, LOCK_EX);
        return;
    }
}

/* ------------------------------------------------------------------------
   Gabarit
   ------------------------------------------------------------------------ */

function admin_page_start(string $title): void
{
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> — Back office</title>
<link rel="icon" type="image/png" href="../images/favicon.ico">
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php if (admin_is_logged_in()):
    $current = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $on_presse = str_starts_with($current, 'presse');
?>
  <div class="admin-topbar">
    <span class="admin-topbar-left">
      <a href="index.php" class="brand">Back office</a>
      <nav class="admin-tabs">
        <a href="index.php"<?= $on_presse ? '' : ' class="active"' ?>>Actualités</a>
        <a href="presse.php"<?= $on_presse ? ' class="active"' : '' ?>>On en parle</a>
      </nav>
    </span>
    <span>
      <a href="../" class="logout" target="_blank" rel="noopener">Voir le site ↗</a>
      &nbsp;·&nbsp;
      <a href="logout.php" class="logout">Se déconnecter</a>
    </span>
  </div>
<?php endif; ?>
<?php
}

function admin_page_end(): void
{
    ?>
</body>
</html>
<?php
}

function admin_alert(?array $flash): void
{
    if ($flash === null) {
        return;
    }
    ?>
<div class="admin-alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php
}
