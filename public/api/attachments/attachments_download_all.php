<?php
header('Access-Control-Allow-Origin: *');
ob_start();
/*@ini_set('zlib.output_compression', 'Off');
@ini_set('output_buffering', 'Off');
@ini_set('output_handler', '');
@apache_setenv('no-gzip', 1);*/
session_start();



use SDFT\Baskets;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Activities;
use SDFT\Baskets\Collaborators;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');


$response=array('status'=>300);

function view_attachment($db,$id){
		
	try{
		$id=htmlentities(htmlspecialchars($id));
		$file_exists=0;
		$returnFile=null;
			$attach_sql="SELECT attachments.* from attachments left join basket on basket.id=attachments.basket_id where attachments.id=:id";
			$attach_statement=$db->prepare($attach_sql);
			$attach_statement->bindParam(':id',$id);
			$attach_statement->execute();
			if($row=$attach_statement->fetch(PDO::FETCH_OBJ)){

					$basket_id = $row->basket_id;

					if(!is_null($row->original_copy_id)){
						//original copy informatoin
						$attach_sql2="SELECT attachments.* from attachments left join basket on basket.id=attachments.basket_id where attachments.id=:id";
						$attach_statement2=$db->prepare($attach_sql2);
						$attach_statement2->bindParam(':id',$row->original_copy_id);
						$attach_statement2->execute();

						if($row2=$attach_statement2->fetch(PDO::FETCH_OBJ)){
							
							//original basket where file is located on the server
							$basket_id = $row2->basket_id;
						}
					}
				
					if(file_exists($_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/'.$basket_id.'/'.$row->filename)){
						$file_exists=1;
						$returnFile=$_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/'.$basket_id.'/'.$row->filename;	
					}
			}

			return $returnFile;


	}catch(PDOException $e){$db->rollback(); echo $e->getMessage();}



}


//block if no token in param
if(!isset($_GET['token'])) exit;

$token=htmlentities(htmlspecialchars($_GET['token']));



//Block if token is empty
if(empty($token)) exit;



//Validate token
$token_class=new Token();
$__identity=$token_class->get_token($db,$token);



$ip=$_SERVER['REMOTE_ADDR'];
//get ip address



if(isset($__identity->id)){
	//check current ip address if the same with identity IP
//	if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

//	if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;

}



//must contain files
if(!isset($_GET['id']))	exit;

$id=(int) htmlentities(htmlspecialchars($_GET['id']));


//get parent basket
$attachments=new Attachments();
$basket=new Baskets();
#$parent=$attachments->details($db,$id);
$basket_details=($basket->get_details($db,$id)[0]);
$basket_id = $basket_details->id;
#$file_name=$parent[0]->original_filename;

	/*--------------------------------
	| Prevent unauthorized access
	|--------------------------------*/
	//get basket information
	$collaborators=new Collaborators();

	$basket_collaborators=($collaborators->get_collaborators($db,$basket_id,0));


	$collaborators_array=array();

	if(isset($basket_collaborators[0]->uid)){

			for ($i=0; $i <count($basket_collaborators); $i++) { 
				
				array_push($collaborators_array, $basket_collaborators[$i]->uid);

			}
		
	}


	#allow them to view if they are collaborators
	if(in_array($__identity->uid,$collaborators_array)){
    
    $zip = new \ZipArchive();
    $basket_name = strtolower(trim(strip_tags($basket_details->basket_name)));
    $zipname = implode('-', explode(' ', $basket_name)).'.zip';
    $path = $_SERVER['DOCUMENT_ROOT'].'/sdft_api/public/uploads/temp/';

    # remove zip file first if exists
    if(file_exists($path.$zipname)) {
      unlink($path.$zipname);
    }

    if ($zip->open($path.$zipname, ZipArchive::CREATE)!==TRUE) {
      exit("cannot open <$zipname>\n");
    }

    foreach($basket_details->attachments as $key => $value) {
      $attachment_id = $value['files']['id'];
      $attachment = view_attachment($db, $attachment_id);
      // should have a valid file
      if(!is_null($attachment)) {
        $zip->addFile($attachment,strip_tags($value['files']['name']));
      }
    }


      if($zip->close()) {
        if(count($basket_details->attachments) > 0) {
          header("Pragma: public");
          header("Expires: 0");
          header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
          header("Cache-Control: public");
          header("Content-Description: File Transfer");
          header("Content-type: application/octet-stream");
          header("Content-Disposition: attachment; filename=\"".$zipname."\"");
          header("Content-Transfer-Encoding: binary");
          ob_end_flush();
          @readfile($path.$zipname);
        }
      } else {
        echo 'File not found!This will automatically close after 5 seconds.<script>setTimeout(function(){window.close();},5000);</script>';
      }

	}else{
		echo 'File not found!This will automatically close after 5 seconds.<script>setTimeout(function(){window.close();},5000);</script>';
	}



?>