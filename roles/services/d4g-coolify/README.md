# Génération de .env.production à partir de d4g-coolify.env.j2

Ce document décrit comment générer correctement le fichier .env.production à partir du template d4g-coolify.env.j2, ainsi que les règles de validation obligatoires pour éviter les erreurs lors de l’initialisation de Coolify.

Email
 - Doit être une adresse email valide
 - Le domaine doit avoir un DNS valide (@icloud.com, @gmail.com etc)
 - Maximum 255 caractères

Username
 - Minimum 3 caractères
 - Maximum 255 caractères
 - Caractères autorisés :lettres, chiffres, espaces, underscore, hyphen

Password
 - Minimum 8 caractères
 - Au moins : 1 majuscule, 1 minuscule, 1 chiffre, 1 symbole spécial


#  Installation 

Coolify needs to access the coolify-realtime service in order to execute the websocket commands.

```
    coolify:
      rule: "Host(`coolify.services.d4g.fr`)"
      service: coolify
      entryPoints:
        - webtls
      tls:
        domains:
          - main: "coolify.services.d4g.fr"
    coolify-realtime:
      rule: "Host(`coolify.services.d4g.fr`) && (PathPrefix(`/app`) || PathPrefix(`/socket.io`))"
      service: coolify-realtime
      entryPoints:
        - webtls
      tls:
        domains:
          - main: "coolify.services.d4g.fr"

  services:
    coolify:
      loadBalancer:
        servers:
          - url: "http://d4g-coolify_coolify:8080"
    coolify-realtime:
      loadBalancer:
        servers:
          - url: "http://d4g-coolify_coolify-realtime:6001"
```

The docker compose is avaiable here : 
https://github.com/coollabsio/coolify/blob/v4.x/docker-compose.yml
https://github.com/coollabsio/coolify/blob/v4.x/docker-compose.prod.yml

we need to generate the ssh key :
https://coolify.io/docs/knowledge-base/server/openssh
