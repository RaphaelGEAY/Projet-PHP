# Projet-PHP

```
sudo apt-get install lamp-server^
```

```
sudo service apache2 start
sudo service mysql start
```

```
sudo chown -R $USER:www-data /var/www/html/php_exam
sudo chmod -R 755 /var/www/html/php_exam
```

```
http://localhost/php_exam
```

```
sudo apt install phpmyadmin
```

```
sudo mysql
```

```
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root';
CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED WITH mysql_native_password BY 'root';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EXIT;
```

```
sudo rm /etc/phpmyadmin/config.inc.php
sudo nano /etc/phpmyadmin/config.inc.php
```

```
<?php
/**
 * Configuration simplifiée pour WSL
 */

// Charge la clé secrète pour les cookies
if (file_exists('/var/lib/phpmyadmin/blowfish_secret.inc.php')) {
    require('/var/lib/phpmyadmin/blowfish_secret.inc.php');
}

$i = 1; // ON INITIALISE LE SERVEUR 1

/* Type d'authentification */
$cfg['Servers'][$i]['auth_type'] = 'cookie';

/* Connexion via IP pour éviter les bugs de socket WSL */
$cfg['Servers'][$i]['host'] = '127.0.0.1';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = false;

/* Masquer le choix du serveur et les réglages inutiles au login */
$cfg['DisplayServersList'] = false;
$cfg['AllowArbitraryServer'] = false;

/* Réglages par défaut */
$cfg['DefaultLang'] = 'fr';
$cfg['ServerDefault'] = 1;

/**
 * Répertoires pour charger/sauvegarder
 */
$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';
```

```
sudo ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin
```

```
sudo service apache2 restart
```

```
http://localhost/phpmyadmin
```

--------------------------------------------

```
sudo mysql -u root -p
```
