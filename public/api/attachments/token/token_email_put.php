<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Baskets;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Attachments\Token as Attachment_Token;

require_once('../../../../vendor/autoload.php');
require_once('../../../../config/database.php');


@parse_str(file_get_contents("php://input"),$_PUT); 

//block if no token in param
if(!isset($_PUT['token'])||!isset($_PUT['id'])) exit;

$token=htmlentities(htmlspecialchars($_PUT['token']));
$id=(int) htmlentities(htmlspecialchars($_PUT['id']));
$email=htmlentities(htmlspecialchars($_PUT['email']));
$response=array();



//Block if token is empty
if(empty($token)||empty($email)) exit;



//Validate token
$token_class=new Token();
$__identity=$token_class->get_token($db,$token);



$ip=$_SERVER['REMOTE_ADDR'];
//get ip address


if(isset($__identity->id)){

	//check current ip address if the same with identity IP
	if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

	if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;





}

$attachment_token=new Attachment_Token();

$response['data']=$attachment_token->update_tokens_email($db,$id,$email);
$response['status']=200;




echo json_encode($response);

?>