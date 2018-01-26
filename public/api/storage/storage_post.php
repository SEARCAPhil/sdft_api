<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Baskets;
use SDFT\Storage;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Activities;
use SDFT\Notifications;
use SDFT\Baskets\Collaborators;

require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

$method = $_SERVER['REQUEST_METHOD'];

$response=array('status'=>300);


//block if no token in param
if(!isset($_POST['token'])) exit;


//Params
$token=htmlentities(htmlspecialchars($_POST['token']));



//Block if token is empty
if(empty($token)) exit;


//Validate token
$token_class=new Token();
$__identity=$token_class->get_token($db,$token);


//IP address
$ip=$_SERVER['REMOTE_ADDR'];


if($method=='POST'){
	if(isset($__identity->id)){

		//check current ip address if the same with identity IP
		if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

		if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;
		
		$storage=new Storage();
		$attachments=new Attachments();
		$activities=new Activities();
		$notifications=new Notifications();

		$ids = @json_decode($_POST['ids']);
		$new_basket_id = (int) @$_POST['basket_id'];
		$files=[];
		$error_files=[];

		for($x=0;$x<count($ids);$x++){
			//get parent basket
			$parent=$attachments->details($db,$ids[$x]);
			$basket_id=$parent[0]->basket_id;

			/*--------------------------------
			| Prevent unauthorized access
			|--------------------------------*/
			//get basket information
			$collaborators=new Collaborators();

			$basket_collaborators=($collaborators->get_collaborators($db,$basket_id,0));


			$collaborators_array=array();
			//Notify collaborators about the changes
			if(isset($basket_collaborators[0]->uid)){

					for ($i=0; $i <count($basket_collaborators) ; $i++) { 
						
						array_push($collaborators_array, $basket_collaborators[$i]->uid);

					}
				
			}


			#allow them to upload if they are collaborators
			if(in_array($__identity->uid,$collaborators_array)){
				
				$res = $attachments->create($db,$new_basket_id,'',$__identity->profile_id,$parent[0]->type,$parent[0]->original_filename,$parent[0]->filename,$parent[0]->size,'duplicate',$parent[0]->id);

				if($res>1){
					array_push($files,array('basket_id'=>$new_basket_id,'type'=>$parent[0]->type,'filename'=>$parent[0]->original_filename,'new_filename'=>$parent[0]->filename,'size'=>$parent[0]->size,'id'=>$res,'original_copy_id'=>$parent[0]->id));


					//log to db
					$activities->log_activity($db,$__identity->profile_id,$new_basket_id,'Attached new file '.$parent[0]->original_filename);

		
					//send only if basket is already published 
					if($basket_collaborators[0]->status!='draft'){

						for ($i=0; $i <count($collaborators_array) ; $i++) { 
							
							//exclude self from notification
							if($__identity->uid!=$collaborators_array[$i]){
								//log to database
								$notifications->notify($db,$__identity->uid,$collaborators_array[$i],$new_basket_id,'uploaded');
							}

						}
					}

				}

			}else{
				//set forbidden
				array_push($error_files,$ids[$x]);
			}



			
		}

		$response['status']=200;
		$response['files']=$files;

	}	
}







//output in JSON format
echo json_encode($response);

?>