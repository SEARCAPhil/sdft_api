<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Token;
use SDFT\Baskets;
use SDFT\Baskets\Notes;
use SDFT\Activities;
use SDFT\Notifications;
use SDFT\Baskets\Collaborators;




require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

@parse_str(file_get_contents("php://input"),$_PUT); 


//block if no token in param
if(!isset($_PUT['token'])||!isset($_PUT['id'])||!isset($_PUT['notes'])||!isset($_PUT['note_id'])) exit;


$token=htmlentities(htmlspecialchars($_PUT['token']));
$id=(int) htmlentities(htmlspecialchars($_PUT['id']));
$note_id=(int) htmlentities(htmlspecialchars($_PUT['note_id']));
$note=utf8_encode(trim($_PUT['notes']));



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





$activities=new Activities();
$basket=new Baskets();


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

		$lastId=$notes->update($db,$note_id,$note);
		$trimmedNotes=strlen($note)>=200?substr($note, 0,200).'. . .':$note;

		if($lastId>0){
			//successfull response
			$response['id']=$lastId;
			$response['status']=200;
			//log
			$activities=new Activities();
			$activities->log_activity($db,$__identity->profile_id,$id,'Updated a note to "'.$trimmedNotes.'"');
			
		} 


	}else{
		//set forbidden
		$response['error_code']=403;
		$response['error_message']='Request Forbidden';
	}

echo json_encode($response);

?>