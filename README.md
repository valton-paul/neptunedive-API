# NeptuneDive

## Comment lancer l'application

### Vérification des ports
Pour commencer, vous devez vérifier que les ports **8000** et **3306** soient bien ouverts.  
Vous pouvez vérifier cela en utilisant la commande suivante dans votre terminal :

```bash
sudo netstat -tuln | grep -E '8000|3306'
```

S'ils n'apparaissent pas, vous pouvez continuer.

### Lancement de l'application
Pour démarrer l'application, il est nécessaire de cloner le projet. 
Pour ce faire, ouvrez votre terminal, accédez au répertoire où vous souhaitez le sauvegarder, puis utilisez l'une des commandes suivantes selon votre préférence :

Via SSH
```bash
git clone git@gitlabinfo.iutmontp.univ-montp2.fr:dream_team/neptunedive.git
```

Via HTTPS
```bash
git clone https://gitlabinfo.iutmontp.univ-montp2.fr/dream_team/neptunedive.git
```

Une fois cela fait, démarrez Docker, soit via Docker Desktop, soit en exécutant la commande suivante :

```bash
sudo systemctl start docker
```

Une fois le projet cloné et Docker lancé, reprenez votre terminal (ou mettez-vous à la racine du projet) puis exécutez cette commande :

```bash
docker compose up --build -d
```

Attendez que les conteneurs soient lancés (Started).
Et voilà, vous pouvez accéder à l'application via [localhost:8000](localhost:8000) (N'utilisez pas https://).