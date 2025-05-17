## Détail des services

| Service           | Description                                                                                                                                                                      | Technologie utilisée      |
| ----------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------- |
| **webapp**        | Conteneur principal qui exécute l'image php-apache:8.2 pour servir l'application web.                                                                                            | PHP 8.2 + Apache          |
| **mariadb**       | Base de données relationnelle pour stocker les données de l'application. Un fichier SQL d'initialisation est chargé au démarrage.                                                | MariaDB 11.7              |
| **phpmyadmin**    | Interface web de gestion de la base de données MariaDB, accessible sur port 8080.                                                                                                | phpMyAdmin                |
| **zabbix-db**     | Base de données pour le serveur de monitoring Zabbix, pour stocker les données collectées.<br>(MariaDB 11.5 est utilisé car zabbix ne prend pas en charge une version supérieur) | MariaDB 11.5              |
| **zabbix-server** | Serveur de qui supervise, collecte et surveille les données des services Docker.                                                                                                 | Zabbix Server             |
| **zabbix-web**    | Interface web de Zabbix, pour visualiser les métriques et alertes.                                                                                                               | Zabbix Web (Apache + PHP) |
| **portainer**     | Outil de gestion visuel pour les conteneurs Docker.                                                                                                                              | Portainer CE              |
| **doom**          | Jeu Doom de 1993 qui fonctionne sous nodejs avec js-dos qui utilise DOSBox dos dans un conteneur Docker.                                                                         | Nodejs + DOSBox           |

---

## Choix de l'architecture

- **Séparation claire des services** :  
    Chaque fonction à son propre conteneur. Donc "un service = un conteneur".
    
- **PHP + Apache** :  
	Puisque l'image PHP propose une image contenant PHP et Apache nous avons choisie cette image.
    
- **MariaDB** :  
    Version open source de MySQL.
    
- **phpMyAdmin** :  
    Permet une gestion rapide et graphique de la base sans utiliser la ligne de commande SQL.
    
- **Zabbix** :  
    Supervision avancée des conteneurs et des métriques système. Malheureusement impossible de le connecter avec les autres containers 
    
- **Portainer** :  
    Interface pour gérer ses container .
    

---

### Avantages de l'orchestration avec Docker

- **Facilité de déploiement** : Un simple `docker-compose up` lance tout l'environnement prêt à l'emploi.

- **Modularité** : Possibilité de remplacer, mettre à jour, ou scale certains services indépendamment.

- **Isolation** : Chaque service tourne dans son propre environnement isolé, évitant les conflits de dépendances.

- **Surveillance** : Avec Zabbix et Portainer, la gestion, supervision et le dépannage sont facilités.

- **Persistance des données** : Avec les volumes Docker, même si les conteneurs crashent, les données restent intactes.

