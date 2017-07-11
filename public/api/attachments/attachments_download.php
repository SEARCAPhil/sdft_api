<?php
header('Access-Control-Allow-Origin: *');
@ini_set('zlib.output_compression', 'Off');
@ini_set('output_buffering', 'Off');
@ini_set('output_handler', '');
@apache_setenv('no-gzip', 1);
	session_start();



use SDFT\Baskets;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Activities;


require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');


$response=array('status'=>300);

function download($db,$id){
		

	try{
		$db->beginTransaction();
		$id=htmlentities(htmlspecialchars($id));
		$file_exists=0;
		$returnFile='File not found!This will automatically close after 5 seconds.<script>setTimeout(function(){window.close();},5000);</script>';
			$attach_sql="SELECT attachments.* from attachments left join basket on basket.id=attachments.basket_id where attachments.id=:id";
			$attach_statement=$db->prepare($attach_sql);
			$attach_statement->bindParam(':id',$id);
			$attach_statement->execute();
			if($row=$attach_statement->fetch(PDO::FETCH_OBJ)){

					if(file_exists($_SERVER['DOCUMENT_ROOT'].'/rfps/api/uploads/'.$row->basket_id.'/'.$row->filename)){
						$file_exists=1;
						#headers to force download
						$returnFile=header("Content-Description: File Transfer"); 
						$returnFile.=header("Content-Type: application/octet-stream"); 
						$returnFile.=header("Content-Disposition: attachment; filename=\"$row->filename\"");
						$returnFile.=ob_clean();
						$returnFile.=flush();
						$returnFile.=readfile ($_SERVER['DOCUMENT_ROOT'].'/rfps/api/uploads/'.$row->basket_id.'/'.$row->filename);	
					}
			}

		
			$db->commit();

			if($file_exists){
				return $returnFile;
			}else{
				echo $returnFile;
			}
			

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
	if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

	if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;

}



//must contain files
if(!isset($_GET['id']))	exit;

$id=(int) htmlentities(htmlspecialchars($_GET['id']));

download($db,$id);

?>