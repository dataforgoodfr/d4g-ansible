# d4g-proxy

This repository holds all the Ansible configuration files for d4g infrastructure.

This repository is organised as such :
 - `playbooks/` holds the playbooks to be applied to hosts. Each playbook represents a combination of roles representing a service (e.g. the d4g-proxy playbook requires the docker and acme.sh roles)
 - `roles/services` holds service ansible roles. They are used to setup the specific parts of a service (e.g. docker-compose configuration, etc.)
 - `roles/utilities` holds utility standalone ansible roles, which can be composed and used in playbooks (e.g. the docker role installs docker)

## Setting up the repository

As all our d4g repositories, d4g-ansible comes with a `bin/setup` utility to install most of the required dependencies on your machine.
The only hard dependency is nodejs, which we leave up to the user to install however they want and configure. Nodejs is required by bitwarden's CLI.

To install dependencies and setup the repo, run
```bash
bin/setup
```

You are now ready to run the playbooks.

## Running a playbook

**DO NOT RUN PLAYBOOKS DIRECTLY USING `ansible-playbook` AS DOING THIS WILL REMOVE ALL SHARED SECRETS AND BREAK YOUR DEPLOYMENT.**
**PLEASE USE `bin/d4g-ansible` AS DETAILED BELOW**

You can run a playbook using the following command :
```
bin/d4g-ansible playbook playbooks/swarm-production.yml --diff --verbose
```

You can apply only specific tagged steps by using the `--tags` flag :
```bash
bin/d4g-ansible playbook playbooks/swarm-production.yml --diff --verbose --tags=acme
```
You can also limit the apply to a specific host in the inventory using the `--limit` flag :
```bash
bin/d4g-ansible playbook playbooks/swarm-production.yml --diff --verbose --limit=metal-1.dataforgood.fr
```
