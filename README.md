Yana Server
===========

Interface PHP de domotique Y.A.N.A (You Are Not Alone)

Pré-requis
============

- Raspberry PI
- Apache 2 ou Lighttpd
- PHP 5
- SQLite 3
- [Librairie Wiring PI](https://projects.drogon.net/raspberry-pi/wiringpi/download-and-install/)

Installation manuelle
============

Executez les commandes suivantes dans un shell :

> sudo apt-get install git-core && sudo apt-get install sqlite3 && sudo apt-get install php5-sqlite && cd /var/www/ && sudo git clone https://github.com/ldleman/yana-server.git /var/www/yana-server && sudo chown -R www-data:www-data yana-server && sudo chown root:www-data /var/www/yana-server/plugins/relay/radioEmission && sudo chmod +s /var/www/yana-server/plugins/relay/radioEmission

Puis executez l'adresse web de yana dans un navigateur :

`http://adresse.de.votre.rpi/yana-server`

Et suivez le formulaire d'installation.

nb : A la fin de l'installation, vous pouvez activer ou désactiver les plugins qui vous sont utiles dans la section
configuration --> plugins, pensez à le faire AVANT de jouer avec Yana windows ou Yana Android sans quoi aucune commande ne sera disponible

Installation automatique
========================
Si vous n'êtes pas à l'aise avec le monde linux et que vous n'avez pas trop bidouillé votre système jusqu'ici, vous pouvez utiliser la commande
d'installation automatique gentiment proposé par maditnerd, tapez simplement :

> sudo wget https://raw.githubusercontent.com/ldleman/yana-server/master/install.sh && sudo chmod +x install.sh && sudo ./install.sh

Puis executez l'adresse web de yana dans un navigateur :

`http://adresse.de.votre.rpi/yana-server`

Et suivez le formulaire d'installation.
nb : A la fin de l'installation, vous pouvez activer ou désactiver les plugins qui vous sont utiles dans la section
configuration --> plugins, pensez à le faire AVANT de jouer avec Yana windows ou Yana Android sans quoi aucune commande ne sera disponible

Sécurité
========
Pour des raisons de sécurité, il est très fortement déconseillé d'ouvrir l'accès au serveur web de yana sur l'exterieur.
Si vous le faites cependant, il est necessaire d'utiliser apache comme serveur http OU de configurer votre serveur http
pour interdire l'accès au dossier /db

Mise à jour
============

Pour mettre a jour yana-server, il faut utiliser git, placez vous dans le répertoire de yana
```cd /var/www/yana-server```

Et faites un git pull pour récuperer la dernière version
```git pull```

Attention, si vous aviez fait des modifs sur le code entre temps il est possible que le git pull ne fonctionne pas, dans ce cas faites un git checkout pour reprendre la copie exacte du dépot officiel en ecransant vos modifications
```git reset --hard origin/master```

Puis remettez les permission en ecriture sur le dossier plugins
> sudo chown -R www-data:www-data /var/www/yana-server && sudo chown root:www-data /var/www/yana-server/plugins/relay/radioEmission && sudo chmod +s /var/www/yana-server/plugins/relay/radioEmission

Une fois l'update terminé, allez en section plugin de yana-server et désactivez/réactivez chaques plugins utilisés afin de mettre à jour leurs tables.
