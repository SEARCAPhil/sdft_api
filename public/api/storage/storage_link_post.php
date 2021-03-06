<?php
header('Access-Control-Allow-Origin: *');

use SDFT\Baskets;
use SDFT\Storage;
use SDFT\Token;
use SDFT\Attachments;
use SDFT\Attachments\Token as Attachments_Token;
use SDFT\Activities;
use SDFT\Notifications;
use SDFT\Baskets\Collaborators;
use SDFT\PusherNotification;

require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');

$method = $_SERVER['REQUEST_METHOD'];

$response=array('status'=>300);


//block if no token in param
if(!isset($_POST['token'])) exit;


//Params
$token=@htmlentities(htmlspecialchars($_POST['token']));
$basket_id=@htmlentities(htmlspecialchars($_POST['basket_id']));
$url=@htmlentities(htmlspecialchars($_POST['url']));



//Block if token is empty
if(empty($token)||empty($basket_id)||empty($url)) exit;


//Validate token
$token_class=new Token();
$__identity=$token_class->get_token($db,$token);


//IP address
$ip = $_SERVER['REMOTE_ADDR'];

function get_attachment_parent_details ($db, $id) {
	$result = [];
	$attachments = new Attachments();
	$parent = $attachments->details($db, $id);
	$id = isset($parent[0]->id) ? $parent[0]->id : null;

	if(is_null($id)) return $result;

	// check if there is another parent file
	$parentId = $parent[0]->original_copy_id;
	if(!is_null($parentId)) return get_attachment_parent_details ($db, $parentId);

	return $parent;

}

if($method=='POST'){
	if(isset($__identity->id)){

		//check current ip address if the same with identity IP
		if(!filter_var($ip,FILTER_VALIDATE_IP)==TRUE) exit;

		if(filter_var($ip,FILTER_VALIDATE_IP)!=$__identity->ip_address) exit;
		
		$storage=new Storage();
		$attachments=new Attachments();
		$activities=new Activities();
		$notifications=new Notifications();
		$attachments_token=new Attachments_Token();
		$recent_notification = array();


		$new_url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
		parse_str(parse_url($new_url, PHP_URL_QUERY),$file_token);

		//non empty value
		if(empty($file_token['token'])) return 0;


		//check token
		$token_details = $attachments_token->view_attachments($db,$file_token['token']);
		if(@isset($token_details[0]->attachments_id)){
			if($token_details[0]->status==1){
				return 0;
			}
		}

		

		//get parent attachment
		$parent=$attachments_token->details($db,$token_details[0]->id);
		$parent_details = $attachments_token->details($db,$token_details[0]->id);
		$parent = get_attachment_parent_details ($db, $parent_details[0]->id);

			/*--------------------------------
			| Prevent unauthorized access
			|--------------------------------*/
			//get basket information
			$collaborators=new Collaborators();

			$basket_collaborators=($collaborators->get_collaborators($db,$basket_id,0));


			$collaborators_array=array();
			//collaborators
			if(isset($basket_collaborators[0]->uid)){

					for ($i=0; $i <count($basket_collaborators) ; $i++) { 
						
						array_push($collaborators_array, $basket_collaborators[$i]->uid);

					}
				
			}
		

			#allow them to upload if they are collaborators
			if(in_array($__identity->uid,$collaborators_array)){
				//insert for public
				if($token_details[0]->visibility==0){
					$res = $attachments->create($db,$basket_id,'',$__identity->profile_id,$parent[0]->type,$parent[0]->original_filename,$parent[0]->filename,$parent[0]->size,'duplicate',$parent[0]->id);
				}else{
					//private
					$valid_emails = explode(',',$token_details[0]->email);
					$trimmed_email = [];
					for($x=0;$x<count($valid_emails);$x++){
						array_push($trimmed_email,html_entity_decode(trim($valid_emails[$x])));
					}
					//check username
					if(in_array($__identity->username, $trimmed_email)){
						$res = $attachments->create($db,$basket_id,'',$__identity->profile_id,$parent[0]->type,$parent[0]->original_filename,$parent[0]->filename,$parent[0]->size,'duplicate',$parent[0]->id);	
					}else{
						return 0;
					}
				}

				# successfull
				if($res) {
						//log to db
						$activities->log_activity($db,$__identity->profile_id,$basket_id,'Attached new file '.$parent[0]->original_filename);

						//send only if basket is already published 
						if($basket_collaborators[0]->status!='draft'){
	
							for ($i=0; $i <count($collaborators_array) ; $i++) { 
								
								//exclude self from notification
								#if($__identity->uid!=$collaborators_array[$i]){
									//log to database
									$notification_id = $notifications->notify($db,$__identity->uid,$collaborators_array[$i],$basket_id,'uploaded');
									// notify channel
									if(!count($recent_notification)) {
										$recent_notification = $notifications->view($db,$notification_id);
									}
									$notif = new PusherNotification ();
									$notif->send("private-{$collaborators_array[$i]}-basket-user",$recent_notification);
								}
	
							#}
						}
				}
				
			}else{

			}



			

		$response['status']=200;
		

		//returns attachment info
		if(@$res>0){
			$response['files']=@$res;
			//change to new ID befre sending
			$parent[0]->id = $res;
			$response['details']=$parent[0];
		}

	}	
}







//output in JSON format
echo json_encode($response);

?>