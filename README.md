# d4g-proxy

This repository holds all the Ansible configuration files for d4g infrastructure.

This repository is organised as such :
 - `playbooks/` holds the playbooks to be applied to hosts. Each playbook represents a combination of roles representing a service (e.g. the d4g-proxy playbook requires the docker and acme.sh roles)
 - `roles/services` holds service ansible roles. They are used to setup the specific parts of a service (e.g. docker-compose configuration, etc.)
 - `roles/utilities` holds utility standalone ansible roles, which can be composed and used in playbooks (e.g. the docker role installs docker)

You can apply a playbook using the following command :
```
ansible-playbook playbooks/d4g-proxy.yml --diff --verbose
```

You can also apply only specific tagged steps by using the `--tags` flag :
```
ansible-playbook playbooks/d4g-proxy.yml --diff --verbose --tags=acme.sh
```

Here are the existing services at the moment :
  - d4g-proxy : Used to create the proxy that is in front of all applications to provide out-of-the-box TLS on all domains *.services.dataforgood.fr


Here are the existing utilities at the moment :
  - base-pkg : Install some basic packages to the host (curl, vim, etc.)
  - docker : Installs docker to the host
  - acme.sh : Provision a TLS certificate for the given domain and sets up the automatic renewal of this certificate. /!\ It's currently custom tweaked for *.services.dataforgood.fr
