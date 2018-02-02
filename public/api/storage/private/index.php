<?php 
ob_start();
use SDFT\Storage;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Attachments\Token as Attachments_Token;
require_once('../../../../vendor/autoload.php');
require_once('../../../../config/database.php');
$method = $_SERVER['REQUEST_METHOD'];
//block if no token in param
if(!isset($_GET['token'])) exit;
//Params
$token=htmlentities(htmlspecialchars($_GET['token']));
//Block if token is empty
if(empty($token)) exit;
$attachments=new Attachments();
$attachments_token=new Attachments_Token();
$token_details = $attachments_token->view_attachments($db,$token);
if(@isset($token_details[0]->attachments_id)){

	//make sure that token is for public and not deleted
	if($token_details[0]->visibility==1||$token_details[0]->status==1){
		show_visibility_error();
		exit;
	}
	//get parent attachment
	$parent=$attachments_token->details($db,$token_details[0]->id);
	//non empty file
	if(isset($parent[0]->basket_id)){
		$file=($_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/'.$parent[0]->basket_id.'/'.$parent[0]->filename);
		if(file_exists($file)){
			$ext = (@pathinfo($parent[0]->filename,PATHINFO_EXTENSION));

			switch ($ext) {
				case 'pdf':
					show_pdf($file);
					break;
				case 'png':
					show_img($file,$ext);
					break;
				case 'jpg':
					show_img($file,$ext);
					break;
				case 'jpeg':
					show_img($file,$ext);
					break;
				case 'gif':
					show_img($file,$ext);
					break;
				default:
					# code...
					break;
			}
			
		}
	}
}else{
	show_visibility_error();
}

function show_pdf($file){
	header('Content-type: application/pdf');
	header('Content-Disposition: inline; filename=sss');
	header('Content-Transfer-Encoding: binary');
	header('Content-Length: ' . filesize($file));
	header('Accept-Ranges: bytes');
	ob_clean();
	flush();
	readfile($file);
	exit;
}

function show_img($file,$ext){
	header('Content-Type : image/'.$ext);
	$base64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($file));
	echo '<style>body{padding:0;margin:0;overflow-x:hidden;}</style><img src="'.$base64.'"/>';
}

function show_visibility_error(){
	echo '<center><br/><br/>
		<h1 style="color:red;">Access Forbidden</h1>
		<p>You dont have eough privilege to access this file or your token is expired.<br/>Please contact the system administrator for more information</p>
	</center>';
}


?>