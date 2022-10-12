Petit exercice pendant mes vacances :

Tu vas devoir créer un formulaire CRUD (Create Read Update Delete) de zéro en ne faisant que du procédural.

Voici le travail a effectuer, le style est secondaire, ne se concentrer uniquement dans un premier temps que sur le côté fonctionnel :

Accès au phpmyadmin : http://jules.local:81 

-✅ Création d'une table article, cette table sera composée de plusieurs champs (tu peux créer soit depuis le phpmyadmin ou soit en mode script php avec vérification que la table n'existe pas) : 
  - id
  - titre
  - sous titre
  - contenu
  - date de création
  - date de modification
-✅ Mise en place de la connexion PHP / Base de données (PDO)
-✅ Création du listing avec une requête PDO
-✅ Je veux dans le listing uniquement les champs suivants dans un tableau que tu
    afficheras : id, titre, sous titre, date de création.
-✅ Création du formulaire création / édition avec requête de récupération + 
    requête de sauvegarde et de modification
-✅ Création de la suppression avec requête de suppression.
-✅ Ajout dans le tableau d'un tri par ordre croissant / décroissant sur chaque 
    champs


Le fonctionnement du DOCKER :

- ✅Faire make install pour faire toute l'installation
- ✅Ajouter le nom de domaine à ta machine (il est dans le .env dans la variable DOCKER_DNS) : sudo nano /etc/hosts
- Le travail se fera uniquement dans src/index.php en full procédural.