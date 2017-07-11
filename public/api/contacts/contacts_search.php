<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Token;
use SDFT\Contacts;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

$response=array('status'=>300);

//param must not be empty
if(!isset($_GET['param'])) exit;

//filter page
if(!isset($_GET['page'])){
	$page=1;
}else{
	$page=htmlentities(htmlspecialchars($_GET['page']));
}

//filter param
$param=trim(strip_tags(htmlentities(htmlspecialchars($_GET['param']))));

$contacts=new Contacts();

$contacts_result=$contacts->search($page,$param,$db);

//results
$response['status']=200;
$response['contacts']=$contacts_result;

//output in JSON format
echo json_encode($response);
?>