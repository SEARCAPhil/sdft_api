<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Baskets\Collaborators as Collaborators;
use SDFT\Token;

require_once('../../../../vendor/autoload.php');
require_once('../../../../config/database.php');

$response=array('status'=>300);


$response=array('status'=>300);

//block if no token in param
if(!isset($_GET['token'])||!isset($_GET['id'])) exit;

$token=htmlentities(htmlspecialchars($_GET['token']));
$id=htmlentities(htmlspecialchars($_GET['id']));


//Validate token
$token_class=new Token();
$__identity=$token_class->get_token($db,$token);


$ip=$_SERVER['REMOTE_ADDR'];
//get ip address


if(isset($__identity->id)){

	//check current ip address if the same with identity IP
	if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

	if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;
	
	//Collaborators
	$collaborators=new Collaborators();

	$response['status']=200;
	$response['collaborators']=$collaborators->get_collaborators($db,$id,$__identity->uid);

	
}


//output in JSON format
echo json_encode($response);

?>