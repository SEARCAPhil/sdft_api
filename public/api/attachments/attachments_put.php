<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Baskets;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Activities;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');


@parse_str(file_get_contents("php://input"),$_PUT); 

//block if no token in param
if(!isset($_PUT['token'])||!isset($_PUT['id'])) exit;

$token=htmlentities(htmlspecialchars($_PUT['token']));
$id=(int) htmlentities(htmlspecialchars($_PUT['id']));



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


//parse status
$status=@strip_tags(htmlentities(htmlspecialchars($_PUT['status'])));



$attachments=new Attachments();
$activities=new Activities();



//get parent basket
$parent=$attachments->get_parent_basket($db,$id);
$basket_id=$parent[0]->basket_id;
$file_name=$parent[0]->original_filename;

if($status=='closed' || strlen($status)<1){
	$last_insert_id=$attachments->update_attachment_status($db,$id,'closed');


	//log to database
	$activities->log_activity($db,$__identity->profile_id,$basket_id,'Closed '.$file_name);

}else{
	$last_insert_id=$attachments->update_attachment_status($db,$id,'open');


	//log to database
	$activities->log_activity($db,$__identity->profile_id,$basket_id,'Open '.$file_name);

}

if($last_insert_id==1){
	$response['status']=200;

}

echo json_encode($response);

?>