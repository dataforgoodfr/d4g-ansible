<?php
/** Back-office : connexion puis liste des actualités. */
require_once __DIR__ . '/lib.php';

admin_session_start();

$login_error = null;
if (!admin_is_logged_in() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_password_configured()) {
        $login_error = 'Le back-office n’est pas configuré (mot de passe absent).';
    } elseif (admin_check_password((string) ($_POST['password'] ?? ''))) {
        admin_login();
        admin_redirect('index.php');
    } else {
        // Ralentit les tentatives en rafale.
        usleep(750000);
        $login_error = 'Mot de passe incorrect.';
    }
}

if (!admin_is_logged_in()) {
    admin_page_start('Connexion');
    ?>
  <div class="login-wrap">
    <h1>Back office</h1>
    <?php admin_alert($login_error ? ['type' => 'error', 'message' => $login_error] : null); ?>
    <form method="post">
      <div class="admin-field">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required autofocus autocomplete="current-password">
      </div>
      <button type="submit" class="btn" style="width:100%;">Se connecter</button>
    </form>
  </div>
    <?php
    admin_page_end();
    exit;
}

$actus = actus_load_all();
admin_page_start('Actualités');
?>
  <div class="admin-wrap">
    <div class="admin-header">
      <h1>Actualités <span style="font-weight:400;color:var(--encre-doux);">(<?= count($actus) ?>)</span></h1>
      <a href="edit.php" class="btn">+ Nouvelle actualité</a>
    </div>

    <?php admin_alert(admin_take_flash()); ?>

    <div class="admin-list">
<?php if ($actus === []): ?>
      <div class="admin-empty">Aucune actualité pour le moment. Créez la première !</div>
<?php else: ?>
<?php foreach ($actus as $actu): ?>
      <div class="admin-row">
<?php if ($actu['image_url']): ?>
        <img src="<?= e($actu['image'] ? '../' . $actu['image_url'] : $actu['image_url']) ?>" alt="">
<?php else: ?>
        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="">
<?php endif; ?>
        <div class="admin-row-info">
          <div class="admin-row-date"><?= e(date_fr($actu['date'])) ?></div>
          <div class="admin-row-title">
            <?= e($actu['titre']) ?>
<?php if ($actu['une']): ?>
            <span class="badge-une">À la une</span>
<?php endif; ?>
          </div>
        </div>
        <div class="admin-row-actions">
          <a href="../actualites.php?actu=<?= e($actu['slug']) ?>" class="btn secondary" target="_blank" rel="noopener">Voir</a>
          <a href="edit.php?slug=<?= e($actu['slug']) ?>" class="btn secondary">Modifier</a>
          <form method="post" action="delete.php" onsubmit="return confirm('Supprimer définitivement « <?= e(addslashes($actu['titre'])) ?> » ?');">
            <?= admin_csrf_field() ?>
            <input type="hidden" name="slug" value="<?= e($actu['slug']) ?>">
            <button type="submit" class="btn danger">Supprimer</button>
          </form>
        </div>
      </div>
<?php endforeach; ?>
<?php endif; ?>
    </div>
  </div>
<?php
admin_page_end();
