<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Token;
use SDFT\Baskets;
use SDFT\Baskets\Routes;
use SDFT\Baskets\Collaborators;


require_once('../../../../vendor/autoload.php');
require_once('../../../../config/database.php');


//block if no token in param
if(!isset($_GET['token'])||!isset($_GET['id'])) exit;

$token=htmlentities(htmlspecialchars($_GET['token']));
$id=htmlentities(htmlspecialchars($_GET['id']));


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


	$route=new Routes();
	$response['status']=200;
	



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
	if(in_array($__identity->uid,$collaborators_array)){

		$response['status']=200;
		$response['routes']=$route->get_routes($db,$id);

	}else{
		$response['routes']=array();
		//set forbidden
		$response['error_code']=403;
		$response['error_message']='Request Forbidden';
	}
}



//output in JSON format
echo json_encode($response);

?>