<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Token;
use SDFT\Baskets;
use SDFT\Activities;
use SDFT\Attachments;
use SDFT\Notifications;
use SDFT\Baskets\Collaborators;



require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

@parse_str(file_get_contents("php://input"),$_PUT); 



//block if no token in param
if(!isset($_PUT['token'])||!isset($_PUT['id'])) exit;


$token=htmlentities(htmlspecialchars($_PUT['token']));
$id=(int) htmlentities(htmlspecialchars($_PUT['id']));

$response['status']=300;

//block if token is empty
if(empty($token)) exit;



//Validate token
$token_class=new Token();
$__identity=$token_class->get_token($db,$token);

//parse status
$keywords=@strip_tags(htmlentities(htmlspecialchars($_PUT['keywords'])));


//get ip address
$ip=$_SERVER['REMOTE_ADDR'];




if(isset($__identity->id)){

	//check current ip address if the same with identity IP
	if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

	if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;

}






$last_insert_id=0;




$activities=new Activities();
$basket=new Baskets();
$parent=$basket->get_details($db,$id);


/*--------------------------------
| Prevent unauthorized access
|--------------------------------*/
//get basket information
$collaborators=new Collaborators();

$basket_collaborators=($collaborators->get_collaborators($db,$id,0));


$collaborators_array=array();

if(isset($basket_collaborators[0]->uid)){

		for ($i=0; $i <count($basket_collaborators); $i++) { 
			
			array_push($collaborators_array, $basket_collaborators[$i]->uid);

		}
	
}


#allow them to view if they are collaborators
if(in_array($__identity->uid,$collaborators_array)&&(@$parent[0]->status!='closed')){

		
		$last_insert_id=$basket->update_keywords($db,$id,$keywords);

		//log to database
		$activities->log_activity($db,$__identity->profile_id,$id,'Updated keywords to '.$keywords);
		



}else{
	//set forbidden
	$response['error_code']=403;
	$response['error_message']='Request Forbidden';
}





if($last_insert_id==1){
	$response['status']=200;

}

echo json_encode($response);

?>