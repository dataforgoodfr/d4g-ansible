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
    'video' => $actu['video'] ?? '',
    'video_frame' => $actu['video_frame'] ?? VIDEO_FRAME_DEFAULT,
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
    $form['video'] = trim((string) ($_POST['video'] ?? ''));
    $form['video_frame'] = (int) ($_POST['video_frame'] ?? VIDEO_FRAME_DEFAULT);
    if (!isset(VIDEO_FRAMES[$form['video_frame']])) {
        $form['video_frame'] = VIDEO_FRAME_DEFAULT;
    }

    if ($form['titre'] === '') {
        $errors[] = 'Le titre est obligatoire.';
    } elseif (mb_strlen($form['titre']) > 200) {
        $errors[] = 'Le titre ne doit pas dépasser 200 caractères.';
    }
    $d = DateTime::createFromFormat('Y-m-d', $form['date']);
    if ($d === false || $d->format('Y-m-d') !== $form['date']) {
        $errors[] = 'La date est invalide.';
    }
    if ($form['video'] !== '' && youtube_id_from_url($form['video']) === null) {
        $errors[] = 'Le lien de la vidéo n’est pas reconnu : copiez l’adresse de la vidéo depuis YouTube (bouton « Partager »).';
    }
    if ($form['texte'] === '' && $form['video'] === '') {
        $errors[] = 'Le texte est obligatoire (sauf pour une actualité vidéo).';
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
            admin_sync_video_thumb($slug, $form['video'] === '' ? null : youtube_id_from_url($form['video']), $form['video_frame']);
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
        <textarea id="texte" name="texte"><?= e($form['texte']) ?></textarea>
        <div class="hint">Une ligne vide sépare les paragraphes. Les adresses web (https://…) deviennent des liens automatiquement. Le début du texte sert d’extrait sur la page d’accueil. Facultatif pour une actualité vidéo.</div>
      </div>

      <div class="admin-field">
        <label for="video">Vidéo YouTube</label>
        <input type="url" id="video" name="video" placeholder="https://youtube.com/shorts/…" value="<?= e($form['video']) ?>">
        <div class="hint">Facultatif. Collez le lien de la vidéo copié depuis YouTube : un lien youtube.com/shorts/… est lu au format vertical, les autres au format 16:9. Un clic sur l’actualité ouvre la pop-in et lance la vidéo.</div>
<?php $video_id = $form['video'] === '' ? null : youtube_id_from_url($form['video']); ?>
        <fieldset class="admin-video-frames" id="video-frames"<?= $video_id === null ? ' hidden' : '' ?>>
          <legend>Miniature</legend>
          <div class="admin-video-frames-list<?= str_contains($form['video'], '/shorts/') ? ' is-short' : '' ?>">
<?php foreach (VIDEO_FRAMES as $n => $label): ?>
            <label>
              <input type="radio" name="video_frame" value="<?= $n ?>"<?= $form['video_frame'] === $n ? ' checked' : '' ?>>
              <img src="<?= $video_id ? e('https://i.ytimg.com/vi/' . $video_id . '/hq' . $n . '.jpg') : '' ?>" data-frame="<?= $n ?>" alt="">
              <span><?= e(ucfirst($label)) ?></span>
            </label>
<?php endforeach; ?>
          </div>
          <div class="hint">Images proposées par YouTube, prises vers le début, le milieu et la fin de la vidéo. L’image choisie sert de vignette si aucune photo n’est envoyée ; pour un autre moment, envoyez une capture d’écran de la vidéo comme photo.</div>
        </fieldset>
      </div>

      <div class="admin-field">
        <label for="image">Photo</label>
        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
        <div class="hint">JPEG, PNG ou WebP, 8 Mo maximum. Format paysage conseillé (16:10) ; pensez à réduire les photos d’appareil avant envoi.</div>
<?php if (!$is_new && $actu['photo']): ?>
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

  <script>
  // Aperçu des miniatures dès que le lien de la vidéo est collé
  // (même reconnaissance que youtube_id_from_url(), lib/actus.php).
  (function () {
    var input = document.getElementById('video');
    var box = document.getElementById('video-frames');
    var pattern = /^(?:https?:\/\/)?(?:(?:www|m)\.)?(?:youtube\.com\/(?:shorts\/|embed\/|live\/|watch\?(?:.*&)?v=)|youtu\.be\/)([A-Za-z0-9_-]{11})(?:[?&#\/].*)?$/;
    input.addEventListener('input', function () {
      var m = input.value.trim().match(pattern);
      box.hidden = !m;
      if (!m) return;
      box.querySelector('.admin-video-frames-list').classList.toggle('is-short', input.value.indexOf('/shorts/') !== -1);
      box.querySelectorAll('img[data-frame]').forEach(function (img) {
        img.src = 'https://i.ytimg.com/vi/' + m[1] + '/hq' + img.getAttribute('data-frame') + '.jpg';
      });
    });
  })();
  </script>
<?php
admin_page_end();
