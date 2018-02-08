<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Token;
use SDFT\Baskets;
use SDFT\Baskets\Routes;
use SDFT\Activities;
use SDFT\Notifications;
use SDFT\Baskets\Collaborators;




require_once('../../../../vendor/autoload.php');
require_once('../../../../config/database.php');

@parse_str(file_get_contents("php://input"),$_PUT); 


//block if no token in param
if(!isset($_PUT['token'])||!isset($_PUT['id'])||!isset($_PUT['action'])) exit;


$token=htmlentities(htmlspecialchars($_PUT['token']));
$id=(int) htmlentities(htmlspecialchars($_PUT['id']));
$action=htmlentities(htmlspecialchars($_PUT['action']));




//block if token is empty
if(empty($token)||empty($action)) exit;



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





$activity=new Activities();



	/*--------------------------------
	| Prevent unauthorized access
	|--------------------------------*/
	//get basket information
	$collaborator=new Collaborators();

	$basket_collaborators=($collaborator->get_collaborators($db,$id,0));


	$collaborators_array=array();
	//Notify collaborators about the changes
	if(isset($basket_collaborators[0]->uid)){

			for ($i=0; $i <count($basket_collaborators) ; $i++) { 
				
				array_push($collaborators_array, $basket_collaborators[$i]->uid);

			}
		
	}


	#allow them to view if they are collaborators
	if(in_array($__identity->uid,$collaborators_array)&&$basket_collaborators[0]->status!='closed'){
	//create new basket
		$route=new Routes();

		$lastId=$route->create($db,$__identity->profile_id,$id,($action=='out'?'out':'in'));

		if($lastId>0){
			//successfull response
			$response['id']=$lastId;
			$response['status']=200;

			//log
			if($action=='in'){
				$activity->log_activity($db,$__identity->profile_id,$id,'Received this basket');	
			}else{
				$activity->log_activity($db,$__identity->profile_id,$id,'Sent this basket');
			}

			/*--------------------------------
			| Notify Users
			|--------------------------------*/
			//get basket information
			$notification=new Notifications();
			//send notification
			for ($i=0; $i <count($collaborators_array) ; $i++) { 
			
				//exclude self from notification
				if($__identity->uid!=$collaborators_array[$i]){
					//log to database
					$notification->notify($db,$__identity->uid,$basket_collaborators[$i]->uid,$id,$action=='in'?'route_in':'route_out',$action=='in'?'Received this basket':'Sent this basket');
				}

			}
			
			
		} 


	}else{
		//set forbidden
		$response['error_code']=403;
		$response['error_message']='Request Forbidden';
	}

echo json_encode($response);

?>