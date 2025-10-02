##Cooksy
Application web collaborative de partage de recettes réalisée avec Symfony 7.

📝 Description
Cooksy est une plateforme où les utilisateurs peuvent :

Publier leurs propres recettes

Consulter celles des autres

Commenter et noter des recettes

Gérer leur profil

L’objectif est de créer une application simple, intuitive et entièrement responsive.

⚙️ Fonctionnalités
Authentification (inscription, connexion, rôles : user / admin)

Gestion des recettes : création, modification, suppression

Commentaires et notes (1 à 5 étoiles)

Classement des meilleures recettes

Recherche et filtrage par catégorie, ingrédient, temps

Interface responsive conçue avec Tailwind CSS

🚀 Installation
Prérequis
Pour installer et exécuter l'application localement, vous devez disposer des outils suivants :

PHP 8.2+

Composer

MySQL ou PostgreSQL

Symfony CLI (optionnel mais recommandé pour le serveur local)

Étapes d'Installation
Clonage du Dépôt
Téléchargez le code source de l'application :

git clone [https://github.com/blo1258/cooksy.git](https://github.com/blo1258/cooksy.git) cooksy
cd cooksy

Installation des Dépendances
Installez toutes les bibliothèques et dépendances PHP nécessaires via Composer :

composer install

Configuration de l'Environnement
Copiez le fichier d'environnement par défaut et mettez à jour votre configuration de base de données.

cp .env .env.local

Modifiez le fichier .env.local en remplissant notamment la variable DATABASE_URL avec les identifiants de votre base de données locale (ex: mysql://app_user:app_password@127.0.0.1:3306/cooksy?serverVersion=13&charset=utf8mb4).

Base de Données et Migrations
Créez la base de données et exécutez les migrations pour mettre en place la structure des tables :

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

Chargement des Données de Démo (Optionnel)
Pour peupler l'application avec des données initiales (utilisateurs, recettes, etc.), vous pouvez exécuter les fixtures :

php bin/console doctrine:fixtures:load

Lancement de l'Application
Utilisez l'outil Symfony CLI pour démarrer un serveur local sécurisé :

symfony serve

L'application Cooksy sera désormais accessible dans votre navigateur à l'adresse suivante : https://127.0.0.1:8000

👨‍💻 Technologie
Framework: Symfony 7

Frontend: HTML5, Tailwind CSS

Base de Données: Doctrine ORM, MySQL/PostgreSQL

Gestion des Dépendances: Composer