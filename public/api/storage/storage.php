<?php
header('Access-Control-Allow-Origin: *');


use SDFT\Storage;
use SDFT\Token;
use SDFT\Attachments;

require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

$method = $_SERVER['REQUEST_METHOD'];

$response=array('status'=>300);


//block if no token in param
if(!isset($_GET['token'])) exit;


//Params
$token=htmlentities(htmlspecialchars($_GET['token']));

//Page
$page=strip_tags(@htmlentities(htmlspecialchars($_GET['page'])));

//Storage type
$storage_type=strip_tags(@htmlentities(htmlspecialchars($_GET['storage'])));


//Block if token is empty
if(empty($token)) exit;


//Validate token
$token_class=new Token();
$__identity=$token_class->get_token($db,$token);


//IP address
$ip=$_SERVER['REMOTE_ADDR'];


if($method=='GET'){
	if(isset($__identity->id)){

		//check current ip address if the same with identity IP
		if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

		if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;
		
		$storage=new Storage();

		$response['status']=200;

		//uploaded files
		if($storage_type=='personal'){
			$response['files']=$storage->get_list_personal($db,$__identity->uid,$page);
		}else{
			$response['files']=$storage->get_list_shared($db,$__identity->uid,$page);	
		}
		

	}	
}




//output in JSON format
echo json_encode($response);

?>