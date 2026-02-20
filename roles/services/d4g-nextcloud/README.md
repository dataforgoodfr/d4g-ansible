# Nextcloud


The deployment is inspired by [nextcloud/all-in-one](https://github.com/nextcloud/all-in-one/tree/main/manual-install).


## D4G setup

Steps to match D4G setup (docker swarm cluster with Traefik reverse proxy, PostgreSQL & S3 on Scaleway):

1. Get latest docker compose and .env all-in-one "manual install" config from [GitHub](https://github.com/nextcloud/all-in-one/blob/main/manual-install/latest.yml)
2. Use host path volumes (directories created by Ansible) for containers instead of compose named volumes
3. Remove database service and set postgres `DATABASE_*` env variables to match our Scaleway config
4. Configure S3 with `OBJECTSTORE_S3_*` env variables
5. Configure mail service for internal purpose with `SMTP_*` env variables pointing to stalwart mail.
6. Network config is: traefik (container) -> nextcloud apache (container) -> nextcloud (container) -> nextcloud apps (container(s)).<br/>It means: remove docker compose port bindings because traefik is responsible for proxying to apache container, and add `d4g-internal` network. cf some nextcloud reverse proxy documentation [here](https://github.com/nextcloud/all-in-one/blob/main/reverse-proxy.md) and [here](https://docs.nextcloud.com/server/32/admin_manual/configuration_server/reverse_proxy_configuration.html)


## Documentation

* Reference documentation: https://docs.nextcloud.com/server/32/admin_manual/contents.html
* Docker images are maintained with entrypoint/start.sh scripts that accept environment variables to enable dynamic configuration, see docker image source code: https://github.com/nextcloud/all-in-one/tree/main/Containers  


## TODO

- [x] Create scaleway infra: postgres db/user & S3 bucket
- [x] Deploy and validate simple nextcloud setup: nextcloud with redis + clamav
- [ ] Setup OIDC user connection, cf [doc](https://docs.nextcloud.com/server/32/admin_manual/configuration_user/user_auth_oidc.html)
- [ ] Setup and enable additional nextcloud applications:
  - [ ] spreed (talk videoconference) -> additional ports (cf env var `TALK_PORT`) to expose
  - [ ] collabora/onlyoffice
  - [ ] fulltext search (stateful elasticsearch)


## Operations


### Option 1 : web UI

Nextcloud creates an `admin` user group that access to `Administration settings` in the [web UI](https://nextcloud.services.dataforgood.fr/settings/admin/overview), adding accounts to the group makes them admin. Admins can manipulate: system instance settings, install/enable/disable applications, application settings, etc.

### Option 2 : cli

Connect to the machine where stack is deployed (currently metal-2), run a shell in the nextcloud docker container. Run `php occ` commands to manipulate configuration and applications, see [occ doc](https://docs.nextcloud.com/server/latest/admin_manual/occ_command.html), such as `php occ app:list` (list installed application), `php occ config:system:set loglevel --value="0" --type=integer` (set logs to debug), etc.

### Troubleshoot

Nextcloud container comes with [entrypoint.sh](https://github.com/nextcloud/all-in-one/blob/main/Containers/nextcloud/entrypoint.sh) running occ commands at each container start, so configuration might be loss in case of manual config in contradiction to docker image config or environment variable.
s