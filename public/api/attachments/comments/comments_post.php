<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Baskets;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Attachments\Comments;
use SDFT\Activities;
use SDFT\Notifications;
use SDFT\Baskets\Collaborators;


require_once('../../../../vendor/autoload.php');
require_once('../../../../config/database.php');

$response['status']=300;

//block if no token in param
if(!isset($_POST['token'])) exit;

$token=htmlentities(htmlspecialchars($_POST['token']));
$id=@htmlentities(htmlspecialchars($_POST['id']));


//Block if token is empty
if(empty($token)||empty($id)) exit;



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


$attachments=new Attachments();
$activities=new Activities();


//get parent basket
$parent=$attachments->details($db,$id);
$basket_id=$parent[0]->basket_id;




$is_removed=0;

/*--------------------------------
| Prevent unauthorized access
|--------------------------------*/
//get basket information
$collaborators=new Collaborators();

$basket_collaborators=($collaborators->get_collaborators($db,$basket_id,0));


$collaborators_array=array();
//Notify collaborators about the changes
if(isset($basket_collaborators[0]->uid)){

		for ($i=0; $i <count($basket_collaborators) ; $i++) { 
			
			array_push($collaborators_array, $basket_collaborators[$i]->uid);

		}
	
}


#allow them to delete if they are collaborators
if(in_array($__identity->uid,$collaborators_array)&&(@$parent[0]->status!='closed')){
	
	
	$comment=@strip_tags(htmlentities(htmlspecialchars($_POST['comment'])));
	$attachment_id=(int )@strip_tags(htmlentities(htmlspecialchars($_POST['id'])));


	//create new basket
	$Com=new Comments();
	$activities=new Activities();
	$notifications=new Notifications();

	$id=($Com->create($db,$__identity->profile_id,$attachment_id,$comment));

	if($id>0){

		$activities->log_activity($db,$__identity->profile_id,$basket_id,'Posted a comment "'.(strlen($comment)>200?substr($comment, 0,200).'. . .':$comment).'" in '.$parent[0]->original_filename);

		//send only if basket is already published 
		if($basket_collaborators[0]->status!='draft'){

			for ($i=0; $i <count($collaborators_array) ; $i++) { 
				
				//exclude self from notification
				if($__identity->uid!=$collaborators_array[$i]){
					//log to database
					$notifications->notify($db,$__identity->uid,$collaborators_array[$i],$basket_id,'comment',(strlen($comment)>200?substr($comment, 0,200).'. . .':$comment));
				}

			}
		}

		$response['id']=$id;
		$response['status']=200;
	} 

}else{
	//set forbidden
	$response['error_code']=403;
	$response['error_message']='Request Forbidden';
}




//output in JSON format
echo json_encode($response);

?>