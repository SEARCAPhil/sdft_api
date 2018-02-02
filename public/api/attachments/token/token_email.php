<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Baskets;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Attachments\Token as Attachment_Token;



require_once('../../../../vendor/autoload.php');
require_once('../../../../config/database.php');

$response['status']=300;


//block if no token in param
if(!isset($_GET['token'])||!isset($_GET['id'])) exit;

$token=htmlentities(htmlspecialchars($_GET['token']));
$id=htmlentities(htmlspecialchars($_GET['id']));




//Block if token is empty
if(empty($token)) exit;



//Validate token
$token_class=new Token();

$__identity=$token_class->get_token($db,$token);

$ip=$_SERVER['REMOTE_ADDR'];


if(isset($__identity->id)){

	//check current ip address if the same with identity IP
	if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

	if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;





}


$attachment_token=new Attachment_Token();

$response['tokens']=$attachment_token->get_tokens_email($db,$id);
$response['status']=200;







echo json_encode($response);

?>