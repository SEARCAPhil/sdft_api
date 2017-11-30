<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Baskets;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Activities;
use SDFT\Baskets\Collaborators;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');


parse_str(file_get_contents("php://input"),$input);


//block if no token in param
if(!isset($input['token'])) exit;
if(!isset($input['id'])) exit;


$token=strip_tags(htmlentities(htmlspecialchars($input['token'])));
$id=(int) strip_tags(htmlentities(htmlspecialchars($input['id'])));
$response=array();


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
	
	//$is_removed=$attachments->remove($db,$id);

}else{
	//set forbidden
	$response['error_code']=403;
	$response['error_message']='Request Forbidden';
}



//rename file
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/'.$basket_id.'/'.$parent[0]->filename)){

	@rename($_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/'.$basket_id.'/'.$parent[0]->filename,$_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/'.$basket_id.'/'.$parent[0]->filename.'.deleted');
	

}







if($is_removed>0){
	$response['status']=200;
	$response['id']=$is_removed;

	//log to database
	$activities->log_activity($db,$__identity->profile_id,$basket_id,'Removed an attachment to this basket');

}

echo json_encode($response);

?>