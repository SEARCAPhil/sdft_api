<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Baskets\Collaborators as Collaborators;
use SDFT\Token;

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


//Collaborators
$collaborators=new Collaborators();

#save collaborator
$saved=array();
foreach ($collaborators_list as $key => $value) {
	if(strlen($value)>0){
		$result=$collaborators->create($db,$id,(int) $key);
		if($result>0){
			$saved[$key]=$result;
		}else{
			$saved[$key]=null;
		}
		
	}
}

if(count($saved)>0){
	$response['status']=200;
	$response['saved']=$saved;
}


//output in JSON format
echo json_encode($response);

?>