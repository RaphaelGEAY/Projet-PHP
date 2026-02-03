# Projet-PHP

```
sudo apt-get install lamp-server^
```

```
sudo systemctl enable --now apache2
sudo systemctl enable --now mysql
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
sudo ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin
```

```
sudo service apache2 restart
```

```
http://localhost/phpmyadmin
```
