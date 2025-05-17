## Architecture des Services

L'architecture repose sur plusieurs conteneurs indépendants reliés via deux réseaux Docker :

- `app-network` : pour les services applicatifs.
- `monitoring-network` : pour les outils de supervision.

### Liste des Services :

| Service           | Rôle                                                        | Technologies                |
| ----------------- | ----------------------------------------------------------- | --------------------------- |
| **webapp**        | Serveur web PHP natif sous Apache, application BusinessCare | PHP 8.2, Apache             |
| **mariadb**       | Base de données relationnelle pour BusinessCare             | MariaDB 11.7                |
| **phpmyadmin**    | Interface web pour administrer la base MariaDB              | phpMyAdmin                  |
| **zabbix-db**     | Base de données pour Zabbix (monitoring)                    | MariaDB 11.5                |
| **zabbix-server** | Serveur de monitoring, collecte des métriques               | Zabbix Server               |
| **zabbix-web**    | Interface web de visualisation des données de Zabbix        | Zabbix Web                  |
| **portainer**     | Gestionnaire graphique des conteneurs Docker                | Portainer Community Edition |
| **doom**          | Conteneur ludique pour afficher Doom via Docker             | Doom In Docker              |

## Structure des fichiers

```
Rendu/
├── Architecture.réseau.png
├── Architecture.stockage.png
├── Documentation.pdf
├── Détaille du projet.pdf
└── build
    ├── Dockerfile.dev # Build de l'image de développement
    ├── Dockerfile.dev # Build de l'image de production (contenant l'app web)
    ├── docker-compose.dev.yml # docker compose pour le developement en màj en temps réelle 
    ├── docker-compose.prod.yml # docker compose pour la mise en production
    └── src
        ├── [...] # fichiers du site
        └── ressources
            └── businesscare.sql # fichier init de la bdd
```

## Configuration Docker

### Variables d'Environnement (`.env`)

```env
DB_NAME=businesscare
MYSQL_ROOT_PASSWORD=businesscare
DB_USER=businesscare
MYSQL_PASSWORD=businesscare
ZABBIX_DB_PASSWORD=zabbix
ZABBIX_ROOT_PASSWORD=zabbix
```

### Dockerfile (webapp)

Utilisé pour construire l’image `webapp` :

```Dockerfile
FROM php:8.2-apache

# Télécharger le script d'installation des extensions PHP
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Installer les extensions demandées
RUN install-php-extensions \
    pdo_mysql \
    mysqli \
    mbstring \
    json \
    openssl \
    curl \
    zip \
    gd \
    intl \
    bcmath

# Activer les modules Apache nécessaires
RUN a2enmod proxy proxy_http rewrite

# Ajout des fichiers du projet
COPY ./src/ /var/www/html/
```

### Réseaux Docker

- **app-network** : communication entre WebApp, MariaDB, phpMyAdmin.
- **monitoring-network** : communication entre Zabbix components et Portainer.
    

### Volumes utilisés

| Volume                 | Rôle                                                |
| ---------------------- | --------------------------------------------------- |
| `mariadb-data`         | Persistant pour les données de la base MariaDB      |
| `portainer-data`       | Persistant pour les configurations de Portainer     |
| `zabbix-db-data`       | Persistant pour les données de la base Zabbix       |
| `/var/run/docker.sock` | Socket Docker pour Portainer (accès aux conteneurs) |

## Lancement de l'Application

### En Développement :

```bash
docker compose -f docker-compose.dev.yml up --build
```

- Accès à BusinessCare : [http://localhost](http://localhost)

- Accès phpMyAdmin : [http://localhost:8080](http://localhost:8080)

- Accès Portainer : [http://localhost:9000](http://localhost:9000)

- Accès Zabbix : [http://localhost:9090](http://localhost:9090)


### En Production :

```bash
docker compose -f docker-compose.prod.yml up -d
```
