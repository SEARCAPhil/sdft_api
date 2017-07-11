<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Token;
use SDFT\Baskets;
use SDFT\Activities;



require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');




parse_str(file_get_contents("php://input"),$input);

//block if no token in param
if(!isset($input['token'])) exit;
if(!isset($input['id'])) exit;


$token=strip_tags(htmlentities(htmlspecialchars($input['token'])));
$id=(int) strip_tags(htmlentities(htmlspecialchars($input['id'])));


//Block if token is empty
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


}



//Remove basket
$basket=new Baskets();
$is_removed=$basket->remove($db,$id);

if($is_removed>0){
	$response['status']=200;
	$response['id']=$is_removed;

	//log to database
	$activities=new Activities();
	$activities->log_activity($db,$__identity->profile_id,$id,'Removed this basket');
}

echo json_encode($response);

?>