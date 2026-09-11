<?php
/** Flux RSS 2.0 des actualités. */
require_once __DIR__ . '/lib/actus.php';

$actus = actus_load_all();
$base = site_url();

header('Content-Type: application/rss+xml; charset=utf-8');
header('Cache-Control: public, max-age=300');

/** Échappement XML (les entités HTML nommées n'existent pas en XML). */
function x(string $s): string
{
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
  <title>Actualités — <?= x(SITE_NAME) ?></title>
  <link><?= x($base) ?>/</link>
  <atom:link href="<?= x($base) ?>/rss.php" rel="self" type="application/rss+xml" />
  <description>Les actualités de la Convention Citoyenne pour la Démocratie : sessions, panel, préconisations.</description>
  <language>fr-fr</language>
  <lastBuildDate><?= x(date_rss($actus[0]['date'] ?? date('Y-m-d'))) ?></lastBuildDate>
<?php foreach ($actus as $actu):
    $link = $base . '/actualites.php?actu=' . rawurlencode($actu['slug']);
    $image = $actu['image_url'] ? __DIR__ . '/' . $actu['image_url'] : null;
?>
  <item>
    <title><?= x($actu['titre']) ?></title>
    <link><?= x($link) ?></link>
    <guid isPermaLink="true"><?= x($link) ?></guid>
    <pubDate><?= x(date_rss($actu['date'])) ?></pubDate>
    <description><?= x(actu_excerpt($actu['texte'])) ?></description>
    <content:encoded><![CDATA[<?= str_replace(']]>', ']]]]><![CDATA[>', texte_to_html($actu['texte'])) ?>]]></content:encoded>
<?php if ($image !== null && is_file($image)): ?>
    <enclosure url="<?= x($base . '/' . $actu['image_url']) ?>" length="<?= filesize($image) ?>" type="<?= x((string) mime_content_type($image)) ?>" />
<?php endif; ?>
  </item>
<?php endforeach; ?>
</channel>
</rss>
