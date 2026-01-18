# Shared PostgreSQL Service Role

This role deploys a PostgreSQL 16 container on Docker Swarm with idempotent database and user creation.

## Features

- PostgreSQL 16 (Alpine-based image)
- Idempotent database and user creation
- Automatic S3 backups of PostgreSQL data directory
- Pinned to a specific node in the swarm cluster
- Health checks configured
- PostgreSQL exporter for Prometheus metrics
- Dedicated observability user with monitoring permissions

## Variables

### Required Variables

- `databases`: List of databases to create. Each entry must have:
  - `db`: Database name
  - `user`: Database user
  - `password`: User password

- `postgres_root_password`: Root password for PostgreSQL (postgres user)

### Optional Variables

- `deploy_on`: Hostname of the node to deploy PostgreSQL on (default: first node in the cluster)
- `observability_user`: Username for PostgreSQL exporter (default: `postgres_exporter`)
- `observability_password`: Password for observability user (default: `changeme_exporter`)

## Example Usage

Add to your playbook:

```yaml
- import_role:
    name: roles/services/shared-postgresql
  vars:
    postgres_root_password: "{{ lookup('env', 'POSTGRES_ROOT_PASSWORD') }}"
    observability_user: "postgres_exporter"
    observability_password: "{{ lookup('env', 'POSTGRES_EXPORTER_PASSWORD') }}"
    deploy_on: production-01.tinker.ovh
    databases:
      - db: vaultwarden
        user: vaultwarden
        password: "{{ lookup('env', 'VAULTWARDEN_DB_PASSWORD') }}"
      - db: nextcloud
        user: nextcloud
        password: "{{ lookup('env', 'NEXTCLOUD_DB_PASSWORD') }}"
      - db: grafana
        user: grafana
        password: "{{ lookup('env', 'GRAFANA_DB_PASSWORD') }}"
  tags:
    - postgresql
    - database
    - service
```

## Tags

- `pull`: Pull the PostgreSQL Docker image
- `up`: Deploy the stack
- `db-init`: Initialize databases and users
- `backup`: Configure S3 backups

## How It Works

1. Creates `/opt/shared-postgresql` directory on all nodes
2. Generates the `init-databases.sql` script from the `databases` variable
3. Deploys the PostgreSQL container and postgres-exporter pinned to the `deploy_on` node
4. Waits for PostgreSQL to be ready
5. Executes the SQL script to create:
   - Observability user with `pg_monitor` role and read access to all databases
   - Application databases and users
6. Sets up S3 backups for the data directory

## Idempotent Database Creation

The role executes the database creation script **every time** the playbook runs, not just on first initialization. The SQL script uses:

- `DO $$ ... IF NOT EXISTS` blocks to create users only if they don't exist
- Conditional `CREATE DATABASE` commands
- This means you can add new databases to the `databases` list and re-run the playbook to create them without affecting existing databases

## Accessing the Database

Services can connect to PostgreSQL using:

- **Host**: `shared-postgresql_postgres` (Docker service name)
- **Port**: `5432` (default PostgreSQL port)
- **Database**: As configured in your `databases` list
- **User/Password**: As configured in your `databases` list

Example connection string:
```
postgresql://vaultwarden:password@shared-postgresql_postgres:5432/vaultwarden
```

## Observability

The PostgreSQL exporter is automatically deployed and configured with:

- **Service name**: `shared-postgresql_exporter`
- **Metrics endpoint**: `http://shared-postgresql_exporter:9187/metrics`
- **Prometheus labels**: Configured for automatic discovery
  - `prometheus.io/scrape=true`
  - `prometheus.io/port=9187`
  - `prometheus.io/path=/metrics`

### Observability User Permissions

The `observability_user` is created with the following permissions:

- Member of `pg_monitor` role (provides read-only access to monitoring views)
- `CONNECT` privilege on all databases
- `SELECT` privilege on all tables in all databases
- Default privileges to automatically get `SELECT` on future tables

This allows the postgres-exporter to collect comprehensive metrics across all databases without having write access.
