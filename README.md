# dts_api
Restful API for DTS


*******************
##### Server Requirements #####

PHP version 5.6 or newer is recommended.

It should work on 5.3.7 as well, but we strongly advise you NOT to run
such old versions of PHP, because of potential security and performance
issues, as well as missing features.  You will also need to install `composer`
for installing dependencies.

************
##### Dependency #####
 For all commands available please visit their [website](https://getcomposer.org/)
```php
$ composer install
```


************
##### Database Configuration #####


Change database configuration in `config/database.php`

Look for the lines below and change it with your server's configuration
```php
<?php
/*DATABASE CONFIGURATION*/

$_host = '127.0.0.1';
$_username = 'root';
$_password = '';
$_dbname = 'sdft';

$db = new \PDO('mysql:host='.$_host.';dbname='.$_dbname.';',$_username);
$db->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);

?>
```
> Replace the `$db` value if `$_password` is required  
`$db = new \PDO('mysql:host='.$_host.';dbname='.$_dbname.';',$_username, $password);` 

