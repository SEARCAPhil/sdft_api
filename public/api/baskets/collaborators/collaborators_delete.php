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


//Collaborators
$collaborators=new Collaborators();


$is_removed=$collaborators->remove($db,$id);

if($is_removed>0){
	$response['status']=200;
	$response['id']=$is_removed;
}


//output in JSON format
echo json_encode($response);

?>