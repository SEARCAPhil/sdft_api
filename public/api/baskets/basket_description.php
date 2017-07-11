<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Token;
use SDFT\Baskets;
use SDFT\Activities;



require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

@parse_str(file_get_contents("php://input"),$_PUT); 


//block if no token in param
if(!isset($_PUT['token'])||!isset($_PUT['id'])||!isset($_PUT['description'])) exit;

$token=htmlentities(htmlspecialchars($_PUT['token']));
$id=(int) htmlentities(htmlspecialchars($_PUT['id']));
$description=@strip_tags(htmlentities(htmlspecialchars($_PUT['description'])));



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

}



$basket=new Baskets();

$last_insert_id=$basket->update_description($db,$id,$description);



if($last_insert_id==1){
	$response['status']=200;

}

echo json_encode($response);

?>