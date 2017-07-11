<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Baskets;
use SDFT\Token;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');



$response=array('status'=>300);


//block if no token in param
if(!isset($_GET['token'])&&!isset($_GET['id'])) exit;


$token=htmlentities(htmlspecialchars($_GET['token']));
$id=strip_tags(htmlentities(htmlspecialchars($_GET['id'])));


//block if token is empty
if(empty($token)||empty($id)) exit;


$basket=new Baskets();

$response['details']=($basket->get_details($db,$id)[0]);


//output in JSON format
echo json_encode($response);


?>