#!/usr/bin/env bash
# Restores every SQL dump made by the sql-backup role into a throwaway
# postgres:15 container, then compares the table count with the dump.
# Nothing touches the RDB. Run on a swarm node, as root:
#   ssh root@metal-1.dataforgood.fr bash -s < roles/utilities/sql-backup/files/verify-restore.sh
set -euo pipefail

CONTAINER=sql-restore-check
docker rm -f "$CONTAINER" >/dev/null 2>&1 || true
docker run -d --rm --name "$CONTAINER" -e POSTGRES_PASSWORD=check postgres:15 >/dev/null
trap 'docker rm -f "$CONTAINER" >/dev/null 2>&1 || true' EXIT
until docker exec "$CONTAINER" pg_isready -U postgres -q; do sleep 1; done

psql() { docker exec -i "$CONTAINER" psql -U postgres -X -q -v ON_ERROR_STOP=1 "$@"; }

failed=0
# DUMPS overrides the glob, to check a single dump.
for dump in ${DUMPS:-/opt/*/sql-backup/backup-*.sql}; do
  [ -e "$dump" ] || { echo "No dump found"; exit 1; }
  db="check_$(basename "$(dirname "$(dirname "$dump")")" | tr -c 'a-z0-9\n' '_')"

  # The dump references its owner and grantees: create them so the restore can run strictly.
  for role in $(grep -ohE '(OWNER TO|TO) [a-z_][a-z0-9_]*;' "$dump" | awk '{print $NF}' | tr -d ';' | sort -u); do
    psql -c "DO \$\$ BEGIN CREATE ROLE \"$role\"; EXCEPTION WHEN duplicate_object THEN NULL; END \$\$;"
  done
  psql -c "CREATE DATABASE \"$db\";"

  expected=$(grep -cE '^CREATE TABLE ' "$dump" || true)
  if psql --single-transaction -d "$db" < "$dump" > /dev/null; then
    restored=$(psql -At -d "$db" -c "SELECT count(*) FROM pg_tables WHERE schemaname NOT IN ('pg_catalog', 'information_schema');")
    rows=$(psql -At -d "$db" -c "ANALYZE;" -c "SELECT coalesce(sum(n_live_tup), 0) FROM pg_stat_user_tables;")
    if [ "$restored" = "$expected" ]; then
      echo "OK   $dump ($(du -h "$dump" | cut -f1), $restored tables, ~$rows rows)"
    else
      echo "FAIL $dump: $expected tables in dump, $restored restored"; failed=1
    fi
  else
    echo "FAIL $dump: restore error (see above)"; failed=1
  fi
  psql -c "DROP DATABASE \"$db\";"
done
exit $failed
