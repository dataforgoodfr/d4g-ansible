<?php
/**
 * Contenu d'une actualité au format JSON, pour la pop-in (assets/actus.js).
 * GET get-actu.php?slug=<slug>
 */
require_once __DIR__ . '/lib/actus.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');

$actu = isset($_GET['slug']) ? actu_load((string) $_GET['slug']) : null;
if ($actu === null) {
    http_response_code(404);
    echo json_encode(['error' => 'Actualité introuvable'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(actu_to_json($actu), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
