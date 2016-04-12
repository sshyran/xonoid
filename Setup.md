# Install PHP5, Apache2, MySQL5 #
Ubuntu Intrepid Ibex:
```
aptitude install libapache2-mod-php5 
aptitude install mysql-server-5.0 
aptitude install php5 php5-{curl,gd,mysql,mcrypt,mhash} phpmyadmin
```

# Get latest version from SVN trunk #
Example:
```
svn --force export http://xonoid.googlecode.com/svn/trunk/ /home/xonoid
```
Or if you want SVN metadata, use [checkout](http://code.google.com/p/xonoid/source/checkout)

# Add Apache virtual host #
Example for Ubuntu:
  * Open file `/etc/apache2/sites-available/crm.vhost` with your favorite editor
  * run `a2ensite crm.vhost` to add crm.vhost to apache's sites-enabled directory
  * restart apache `/etc/init.d/apache2 restart`

`/etc/apache2/sites-available/crm.vhost` contents:
```
<VirtualHost *>
  DocumentRoot /home/xonoid/public_html
  ServerName crm.dev.lan
  Options +All
</VirtualHost>
```

# Add MySQL Database #
Example:
Database = crm
Username = xonoid
Password = xonoidcrm
```
mysql -u root -p
```

SQL:
```
CREATE DATABASE crm;
GRANT USAGE ON *.* to xonoid@localhost IDENTIFIED BY 'xonoidcrm';
GRANT ALL PRIVILEGES ON crm.* TO xonoid@localhost;
FLUSH PRIVILEGES;
```
To quit write `quit` or `\q`

# Configure XoNoiD #
  * Modify `config.ini` with database settings
  * Generate string at _least 64 characters_ long as **salt** (a-z, A-Z, 0-9)

# Initialize database #

Use MySQL Workbench to open `crm.mwb` or dump `db.sql` to your database with phpmyadmin or `mysql` CLI command.

Example:
```
mysql -u crm -p < db.sql
```

After that add:

```
INSERT INTO `PORT_TYPES` (`name`) VALUES('Ethernet 10M');
INSERT INTO `PORT_TYPES` (`name`) VALUES('Ethernet 100M');
INSERT INTO `PORT_TYPES` (`name`) VALUES('Ethernet 1000M');
INSERT INTO `PORT_TYPES` (`name`) VALUES('Ethernet 10000M');
INSERT INTO `PORT_TYPES` (`name`) VALUES('Admin Serial');
INSERT INTO `PORT_TYPES` (`name`) VALUES('Admin Ethernet');
INSERT INTO `PORT_TYPES` (`name`) VALUES('ATM 155M');
INSERT INTO `PORT_TYPES` (`name`) VALUES('Fiber 1000M');
```

Add first user so you can log in:

```
INSERT INTO 
  `USERS` (`companyid`, `firstname`, `lastname`, `email`, `phone`, `password`)
VALUES
  (
    NULL, 'My', 'Name', 'my.email@address-here.com', '1234567', 
    MD5(CONCAT('insert your salt here from config.ini', 'password you want here')
  )
);
```

# Add permissions #
  * Add write permissions to `cache` directory
```
chmod a+rwx cache
```

# Ready to go #
Now you can login and add companies etc.