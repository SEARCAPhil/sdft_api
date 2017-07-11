<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Baskets;
use SDFT\Token;

require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');



$response=array('status'=>300);


//block if no token in param
if(!isset($_GET['token'])) exit;


//Params
$token=htmlentities(htmlspecialchars($_GET['token']));
$status=strip_tags(@htmlentities(htmlspecialchars($_GET['status'])));
$response['category']=$status;


//Page
$page=strip_tags(@htmlentities(htmlspecialchars($_GET['page'])));


//Block if token is empty
if(empty($token)) exit;



//Validate token
$token_class=new Token();
$__identity=$token_class->get_token($db,$token);



//IP address
$ip=$_SERVER['REMOTE_ADDR'];



if(isset($__identity->id)){

	//check current ip address if the same with identity IP
	if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

	if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;
	
	$basket=new Baskets();

	$response['status']=200;
	$response['baskets']=$basket->get_list($db,$__identity->uid,$status,$page);

}



//output in JSON format
echo json_encode($response);

?>