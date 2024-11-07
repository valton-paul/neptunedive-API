# Neptune Dive
## Prérequis

Avant de commencer, assurez-vous d'avoir installé les outils suivants :

- [Docker Engine](https://docs.docker.com/engine/install/)
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (facultatif)

Pour vérifier si vous avez Docker, utilisez la commande suivante :

```bash
docker --version
```

## Installation et Lancement

Suivez les étapes ci-dessous pour cloner, installer et démarrer le projet.

### 1. Cloner les dépôts

Pour cloner les dépôts dans le même répertoire parent, exécutez les commandes suivantes (Linux) :

```bash
git clone -b 13-client-docker-compose https://gitlabinfo.iutmontp.univ-montp2.fr/dream_team/neptunedive.git &&
git clone -b 13-client-docker-compose https://gitlabinfo.iutmontp.univ-montp2.fr/dream_team/neptunedivefront.git
```

Sinon, si vous êtes sous Windows, copiez les liens pour récupérer les fichiers Zip et faites un dézippe de ces derniers (dans le même dossier parent).

### 2. Lancer les dockers

Ouvrez un terminal dans le dossier parent des deux projets (neptunedive et neptunedivefront).

Une fois dans le répertoire parent, exécutez les commandes suivantes (si vous avez pas git clone les noms des dossiers seront neptunedive-13-client-docker-compose ou neptunedivefront-13-client-docker-compose):

```bash
cd neptunedive &&
docker network create shared_network &&
docker compose up --build -d
```

Une fois que le conteneur du backend est lancé, passons au docker du frontend :

```bash
cd ../neptunedivefront &&
docker compose up --build -d
```

### 3. Accéder à l'application

Vous pouvez utiliser l'application via l'URL suivante [http://tenant2.localhost:5173](http://tenant2.localhost:5173)

__Voici deux comptes pré-inscrits :__

Un simple plongeur : 
- email : __john.doe@gmail.com__
- mot de passe : __Azerty123!__

Un plongeur qui est moniteur :
- email : __jean.dupont@gmail.com__
- mot de passe : __Azerty123!__