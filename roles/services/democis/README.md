# democis

Site de la Convention Citoyenne pour la Démocratie (conventioncitoyennepourlademocratie.fr).

Le site vit dans son propre dépôt, [dataforgoodfr/democis-site](https://github.com/dataforgoodfr/democis-site) :
c'est là qu'on modifie les pages, les images et la configuration Apache/PHP. Chaque push
sur `main` y publie l'image `ghcr.io/dataforgoodfr/democis-site:main-<sha>`.

Ce rôle ne fait que la déployer :

- le tag de l'image est épinglé dans `templates/docker-compose.yml.j2` ; pour livrer une
  nouvelle version du site, monter ce tag puis lancer
  `bin/d4g-ansible playbook playbooks/swarm-production.yml --tags democis` ;
- `/opt/democis/actus/` sur le serveur, monté dans le conteneur, garde les données du
  back-office ; il est sauvegardé chaque nuit sur S3 (rôle `s3-backup`, service `democis`) :
  - `data/<slug>/` : les actualités créées depuis le back-office (`actu.json`,
    `photo.<ext>`, miniature de vidéo `video-<id>-<n>.jpg`)
  - `presse/` : `presse.json` (articles « On en parle », dans l'ordre d'affichage)
    et les logos envoyés

## Back-office

Accessible sur `/admin/`, protégé par le mot de passe `DEMOCIS_ADMIN_PASSWORD`, à
renseigner dans les secrets Vaultwarden (collection `ansible-production`). Sans cette
variable, le back-office refuse toute connexion.

Le mot de passe est transmis au conteneur via `democis.env` (template
`templates/democis.env.j2`), avec `SITE_URL` (variable `democis_site_url`) qui sert
aux liens absolus du flux RSS et des balises Open Graph.
