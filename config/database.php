<?php
/*DATABASE CONFIGURATION*/

$_host='127.0.0.1';
$_username='root';
$_password='';
$_dbname='sdft';

$db=new \PDO('mysql:host='.$_host.';dbname='.$_dbname.';',$_username);
$db->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);

?>