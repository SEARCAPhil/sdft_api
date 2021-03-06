<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Baskets;
use SDFT\Token;
use SDFT\Notifications;
use SDFT\Baskets\Collaborators;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');



$response=array('status'=>300);


//block if no token in param
if(!isset($_GET['token'])&&!isset($_GET['id'])) exit;


$token=htmlentities(htmlspecialchars($_GET['token']));
$id=strip_tags(htmlentities(htmlspecialchars($_GET['id'])));



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




	/*--------------------------------
	| Prevent unauthorized access
	|--------------------------------*/
	//get basket information
	$collaborators=new Collaborators();

	$basket_collaborators=($collaborators->get_collaborators($db,$id,0));


	$collaborators_array=array();

	if(isset($basket_collaborators[0]->uid)){

			for ($i=0; $i <count($basket_collaborators) ; $i++) { 
				
				array_push($collaborators_array, $basket_collaborators[$i]->uid);

			}
		
	}


	#allow them to view if they are collaborators
	if(in_array($__identity->uid,$collaborators_array)){

		$basket=new Baskets();
		$response['status']=200;
		$response['details']=($basket->get_details($db,$id)[0]);

	}else{
		$response['details']=array();
		//set forbidden
		$response['error_code']=403;
		$response['error_message']='Request Forbidden';
	}


/*-------------------------------------------------
| Clear notification if notfication param exists
|-----------------------------------------------*/

if(isset($_GET['notification'])&&isset($_GET['notif'])){
	$notif_id=(int) htmlentities(htmlspecialchars($_GET['notif']));
	$notifications=new Notifications();
	//clear notification
	if(!empty($notif_id)) $notifications->set_read($db,$notif_id);
}


//output in JSON format
echo json_encode($response);


?>