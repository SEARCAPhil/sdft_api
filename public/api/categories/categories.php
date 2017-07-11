<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Token;
use SDFT\Categories;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

$id=(int) strip_tags(htmlentities(htmlspecialchars((@$_GET['id']))));

$categories=new Categories();

if(empty($id)){

	$response['categories']=$categories->get_parent_categories($db);

}else{

	$response['categories']=$categories->get_sub_categories($db,$id);
}


//output in JSON format
echo json_encode($response);

?>