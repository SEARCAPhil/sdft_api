<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Baskets;
use SDFT\Token;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');



$response=array('status'=>300);


//block if no token in param
if(!isset($_POST['token'])) exit;

$token=htmlentities(htmlspecialchars($_POST['token']));


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

	$response['status']=200;

	$name=@strip_tags(htmlentities(htmlspecialchars($_POST['basket_name'])));
	$description=@strip_tags(htmlentities(htmlspecialchars($_POST['description'])));
	$keywords=@strip_tags(htmlentities(htmlspecialchars($_POST['keywords'])));
	$category=(int) @strip_tags(htmlentities(htmlspecialchars($_POST['category'])));


	//create new basket
	$basket=new Baskets();

	$id=($basket->create($db,$__identity->profile_id,$name,$description,$category,$keywords));

	if($id>0) $response['id']=$id;
	
}



//output in JSON format
echo json_encode($response);

?>