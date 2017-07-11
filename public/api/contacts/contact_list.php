<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Token;
use SDFT\Contacts;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

$response=array('status'=>300);

if(!isset($_GET['page'])){
	$page=1;
}else{
	$page=(int) trim(strip_tags(htmlentities(htmlspecialchars($_GET['page']))));
}


$contacts=new Contacts();
$contact_list=($contacts->get_contacts_by_department($db,$page));

//attachments
$response['contacts']=array();

for ($i=0; $i <count($contact_list); $i++) { 
	$letter=strtoupper(substr($contact_list[$i]->first_name,0,1));

	#set first
	if(!isset($response['contacts'][$letter]))  $response['contacts'][$letter]=array();

	$response['contacts'][$letter][]=$contact_list[$i];


}

$response['status']=200;

//output in JSON format
echo json_encode($response);

?>