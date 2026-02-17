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

- [ ] Create scaleway infra: postgres db/user & S3 bucket
- [ ] Deploy and validate simple nextcloud setup: nextcloud with redis + clamav
- [ ] Setup OIDC user connection, cf [doc](https://docs.nextcloud.com/server/32/admin_manual/configuration_user/user_auth_oidc.html)
- [ ] Setup and enable additional nextcloud applications:
  - [ ] spreed (talk videoconference) -> additional ports (cf env var `TALK_PORT`) to expose
  - [ ] collabora/onlyoffice
  - [ ] fulltext search (stateful elasticsearch)
