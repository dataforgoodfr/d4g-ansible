<?php
/** Back-office : liste des articles « On en parle », ordre et suppression. */
require_once __DIR__ . '/lib.php';

admin_session_start();
admin_require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_csrf();
    $items = presse_load_all();
    $i = presse_find($items, (string) ($_POST['id'] ?? ''));
    if ($i === null) {
        admin_flash('error', 'Cet article n’existe pas (ou plus).');
        admin_redirect('presse.php');
    }
    try {
        switch ($_POST['action'] ?? '') {
            case 'up':
            case 'down':
                $j = $_POST['action'] === 'up' ? $i - 1 : $i + 1;
                if (isset($items[$j])) {
                    [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
                    presse_save_all($items);
                }
                break;
            case 'delete':
                $logo = $items[$i]['logo'];
                array_splice($items, $i, 1);
                presse_save_all($items);
                presse_remove_logo($logo);
                admin_flash('success', 'Article supprimé.');
                break;
        }
    } catch (Throwable $ex) {
        admin_flash('error', 'Enregistrement impossible : ' . $ex->getMessage());
    }
    admin_redirect('presse.php');
}

$items = presse_load_all();
$last = count($items) - 1;
admin_page_start('On en parle');
?>
  <div class="admin-wrap">
    <div class="admin-header">
      <h1>On en parle <span style="font-weight:400;color:var(--encre-doux);">(<?= count($items) ?>)</span></h1>
      <a href="presse-edit.php" class="btn">+ Nouvel article</a>
    </div>

    <?php admin_alert(admin_take_flash()); ?>

    <div class="admin-list">
<?php if ($items === []): ?>
      <div class="admin-empty">Aucun article pour le moment : la section « On en parle » est masquée sur le site.</div>
<?php else: ?>
<?php foreach ($items as $i => $item): ?>
      <div class="admin-row">
        <form method="post" class="admin-order">
          <?= admin_csrf_field() ?>
          <input type="hidden" name="id" value="<?= e($item['id']) ?>">
          <button type="submit" name="action" value="up" title="Monter"<?= $i === 0 ? ' disabled' : '' ?>>▲</button>
          <button type="submit" name="action" value="down" title="Descendre"<?= $i === $last ? ' disabled' : '' ?>>▼</button>
        </form>
<?php if ($item['logo'] !== ''): ?>
        <img class="admin-presse-logo" src="../<?= e($item['logo']) ?>" alt="<?= e($item['media']) ?>">
<?php else: ?>
        <span class="admin-presse-nologo"><?= e($item['media']) ?></span>
<?php endif; ?>
        <div class="admin-row-info">
          <div class="admin-row-date"><?= e($item['media']) ?><?= $item['date'] !== '' ? ' · ' . e(date_fr($item['date'])) : '' ?></div>
          <div class="admin-row-title"><?= e($item['titre']) ?></div>
        </div>
        <div class="admin-row-actions">
          <a href="<?= e($item['url']) ?>" class="btn secondary" target="_blank" rel="noopener">Voir</a>
          <a href="presse-edit.php?id=<?= e($item['id']) ?>" class="btn secondary">Modifier</a>
          <form method="post" onsubmit="return confirm('Supprimer l’article de <?= e(addslashes($item['media'])) ?> ?');">
            <?= admin_csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($item['id']) ?>">
            <button type="submit" name="action" value="delete" class="btn danger">Supprimer</button>
          </form>
        </div>
      </div>
<?php endforeach; ?>
<?php endif; ?>
    </div>
    <p class="hint" style="margin-top:12px;font-size:13px;color:var(--encre-doux);">Les articles s’affichent sur l’accueil dans cet ordre, de gauche à droite.</p>
  </div>
<?php
admin_page_end();
