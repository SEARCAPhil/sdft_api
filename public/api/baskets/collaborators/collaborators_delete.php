<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Baskets\Collaborators as Collaborators;
use SDFT\Token;

require_once('../../../../vendor/autoload.php');
require_once('../../../../config/database.php');

$response=array('status'=>300);


@parse_str(file_get_contents("php://input"),$_PUT); 

//block if no token in param
if(!isset($_PUT['token'])||!isset($_PUT['id'])) exit;

$token=htmlentities(htmlspecialchars($_PUT['token']));
$id=(int) htmlentities(htmlspecialchars($_PUT['id']));



//block if token is empty
if(empty($token)) exit;
if(empty($id)) exit;



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


$is_removed=0;

/*--------------------------------
| Prevent unauthorized access
|--------------------------------*/
//get basket information
$collaborators=new Collaborators();


$parent=$collaborators->details($db,$id);
$basket_id=$parent[0]->basket_id;

$basket_collaborators=($collaborators->get_collaborators($db,$basket_id,0));


$collaborators_array=array();

if(isset($basket_collaborators[0]->uid)){

		for ($i=0; $i <count($basket_collaborators); $i++) { 
			
			array_push($collaborators_array, $basket_collaborators[$i]->uid);

		}
	
}


#allow them to view if they are collaborators
if(in_array($__identity->uid,$collaborators_array)){
	$is_removed=$collaborators->remove($db,$id);

}else{
		//set forbidden
	$response['error_code']=403;
	$response['error_message']='Request Forbidden';
}





if($is_removed>0){
	$response['status']=200;
	$response['id']=$is_removed;
}


//output in JSON format
echo json_encode($response);

?>