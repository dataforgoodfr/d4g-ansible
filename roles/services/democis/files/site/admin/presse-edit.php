<?php
/** Back-office : ajout (sans ?id) ou modification (?id=…) d'un article « On en parle ». */
require_once __DIR__ . '/lib.php';

admin_session_start();
admin_require_login();

$items = presse_load_all();
$id = isset($_GET['id']) ? (string) $_GET['id'] : null;
$index = $id !== null ? presse_find($items, $id) : null;
if ($id !== null && $index === null) {
    admin_flash('error', 'Cet article n’existe pas (ou plus).');
    admin_redirect('presse.php');
}
$is_new = $index === null;
$item = $is_new ? null : $items[$index];

$form = [
    'media' => $item['media'] ?? '',
    'titre' => $item['titre'] ?? '',
    'url' => $item['url'] ?? '',
    'date' => $item['date'] ?? '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST === [] && $_FILES === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    // Envoi dépassant post_max_size : voir edit.php.
    $errors[] = 'Le logo est trop lourd (8 Mo maximum) : l’article n’a pas été enregistré.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_csrf();

    foreach (['media', 'titre', 'url'] as $field) {
        $form[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    if ($form['media'] === '') {
        $errors[] = 'Le nom du média est obligatoire.';
    } elseif (mb_strlen($form['media']) > 80) {
        $errors[] = 'Le nom du média ne doit pas dépasser 80 caractères.';
    }
    if (mb_strlen($form['titre']) > 300) {
        $errors[] = 'Le titre ne doit pas dépasser 300 caractères.';
    }
    if (!presse_url_is_valid($form['url'])) {
        $errors[] = 'Le lien de l’article doit être une adresse web complète (https://…).';
    }
    [$logo_ext, $logo_error] = admin_check_image_upload($_FILES['logo'] ?? [], 'le logo');
    if ($logo_error !== null) {
        $errors[] = $logo_error;
    }

    if ($errors === []) {
        try {
            $new = ['id' => $item['id'] ?? presse_new_id()] + $form + ['logo' => $item['logo'] ?? ''];
            $old_logo = $new['logo'];
            if (!empty($_POST['remove_logo'])) {
                $new['logo'] = '';
            }
            if ($logo_ext !== null) {
                if (!is_dir(PRESSE_DIR) && !mkdir(PRESSE_DIR, 0775, true) && !is_dir(PRESSE_DIR)) {
                    throw new RuntimeException('Impossible de créer le dossier de la presse');
                }
                // Nom unique à chaque envoi : le navigateur ne garde pas l'ancien logo en cache.
                $name = 'logo-' . $new['id'] . '-' . bin2hex(random_bytes(3)) . '.' . $logo_ext;
                if (!move_uploaded_file($_FILES['logo']['tmp_name'], PRESSE_DIR . '/' . $name)) {
                    throw new RuntimeException('Impossible d’enregistrer le logo sur le serveur');
                }
                $new['logo'] = PRESSE_UPLOAD_PREFIX . $name;
            }

            if ($is_new) {
                array_unshift($items, $new);
            } else {
                $items[$index] = $new;
            }
            presse_save_all($items);
            if ($new['logo'] !== $old_logo) {
                presse_remove_logo($old_logo);
            }
            admin_flash('success', $is_new ? 'Article ajouté.' : 'Article mis à jour.');
            admin_redirect('presse.php');
        } catch (Throwable $ex) {
            $errors[] = 'Enregistrement impossible : ' . $ex->getMessage();
        }
    }
}

admin_page_start($is_new ? 'Nouvel article' : 'Modifier un article');
?>
  <div class="admin-wrap">
    <div class="admin-header">
      <h1><?= $is_new ? 'Nouvel article' : 'Modifier un article' ?></h1>
      <a href="presse.php" class="btn secondary">← Retour à la liste</a>
    </div>

<?php if ($errors !== []): ?>
    <div class="admin-alert error"><?= implode('<br>', array_map('e', $errors)) ?></div>
<?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="admin-form">
      <?= admin_csrf_field() ?>

      <div class="admin-field">
        <label for="media">Média</label>
        <input type="text" id="media" name="media" required maxlength="80" placeholder="Le Monde" value="<?= e($form['media']) ?>">
        <div class="hint">Affiché à la place du logo s’il n’y en a pas.</div>
      </div>

      <div class="admin-field">
        <label for="titre">Titre de l’article</label>
        <input type="text" id="titre" name="titre" maxlength="300" value="<?= e($form['titre']) ?>">
        <div class="hint">Facultatif et non affiché sur la page : il apparaît seulement en infobulle au survol du logo, et vous aide à vous repérer dans cette liste.</div>
      </div>

      <div class="admin-field">
        <label for="url">Lien de l’article</label>
        <input type="url" id="url" name="url" required placeholder="https://…" value="<?= e($form['url']) ?>">
      </div>

      <div class="admin-field">
        <label for="logo">Logo du média</label>
        <input type="file" id="logo" name="logo" accept="image/png,image/webp,image/jpeg">
        <div class="hint">C’est lui qui est affiché sur la page. PNG ou WebP à fond transparent de préférence (JPEG accepté), en couleur, recadré au plus près du logo.</div>
<?php if (!$is_new && $item['logo'] !== ''): ?>
        <div class="admin-current-image">
          <img src="../<?= e($item['logo']) ?>" alt="" style="max-height:48px;">
          <label style="display:inline-flex;gap:8px;align-items:center;font-weight:400;margin-top:8px;">
            <input type="checkbox" name="remove_logo" value="1"> Supprimer le logo actuel
          </label>
        </div>
<?php endif; ?>
      </div>

      <div class="admin-actions">
        <button type="submit" class="btn"><?= $is_new ? 'Ajouter' : 'Enregistrer' ?></button>
        <a href="presse.php" class="btn secondary">Annuler</a>
      </div>
    </form>
  </div>
<?php
admin_page_end();
