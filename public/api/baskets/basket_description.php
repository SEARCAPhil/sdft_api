<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Token;
use SDFT\Baskets;
use SDFT\Activities;
use SDFT\Notifications;
use SDFT\Baskets\Collaborators;



require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

@parse_str(file_get_contents("php://input"),$_PUT); 


$response['status']=300;

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


$last_insert_id=0;

/*--------------------------------
| Prevent unauthorized access
|--------------------------------*/
//get basket information
$collaborators=new Collaborators();

$basket_collaborators=($collaborators->get_collaborators($db,$id,0));


$collaborators_array=array();

if(isset($basket_collaborators[0]->uid)){

		for ($i=0; $i <count($basket_collaborators); $i++) { 
			
			array_push($collaborators_array, $basket_collaborators[$i]->uid);

		}
	
}


#allow them to view if they are collaborators
if(in_array($__identity->uid,$collaborators_array)){

	$basket=new Baskets();

	$last_insert_id=$basket->update_description($db,$id,$description);	

}else{
		//set forbidden
	$response['error_code']=403;
	$response['error_message']='Request Forbidden';
}











if($last_insert_id==1){
	$response['status']=200;

	$activities=new Activities();

	//log to database
	$activities->log_activity($db,$__identity->profile_id,$id,'changed the description of this basket');


	/*--------------------------------
	| Notify Users
	|--------------------------------*/
	//get basket information
	$notifications=new Notifications();


	//send only if basket is already published
	if($basket_collaborators[0]->status!='draft'){

		for ($i=0; $i <count($collaborators_array) ; $i++) { 
			
			//exclude self from notification
			if($__identity->uid!=$collaborators_array[$i]){
				//log to database
				$notifications->notify($db,$__identity->uid,$basket_collaborators[$i]->uid,$id,'changed_description',$description);
			}

		}
	}



}


echo json_encode($response);

?>