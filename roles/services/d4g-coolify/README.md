# Génération de .env.production à partir de d4g-coolify.env.j2

Ce document décrit comment générer correctement le fichier .env.production à partir du template d4g-coolify.env.j2, ainsi que les règles de validation obligatoires pour éviter les erreurs lors de l’initialisation de Coolify.

# Base de données (PostgreSQL)
Les variables suivantes doivent être strictement identiques :
DB_USERNAME     = POSTGRES_USER
DB_PASSWORD     = POSTGRES_PASSWORD

# Root User – Validation Requirements (OBLIGATOIRE)

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
