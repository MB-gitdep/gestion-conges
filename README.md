# Gestion des congés

Application de gestion des congés et des utilisateurs, avec hiérarchie de
responsables par service, développée en PHP / Symfony 7.4 LTS.

## Fonctionnalités

- Demande de congé par les employés, avec vérification automatique du
  solde disponible et détection des chevauchements de périodes
- Validation/refus par le(s) responsable(s) du service concerné, avec
  traçabilité complète (workflow Symfony + historique)
- Notifications automatiques par email (nouvelle demande, décision)
- Back-office intégral : utilisateurs et droits, services, types de
  congés, calendrier des jours fériés, soldes, suivi/audit
- Multi-entreprises (multi-tenant), gestion des rôles hiérarchisée
- Mots de passe créés par les utilisateurs eux-mêmes, avec contrôle de
  complexité strict (voir plus bas)

## Stack technique

- PHP 8.4, **Symfony 7.4 LTS**
- MariaDB 11.4 (via Doctrine ORM)
- Debian + Apache + PHP-FPM


## Démarrage en local

```bash
composer install --no-security-blocking
cp .env.local.example .env.local   # puis renseigner DATABASE_URL, APP_SECRET, MAILER_DSN
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
APP_ENV=dev php bin/console doctrine:fixtures:load   # jeu de données de démonstration
symfony server:start
```

Comptes de démonstration (mot de passe `password`, aucun changement exigé) :
- `admin@acme.fr` — RH / Administration
- `responsable@acme.fr` — Responsable du service Technique
- `julie@acme.fr`, `karim@acme.fr` — Employés

## Gestion des mots de passe

- **Création d'un compte (admin)** : un mot de passe temporaire aléatoire
  est généré et affiché une seule fois à l'admin (à transmettre à
  l'utilisateur par un canal sécurisé — jamais par email en clair).
- **Première connexion** : l'utilisateur est automatiquement redirigé
  vers `/profil/mot-de-passe` et ne peut accéder à aucune autre page tant
  qu'il n'a pas défini son propre mot de passe (`ForcerChangementMotDePasseSubscriber`).
- **Changement libre** : chaque utilisateur peut changer son mot de passe
  à tout moment depuis le lien dans le menu (nécessite de ressaisir le
  mot de passe actuel).
- **Contrôle de complexité** (`ChangerMotDePasseType`) :
  - 12 caractères minimum
  - au moins une majuscule, une minuscule, un chiffre, un caractère spécial
  - score de robustesse minimum (`Assert\PasswordStrength`)
  - vérification anti-fuite de données (`Assert\NotCompromisedPassword`,
    interroge l'API "Have I Been Pwned" via `symfony/http-client` — **nécessite
    un accès sortant à internet** ; si le serveur est isolé, retirez cette
    contrainte du formulaire ou autorisez le flux sortant correspondant)

## Tests

```bash
php bin/phpunit
```

Couvre notamment :
- les règles métier de validation d'une demande (période invalide,
  chevauchement, solde insuffisant) — `CongeValidationServiceTest`
- les droits d'accès du `CongeVoter` (demandeur, responsable, RH,
  non connecté) — `CongeVoterTest`
- le contrôle de complexité des mots de passe — `ChangerMotDePasseTypeTest`
- le comportement HTTP du contrôleur de demande de congé — `CongeControllerTest`

## Déploiement

Voir [`deploy/DEPLOIEMENT.md`](deploy/DEPLOIEMENT.md) pour la procédure
générale sous Debian (Apache, PHP-FPM, MariaDB, HTTPS), et
[`deploy/apache/gestion-conges.conf`](deploy/apache/gestion-conges.conf)
pour la configuration réelle utilisée en production.

### Après toute installation de nouveau bundle Composer

Un décalage de cache entre environnements est la cause la plus fréquente
d'erreurs "commande non trouvée" ou "service non trouvé" rencontrées lors
du déploiement initial. Réflexe systématique après `composer require` :

```bash
rm -rf var/cache/dev var/cache/prod
php bin/console cache:clear --env=prod
```

Si un bundle reste introuvable malgré une entrée correcte dans
`config/bundles.php`, forcer sa recette :

```bash
composer recipes:install <nom-du-paquet> --force -v
```

## Sécurité

- CSRF activé sur tous les formulaires
- Limitation des tentatives de connexion (5/minute, anti-bruteforce natif Symfony)
- En-têtes de sécurité HTTP (CSP, HSTS, anti-clickjacking) sur chaque réponse
- Cookies de session `Secure` + `HttpOnly` + `SameSite=Lax`
- Droits vérifiés par Voters à chaque action sensible (`access_decision_manager: unanimous`)
- Mots de passe hashés (bcrypt/argon2, coût renforcé), jamais choisis par un tiers
  au-delà de la première connexion, contrôle de complexité strict à chaque changement
- Toute exception métier est interceptée et journalisée ; aucun détail
  technique n'est jamais renvoyé à l'utilisateur (pages d'erreur 403/404/500 dédiées)
- **Dépendances maintenues** : projet verrouillé sur Symfony 7.4 LTS ;
  vérifier régulièrement `composer audit` (aucune faille au dernier contrôle)

## Licence

Distribué sous licence MIT — voir le fichier [`LICENSE`](LICENSE).
# gestion-conges
