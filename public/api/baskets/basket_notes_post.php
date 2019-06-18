<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Baskets;
use SDFT\Baskets\Notes;
use SDFT\Baskets\Collaborators;
use SDFT\Token;
use SDFT\Activities;
use SDFT\Notifications;
use SDFT\PusherNotification;




require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');



$response=array('status'=>300);


//block if no token in param
if(!isset($_POST['token'])||!isset($_POST['id'])) exit;

$token=htmlentities(htmlspecialchars($_POST['token']));
$id=htmlentities(htmlspecialchars($_POST['id']));


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

	$response['status']=200;

	$note=@strip_tags(htmlentities(htmlspecialchars($_POST['notes'])));



	/*--------------------------------
	| Prevent unauthorized access
	|--------------------------------*/
	//get basket information
	$collaborators=new Collaborators();

	$basket_collaborators=($collaborators->get_collaborators($db,$id,0));


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
		$notes=new Notes();

		$lastId=$notes->create($db,$__identity->profile_id,$id,$note);
		$trimmedNotes=strlen($note)>=200?substr($note, 0,200).'. . .':$note;

		if($lastId>0){
			//successfull response
			$response['id']=$lastId;
			//log
			$activities=new Activities();
			$activities->log_activity($db,$__identity->profile_id,$id,'Posted a note "'.$trimmedNotes.'" on this basket');


			/*--------------------------------
			| Notify Users
			|--------------------------------*/
			//get basket information
			$notifications=new Notifications();
			$recent_notification = array();

			$basket_collaborators=($collaborators->get_collaborators($db,$id,$__identity->uid));


			//Notify collaborators about the changes
			if(isset($basket_collaborators[0]->uid)){

				//send only if basket is already published
				if($basket_collaborators[0]->status!='draft'){

					for ($i=0; $i <count($basket_collaborators) ; $i++) { 

						//exclude self from notification
						if($__identity->uid!=$collaborators_array[$i]){
						
							//log to database
							$notification_id = $notifications->notify($db,$__identity->uid,$basket_collaborators[$i]->uid,$id,'notes');
							// notify channel
							if(!count($recent_notification)) {
								$recent_notification = $notifications->view($db,$notification_id);
							}
							$notif = new PusherNotification ();
							$notif->send("private-{$collaborators_array[$i]}-basket-user",$recent_notification);
						}

					}
				}
			}
			
		} 


		



	}else{
		//set forbidden
		$response['error_code']=403;
		$response['error_message']='Request Forbidden';
	}

	
}



//output in JSON format
echo json_encode($response);

?>