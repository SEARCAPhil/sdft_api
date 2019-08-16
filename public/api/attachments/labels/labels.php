<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Baskets;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Attachments\Labels;
use SDFT\Activities;
use SDFT\Notifications;
use SDFT\Baskets\Collaborators;
use SDFT\PusherNotification;


require_once('../../../../vendor/autoload.php');
require_once('../../../../config/database.php');

$response['status']=300;


//block if no token in param
if(!isset($_GET['token'])&&!isset($_GET['id'])) exit;

$token=htmlentities(htmlspecialchars($_GET['token']));
$id=strip_tags(htmlentities(htmlspecialchars($_GET['id'])));

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


#allow them if they are one of the collaborators
if(in_array($__identity->uid,$collaborators_array)&&(@$parent[0]->status!='closed')){
	
	$attachment_id = (int)@strip_tags(htmlentities(htmlspecialchars($_GET['id'])));


	//create new basket
	$Lab = new Labels();

  $response['labels'] = @$parent[0]->label;
  $response['id'] = $id;
  $response['status'] = 200;
	

}else{
	//set forbidden
	$response['error_code'] = 403;
	$response['error_message'] = 'Request Forbidden';
}




//output in JSON format
echo json_encode($response);

?>