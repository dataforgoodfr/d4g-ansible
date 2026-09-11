<?php
/** Back-office : suppression d'une actualité (POST uniquement). */
require_once __DIR__ . '/lib.php';

admin_session_start();
admin_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    admin_redirect('index.php');
}
admin_require_csrf();

$slug = (string) ($_POST['slug'] ?? '');
$actu = actu_load($slug);
if ($actu === null) {
    admin_flash('error', 'Cette actualité n’existe pas (ou plus).');
} else {
    actu_delete($slug);
    admin_flash('success', 'Actualité « ' . $actu['titre'] . ' » supprimée.');
}
admin_redirect('index.php');
