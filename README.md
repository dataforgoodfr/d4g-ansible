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

## Local environment

Follow the following steps to start to work locally:

1. Overwrite or create a `.env` file by running: 
    ```bash
    cp .env.local .env
    ```

2. Install the following softwares: 
    - Virtualbox and its Extension pack (https://www.virtualbox.org/wiki/Downloads)
    - Vagrant (https://developer.hashicorp.com/vagrant/install)

3. **Windows + WSL2 only:** If you're using WSL2 with Windows, your network configuration may not allow your WSL instance to connect via SSH to VMs with the IP address 127.0.0.1. Here a quick fix with socat:
    ```bash
    # If socat is not installed
    sudo apt update
    sudo apt install socat

    # To be launched each time
    socat TCP-LISTEN:2222,bind=127.0.0.1,reuseaddr,fork TCP:<YOUR WINDOWS PRIVATE IP>:2221 &
    socat TCP-LISTEN:2222,bind=127.0.0.1,reuseaddr,fork TCP:<YOUR WINDOWS PRIVATE IP>:2222 &
    ```

4. Launch the VMs:
    ```bash
    cp local-env
    vagrant up
    ```

5. When running /bin/d4g-ansible:
    - Use `--skip-secrets` to avoid overwriting your local dotenv file
    - Use `-i local-env/hosts-local.yml` Ansible parameter to use your local VMs
    - Example: `bin/d4g-ansible --skip-git-checks --skip-secrets="true" playbook -i local-env/hosts-local.yml playbooks/swarm-production.yml`
