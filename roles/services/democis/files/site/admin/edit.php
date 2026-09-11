<?php
/** Back-office : création (sans ?slug) ou modification (?slug=…) d'une actualité. */
require_once __DIR__ . '/lib.php';

admin_session_start();
admin_require_login();

$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : null;
$actu = $slug !== null ? actu_load($slug) : null;
if ($slug !== null && $actu === null) {
    admin_flash('error', 'Cette actualité n’existe pas (ou plus).');
    admin_redirect('index.php');
}
$is_new = $actu === null;

$form = [
    'titre' => $actu['titre'] ?? '',
    'date' => $actu['date'] ?? date('Y-m-d'),
    'texte' => $actu['texte'] ?? '',
    'une' => $actu['une'] ?? false,
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST === [] && $_FILES === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    // Envoi dépassant post_max_size : PHP vide $_POST et $_FILES, le formulaire
    // est perdu et le jeton CSRF avec. On explique plutôt que de renvoyer un 403.
    $errors[] = 'La photo est trop lourde (8 Mo maximum) : l’actualité n’a pas été enregistrée.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_csrf();

    $form['titre'] = trim((string) ($_POST['titre'] ?? ''));
    $form['date'] = trim((string) ($_POST['date'] ?? ''));
    $form['texte'] = trim(str_replace("\r\n", "\n", (string) ($_POST['texte'] ?? '')));
    $form['une'] = !empty($_POST['une']);

    if ($form['titre'] === '') {
        $errors[] = 'Le titre est obligatoire.';
    } elseif (mb_strlen($form['titre']) > 200) {
        $errors[] = 'Le titre ne doit pas dépasser 200 caractères.';
    }
    $d = DateTime::createFromFormat('Y-m-d', $form['date']);
    if ($d === false || $d->format('Y-m-d') !== $form['date']) {
        $errors[] = 'La date est invalide.';
    }
    if ($form['texte'] === '') {
        $errors[] = 'Le texte est obligatoire.';
    }

    if ($errors === []) {
        try {
            if ($is_new) {
                $slug = actu_new_slug($form['date'], $form['titre']);
            }
            actu_save($slug, $form);

            if (!empty($_POST['remove_image'])) {
                admin_remove_image($slug);
            }
            $upload_error = admin_store_upload($slug, $_FILES['image'] ?? []);
            if ($upload_error !== null) {
                // Le texte est enregistré : on ne perd rien, on signale juste la photo.
                admin_flash('error', 'Actualité enregistrée, mais la photo n’a pas été prise en compte : ' . $upload_error);
                admin_redirect('edit.php?slug=' . rawurlencode($slug));
            }
            admin_flash('success', $is_new ? 'Actualité créée.' : 'Actualité mise à jour.');
            admin_redirect('index.php');
        } catch (Throwable $ex) {
            $errors[] = 'Enregistrement impossible : ' . $ex->getMessage();
        }
    }
}

admin_page_start($is_new ? 'Nouvelle actualité' : 'Modifier une actualité');
?>
  <div class="admin-wrap">
    <div class="admin-header">
      <h1><?= $is_new ? 'Nouvelle actualité' : 'Modifier une actualité' ?></h1>
      <a href="index.php" class="btn secondary">← Retour à la liste</a>
    </div>

    <?php admin_alert(admin_take_flash()); ?>
<?php if ($errors !== []): ?>
    <div class="admin-alert error"><?= implode('<br>', array_map('e', $errors)) ?></div>
<?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="admin-form">
      <?= admin_csrf_field() ?>

      <div class="admin-field">
        <label for="titre">Titre</label>
        <input type="text" id="titre" name="titre" required maxlength="200" value="<?= e($form['titre']) ?>">
      </div>

      <div class="admin-field">
        <label for="date">Date</label>
        <input type="date" id="date" name="date" required value="<?= e($form['date']) ?>">
        <div class="hint">Les actualités sont triées de la plus récente à la plus ancienne.</div>
      </div>

      <div class="admin-field">
        <label for="texte">Texte</label>
        <textarea id="texte" name="texte" required><?= e($form['texte']) ?></textarea>
        <div class="hint">Une ligne vide sépare les paragraphes. Les adresses web (https://…) deviennent des liens automatiquement. Le début du texte sert d’extrait sur la page d’accueil.</div>
      </div>

      <div class="admin-field">
        <label for="image">Photo</label>
        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
        <div class="hint">JPEG, PNG ou WebP, 8 Mo maximum. Format paysage conseillé (16:10) ; pensez à réduire les photos d’appareil avant envoi.</div>
<?php if (!$is_new && $actu['image_url']): ?>
        <div class="admin-current-image">
          <img src="../<?= e($actu['image_url']) ?>" alt="">
          <label style="display:inline-flex;gap:8px;align-items:center;font-weight:400;margin-top:8px;">
            <input type="checkbox" name="remove_image" value="1"> Supprimer la photo actuelle
          </label>
        </div>
<?php endif; ?>
      </div>

      <div class="admin-field admin-field-checkbox">
        <label class="checkbox-label">
          <input type="checkbox" name="une" value="1"<?= $form['une'] ? ' checked' : '' ?>>
          Mettre à la une
        </label>
        <div class="hint">L’actualité à la une est affichée en grand sur la page d’accueil, même si d’autres sont plus récentes. Sinon, c’est la plus récente qui l’est.</div>
      </div>

      <div class="admin-actions">
        <button type="submit" class="btn"><?= $is_new ? 'Publier' : 'Enregistrer' ?></button>
        <a href="index.php" class="btn secondary">Annuler</a>
      </div>
    </form>
  </div>
<?php
admin_page_end();
