## exercice
J'ai suivie les consignes de l'exercice pour Item -> ItemController, addItem -> AddItemController, j'ai aussi changer les liens vers les images qui étaient pas bon. 
et fait une classe de test pour addItem

## Racoin

Racoin est une application de vente en ligne entre particulier.

## Installation

Les commandes suivantes permettent d'installer les dépendances et de construire les fichiers statiques nécessaires au bon fonctionnement de l'application.

```bash
cp config/config.ini.dist config/config.ini
docker compose run --rm php composer install
docker compose run --rm php php sql/initdb.php
docker compose run node npm install
docker compose run node npm run build

```

## Utilisation

Pour lancer l'application, il suffit de lancer la commande suivante:

```bash
docker compose up
```
