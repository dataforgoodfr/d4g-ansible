# democis

Site de la Convention Citoyenne pour la Démocratie (conventioncitoyennepourlademocratie.fr).

Site PHP sans base de données, servi par l'image `php:8.3-apache` :

- `files/site/` : le site (déployé dans `/opt/democis/site`, monté en lecture seule)
  - `index.php`, `actualites.php` : pages publiques
  - `get-actu.php` : contenu d'une actualité (JSON) pour la pop-in
  - `rss.php` : flux RSS des actualités
  - `admin/` : back-office des actualités
  - `lib/actus.php` : stockage et helpers des actualités
- `/opt/democis/actus/data/<slug>/` sur le serveur : les actualités créées depuis le
  back-office (`actu.json` + `photo.<ext>`), seul dossier à sauvegarder.

## Back-office

Accessible sur `/admin/`, protégé par le mot de passe `DEMOCIS_ADMIN_PASSWORD`, à
renseigner dans les secrets Vaultwarden (collection `ansible-production`). Sans cette
variable, le back-office refuse toute connexion.

Le mot de passe est transmis au conteneur via `democis.env` (template
`templates/democis.env.j2`), avec `SITE_URL` (variable `democis_site_url`) qui sert
aux liens absolus du flux RSS et des balises Open Graph.
