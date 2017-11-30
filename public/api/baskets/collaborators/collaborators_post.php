<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Baskets\Collaborators as Collaborators;
use SDFT\Token;
use SDFT\Contacts;
use SDFT\Activities;

require_once('../../../../vendor/autoload.php');
require_once('../../../../config/database.php');

$response=array('status'=>300);


//block if no token in param
if(!isset($_POST['token'])||!isset($_POST['collaborators'])||!isset($_POST['id'])) exit;

$token=htmlentities(htmlspecialchars($_POST['token']));
$id=(int) htmlentities(htmlspecialchars($_POST['id']));
$collaborator_json=@json_decode($_POST['collaborators']);
$collaborators_list=$collaborator_json->data;



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


/*--------------------------------
| Prevent unauthorized access
|--------------------------------*/
//get basket information
$collaborators=new Collaborators();


$basket_collaborators=($collaborators->get_collaborators($db,$id,0));


$collaborators_array=array();
$saved=array();


if(isset($basket_collaborators[0]->uid)){

		for ($i=0; $i <count($basket_collaborators); $i++) { 
			
			array_push($collaborators_array, $basket_collaborators[$i]->uid);

		}
	
}

$collaborators_profile=array();

$Contacts=new Contacts();
#allow them to add if they are collaborators
if(in_array($__identity->uid,$collaborators_array)&&($basket_collaborators[0]->status!='closed')){
	#save collaborator
	$saved=array();
	foreach ($collaborators_list as $key => $value) {
		if(strlen($value)>0){

			$result=$collaborators->create($db,$id,(int) $key);


			/*---------------------------------
			| Collaborators Information
			|----------------------------------*/
			$collaborator_info=($Contacts->get_profile($db,(int) $key));


			if(isset($collaborator_info[0]->id)){

				$name=strlen($collaborator_info[0]->profile_name)<2?$collaborator_info[0]->first_name.' '.$collaborator_info[0]->last_name:$collaborator_info[0]->profile_name;

				array_push($collaborators_profile, $name);
			}


			/*---------------------------------
			| Results
			|----------------------------------*/

			if($result>0){
				$saved[$key]=$result;
			}else{
				$saved[$key]=null;
			}
			

		}
	}	

}else{
	//set forbidden
	$response['error_code']=403;
	$response['error_message']='Request Forbidden';
}



if(count($saved)>0){
	$response['status']=200;
	$response['saved']=$saved;


		
	/*-----------------------------------------------
	| Activities
	|
	|-----------------------------------------------*/
	$activities=new Activities();

	//2 to 5 collaborators
	if(count($collaborators_profile)>2){
		$n='';
		for($x=0;$x<2;$x++){
			$n.=$collaborators_profile[$x];

			if($x<1){
				$n.=',';
			}else{
				$n.=' ';
			}
		}

		$n.=' and '.(count($collaborators_profile)-2).' others were added';
		$activities->log_activity($db,$__identity->profile_id,$id,$n);
	}


	//only one collaborator
	if(count($collaborators_profile)==1){
	
		$activities->log_activity($db,$__identity->profile_id,$id,$collaborators_profile[0].' '.'was added');
	}


	//two collaborators
	if(count($collaborators_profile)==2){
	
		$n=$collaborators_profile[0].' and '.$collaborators_profile[1].' were added to the basket';
		$activities->log_activity($db,$__identity->profile_id,$id,$n);
	}


	
}


//output in JSON format
echo json_encode($response);

?>