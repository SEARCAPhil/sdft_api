<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Baskets;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Activities;
use SDFT\Notifications;
use SDFT\Baskets\Collaborators;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');



@parse_str(file_get_contents("php://input"),$_PUT); 


//block if no token in param
if(!isset($_PUT['token'])||!isset($_PUT['id'])|!isset($_PUT['category'])) exit;

$token=htmlentities(htmlspecialchars($_PUT['token']));
$id=(int) htmlentities(htmlspecialchars($_PUT['id']));
$category=(int) htmlentities(htmlspecialchars($_PUT['category']));


$response['status']=300;

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
$category=@strip_tags(htmlentities(htmlspecialchars($_PUT['category'])));
$last_insert_id=0;



$attachments=new Attachments();
$activities=new Activities();



//get parent basket
$parent=$attachments->details($db,$id);
$basket_id=$parent[0]->basket_id;
$file_name=$parent[0]->original_filename;



	/*--------------------------------
	| Prevent unauthorized access
	|--------------------------------*/
	//get basket information
	$collaborators=new Collaborators();

	$basket_collaborators=($collaborators->get_collaborators($db,$basket_id,0));


	$collaborators_array=array();

	if(isset($basket_collaborators[0]->uid)){

			for ($i=0; $i <count($basket_collaborators); $i++) { 
				
				array_push($collaborators_array, $basket_collaborators[$i]->uid);

			}
		
	}


	$new_category='';

	#allow them to view if they are collaborators
	if(in_array($__identity->uid,$collaborators_array)){

		//get the new category details
		$new_category=$attachments->get_attachment_category($db,$category);

	}else{
		$response['details']=array();
		//set forbidden
		$response['error_code']=403;
		$response['error_message']='Request Forbidden';
	}








//do not update if new category is empty



if($category>1&&strlen($new_category)>1){

	$last_insert_id=$attachments->update_attachment_category($db,$id,$category);

	//log to database
	$activities->log_activity($db,$__identity->profile_id,$basket_id,'Set category of '.$file_name. ' to '.$new_category);




	/*--------------------------------
	| Notify Users
	|--------------------------------*/
	//get basket information

	$notifications=new Notifications();

		//send only if basket is already published
		if($basket_collaborators[0]->status!='draft'){

			for ($i=0; $i <count($collaborators_array); $i++) { 
				
				if($collaborators_array[$i]!=$__identity->uid){
					//log to database
					$notifications->notify($db,$__identity->uid,$collaborators_array[$i],$basket_id,'file_category','Set category of '.$file_name. ' to '.$new_category);
				}

			}
		}


}

if($last_insert_id==1){
	$response['status']=200;
	$response['category']=$new_category;

}

echo json_encode($response);

?>