<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Email;
use SDFT\Token;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

$method = $_SERVER['REQUEST_METHOD'];

$response=array('status'=>300);


//block if no token in param
if(!isset($_GET['token'])) exit;


//token
$token=htmlentities(htmlspecialchars($_GET['token']));


//param
$param=strip_tags(@htmlentities(htmlspecialchars($_GET['email'])));


//Block if token is empty
if(empty($token)||empty($param)) exit;


//Validate token
$token_class=new Token();
$__identity=$token_class->get_token($db,$token);


//IP address
$ip=$_SERVER['REMOTE_ADDR'];


if($method=='GET'){
	if(isset($__identity->id)){

		//check current ip address if the same with identity IP
		if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

		if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;
		
		$email=new Email();

		$response['status']=200;


		$response['email']=$email->search($db,$param);
		
		

	}	
}




//output in JSON format
echo json_encode($response);

?>