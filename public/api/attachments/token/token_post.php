<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Baskets;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Attachments\Token as Attachment_Token;



require_once('../../../../vendor/autoload.php');
require_once('../../../../config/database.php');

$response['status']=300;


//block if no token in param
if(!isset($_POST['token'])||!isset($_POST['id'])||!isset($_POST['visibility'])) exit;

$token=htmlentities(htmlspecialchars($_POST['token']));
$id=htmlentities(htmlspecialchars($_POST['id']));
$visibility=@htmlentities(htmlspecialchars($_POST['visibility']));



//Block if token is empty
if(empty($token)) exit;



//Validate token
$token_class=new Token();

$__identity=$token_class->get_token($db,$token);

$ip=$_SERVER['REMOTE_ADDR'];


if(isset($__identity->id)){

	//check current ip address if the same with identity IP
	if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

	if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;





}


$attachment_token=new Attachment_Token();

if($visibility=='all'){
	$generated_token = md5(sha1(date('y-m-d h:i:s').'SDFT salt'));
	$response['token'] = $generated_token;
	$response['id']=$attachment_token->create_public($db,$id,$generated_token);
	$response['status']=200;
}else{
	if(!isset($_POST['username'])) return 0;
	$username = htmlentities(htmlspecialchars($_POST['username']));
	$generated_token = md5(sha1(date('y-m-d h:i:s').'SDFT salt'));
	$response['token'] = $generated_token;
	$u = explode(',',trim($username));

	$u_array=[];

	for($x=0;$x<count($u);$x++){
		array_push($u_array, trim($u[$x]));
	}

	$ux=implode(',',$u_array);

	$response['id']=$attachment_token->create_private($db,$id,$generated_token,$ux);
	$response['status']=200;	
}








echo json_encode($response);

?>