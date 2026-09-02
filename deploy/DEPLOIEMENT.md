# Déploiement sous Debian — gestion-conges

## 1. Prérequis serveur

```bash
sudo apt update
sudo apt install -y apache2 php8.4-fpm php8.4-cli php8.4-common php8.4-xml \
  php8.4-mbstring php8.4-curl php8.4-mysql php8.4-intl php8.4-zip \
  mariadb-server mariadb-client certbot python3-certbot-apache git unzip

sudo a2enmod proxy_fcgi setenvif rewrite headers ssl
```

## 2. Récupération du code

```bash
sudo mkdir -p /var/www/gestion-conges
sudo chown $USER:$USER /var/www/gestion-conges
git clone git@github.com:VOTRE_ORG/gestion-conges.git /var/www/gestion-conges
cd /var/www/gestion-conges
```

## 3. Installation des dépendances (production)

```bash
composer install --no-dev --optimize-autoloader --no-security-blocking
```

## 4. Configuration

```bash
cp .env.local.example .env.local
nano .env.local   # renseigner APP_SECRET, DATABASE_URL, MAILER_DSN
```

Générer un `APP_SECRET` fort :
```bash
php -r "echo bin2hex(random_bytes(16));"
```

## 5. Base de données

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
# Uniquement si vous voulez le jeu de données de démonstration :
# php bin/console doctrine:fixtures:load --no-interaction
```

## 6. Droits sur les dossiers inscriptibles

```bash
sudo chown -R www-data:www-data /var/www/gestion-conges/var
sudo chmod -R 775 /var/www/gestion-conges/var
```

## 7. Vhost Apache

```bash
sudo cp deploy/apache/gestion-conges.conf /etc/apache2/sites-available/
sudo nano /etc/apache2/sites-available/gestion-conges.conf   # adapter le domaine
sudo a2ensite gestion-conges.conf
sudo systemctl reload apache2
```

## 8. Certificat HTTPS

```bash
sudo certbot --apache -d gestion-conges.votredomaine.fr
```
(Certbot édite automatiquement le VirtualHost 443 si besoin — vérifiez qu'il correspond bien au fichier fourni.)

## 9. Vérification finale

```bash
php bin/console debug:router          # toutes les routes sont bien déclarées
php bin/console security:check        # aucune dépendance vulnérable connue (si le plugin est installé)
sudo systemctl status php8.4-fpm apache2 mariadb
```

Ouvrez `https://gestion-conges.votredomaine.fr/login` — la connexion doit s'afficher en HTTPS avec le cadenas actif.

## Mise à jour ultérieure du code

```bash
cd /var/www/gestion-conges
git pull
composer install --no-dev --optimize-autoloader --no-security-blocking
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console cache:clear --env=prod
sudo systemctl reload php8.4-fpm
```
