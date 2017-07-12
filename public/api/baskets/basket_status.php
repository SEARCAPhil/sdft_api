<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Token;
use SDFT\Baskets;
use SDFT\Activities;
use SDFT\Attachments;
use SDFT\Notifications;
use SDFT\Baskets\Collaborators;



require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

@parse_str(file_get_contents("php://input"),$_PUT); 



//block if no token in param
if(!isset($_PUT['token'])||!isset($_PUT['id'])) exit;


$token=htmlentities(htmlspecialchars($_PUT['token']));
$id=(int) htmlentities(htmlspecialchars($_PUT['id']));



//block if token is empty
if(empty($token)) exit;



//Validate token
$token_class=new Token();
$__identity=$token_class->get_token($db,$token);



//get ip address
$ip=$_SERVER['REMOTE_ADDR'];




if(isset($__identity->id)){

	//check current ip address if the same with identity IP
	if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

	if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;

}




//parse status
$status=@strip_tags(htmlentities(htmlspecialchars($_PUT['status'])));


$activities=new Activities();
$basket=new Baskets();



if($status=='close'){
	$last_insert_id=$basket->update_status($db,$id,'closed');

	//log to database
	$activities->log_activity($db,$__identity->profile_id,$id,'Closed this basket');

	$attachments=new Attachments();
	//close all attachments
	$attachments->close_all($db,$id);


	/*--------------------------------
	| Notify Users
	|--------------------------------*/
	//get basket information
	$collaborators=new Collaborators();
	$notifications=new Notifications();

	$basket_collaborators=($collaborators->get_collaborators($db,$id,$__identity->uid));


	//Notify collaborators about the changes
	if(isset($basket_collaborators[0]->uid)){

		//send only if basket is already published
		if($basket_collaborators[0]->status!='draft'){

			for ($i=0; $i <count($basket_collaborators) ; $i++) { 
				
				//log to database
				$notifications->notify($db,$__identity->uid,$basket_collaborators[$i]->uid,$id,'closed');

			}
		}
	}




}else{
	$last_insert_id=$basket->update_status($db,$id,'open');

	//log to database
	$activities->log_activity($db,$__identity->profile_id,$id,'Open this basket');
}

if($last_insert_id==1){
	$response['status']=200;

}

echo json_encode($response);

?>