												README LIFPROJET


1 - Une fois le clone récuperé, allez sur le site : https://getcomposer.org/ pour télécharger COMPOSER et installez le.

2 - Avant de commencer, vérifiez que tout est bien installé et tapez dans votre invite de commande les commandes suivantes:
" php -v " et " composer -V ".

3 - S'il n'y a pas d'erreur dans les vérifications précedentes, allez maintenant dans le dossier "Lifprojet".
Lancez la console dans ce dossier.
Entrez la commande : composer install.

4 - Une fois les fichiers installés, editez le fichier .env et remplacez la ligne 16 par : 
DATABASE_URL=mysql://root:@127.0.0.1:3306/blog
(Assurez-vous que vous avez créé votre base de données "blog" sur phpmyadmin et que wamp est bien lancé).

5 - Assurez-vous que votre dossier "src/migration" ne contient qu'un seul fichier texte sans nom.
Il ne faut pas qu'il y ait de fichier nommé "version...." s'il y en a, supprimez les et laissez seulement le fichier txt sans nom.

6 - Une fois cela fait, lancez une console dans le dossier où se trouvent les dossier src,var .. etc Et tapez :
"php bin/console make:migration".

7 - Une fois cela fait, exécutez cette commande :
"php bin/console doctrine:migrations:migrate".


8 - Une fois votre base de données bien crée vous pourrez exécuter toujours dans la console : php bin/console doctrine:fixtures:load
(cette commande permet de générer des articles/user/commentaires ... aléatoirement afin de remplir le site).

9 - Une fois tout cela fait vous pouvez lancer le serveur : php bin/console server:run.

10 - Maintenant vous pouvez aller sur l'adresse indiqueée.

11 - Si vous avez une erreur du type "unknow..such stream.." verifiez que le fichier routé présent dans ce chemin : lifprojet\vendor\symfony\web-server-bundle\Resources
n'ai pas été supprimé par votre anti-virus, si c'est le cas désactivez l'antivirus ou mettez le dossier du projet dans votre zone verte.

