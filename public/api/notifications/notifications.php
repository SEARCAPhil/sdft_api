<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Token;
use SDFT\Notifications;




require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');


$response=array('status'=>300);

//block if no token in param
if(!isset($_GET['token'])) exit;

$token=htmlentities(htmlspecialchars($_GET['token']));
$page=(int) isset($_GET['page'])?htmlentities(htmlspecialchars($_GET['page'])):1;


//block if token is empty
if(empty($token)) exit;


//Validate token
$token_class=new Token();
$__identity=$token_class->get_token($db,$token);



$ip=$_SERVER['REMOTE_ADDR'];
//get ip address


if(isset($__identity->id)){

	//check current ip address if the same with identity IP
	if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

	if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;
	
	$notifications=new Notifications();

	$response['status']=200;
	$response['notifications']=$notifications->get_notifications($db,$__identity->uid,$page);



	
}




//output in JSON format
echo json_encode($response);

?>