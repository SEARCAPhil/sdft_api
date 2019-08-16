<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Baskets;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Activities;
use SDFT\Notifications;
use SDFT\Baskets\Collaborators;
use SDFT\PusherNotification;
use SDFT\OCRParser;
use SDFT\TesseractOCRParser;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

$response['status']=300;

function attach($db,$file,$basket_id,$category_id,$author_id){ 
	#attach_to_thumbnails($file['tmp_name']); exit;
	$allowed_format=array('png','jpg','jpeg','pdf','PDF','doc','docx','xls','xlsx');
	$allowed_size=104857600; #100MB
	$file_name=utf8_encode(htmlentities(htmlspecialchars($file['name'])));
	$file_type=htmlentities(htmlspecialchars($file['type']));
	$file_tmp_name=$file['tmp_name'];
	$file_size=$file['size'];
	$ext = pathinfo($file_name, PATHINFO_EXTENSION);
	$dir=$_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/'.$basket_id.'/';
	#check extension
	if(in_array($ext, $allowed_format)){
		#check file size
		if($file_size<$allowed_size){
			#check if no errors occured
			if(is_uploaded_file($file_tmp_name)){
				#new file name to be unique
				$new_file_name=date('mdyhsi').rand().'.'.$ext;

				#check if the path exist else create one
				if(is_dir($dir)){ }else{ mkdir($dir); }
				
				#if file has been uploaded.begin a transaction
				if(move_uploaded_file($file_tmp_name,$dir.$new_file_name)){

					$type=@explode('/',$file_type)[1];


					#save attachemnts to database
					$attachments=new Attachments();
					$attachment_id=$attachments->create($db,$basket_id,$category_id,$author_id,$type,$file_name,$new_file_name,$file_size);

					return $attachment_id;

					
				}else{
					return "something went wrong to wile uploading.Please retry!";

				}
				#/move_uploadede_file

			}#/is_uploaded_file
		}else{

		#return if >5mb
		return "<i>file is larger than 40MB</i>";

		}#/file size check

	}else{
		#return if unsupported
		return "<i>Unsupported file format</i>";

	}#/format

}

# https://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-and-its-entire-contents-files-sub-dir
function rrmdir($dir) { 
	if (is_dir($dir)) { 
		$objects = scandir($dir); 
		foreach ($objects as $object) { 
			if ($object != "." && $object != "..") { 
				if (is_dir($dir."/".$object))
					rrmdir($dir."/".$object);
				else
					unlink($dir."/".$object); 
			} 
		}
		rmdir($dir); 
	} 
}

function attach_to_thumbnails ($file) {
	$teseractParser = new TesseractOCRParser();
	$output_dir = time();
	#$output_dir = $_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/temp/'.$base_dir;
	$teseractParser->PDFtoImage($_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/7/0606190136141601925747.pdf[0]', $output_dir);
	/*$im = new \Imagick($file); 
	$im->setRegistry('temporary-path', './temporary');
	$im->setImageFormat('jpg'); 
	header('Content-Type: image/jpeg'); 
	echo $im;*/
	#var_dump(shell_exec("which /usr/local/bin/convert"));

	#https://stackoverflow.com/questions/31511553/homebrew-recompile-from-edited-source-code/
	#https://github.com/Homebrew/brew/issues/2743
	
	# remove or create directory before uploading
	//$base_dir = '1563239008';
	//$dir = $_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/temp/'.$base_dir;
	/*if(is_dir($dir)) {
		rrmdir($dir);
		mkdir($dir);
	} else {
		mkdir($dir);
	}*/

	//$com = '/usr/local/bin/convert -density 350 -quality 200  -define registry:temporary-path='.$_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/temp/tmp "'.$_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/7/0606190136141601925747.pdf"'.' "'.$dir.'/output-%d.png"';
	//exec($com, $output);
//var_dump($output);
	#var_dump(exec('/usr/local/bin/tesseract '.$dir.'/output-1.png -l eng'));
	/*$text =  (new TesseractOCR($dir.'/output-30.png'))->lang('eng')->run();
	$regex_results = [];

	$ocrParser = new OCRParser();
	$ocrParser->providers(['RFP', 'RevolvingFundReplenishment', 'RevolvingFundDisbursement', 'RevolvingFundAvailment']);
	$ocrParser->text($text);
	$ocrParser->run();
	var_dump($ocrParser->which());
var_dump($text);*/
	#var_dump($ocrParser->results);
}




//block if no token in param
if(!isset($_POST['token'])||!isset($_POST['id'])) exit;

$token=htmlentities(htmlspecialchars($_POST['token']));
$id=htmlentities(htmlspecialchars($_POST['id']));




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





}


//must contain files
if(!isset($_FILES['file']))	exit;



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


#allow them to view if they are collaborators but not yet closed
if(in_array($__identity->uid,$collaborators_array)&&($basket_collaborators[0]->status!='closed')){

	$attachments=new Attachments();
	$file_id=attach($db,$_FILES['file'],$id,null,$__identity->profile_id);

	if($file_id>0){
		$response['id']=$file_id;
		$response['status']=200;

		$activities=new Activities();
		$notifications=new Notifications();


		//log to database
		$file_name=utf8_encode(htmlentities(htmlspecialchars($_FILES['file']['name'])));
		$activities->log_activity($db,$__identity->profile_id,$id,'Attached new file '.$file_name);
		$recent_notification = array();
		
		//send only if basket is already published 
		if($basket_collaborators[0]->status!='draft'){

			for ($i=0; $i <count($collaborators_array) ; $i++) { 
				
				//exclude self from notification
				if($__identity->uid!=$collaborators_array[$i]){
					//log to database
					$notification_id = $notifications->notify($db,$__identity->uid,$collaborators_array[$i],$id,'uploaded');
					// notify channel
					if(!count($recent_notification)) {
						$recent_notification = $notifications->view($db,$notification_id);
					}
					$notif = new PusherNotification ();
					$notif->send("private-{$collaborators_array[$i]}-basket-user",$recent_notification);
				}

			}
		}

	}




}else{
	//set forbidden
	$response['error_code']=403;
	$response['error_message']='Request Forbidden';
}














//get basket information
$collaborators=new Collaborators();
$basket_collaborators=($collaborators->get_collaborators($db,$id,$__identity->uid));



echo json_encode($response);

?>