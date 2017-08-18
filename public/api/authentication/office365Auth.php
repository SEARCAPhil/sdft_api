<?php
/**
 * AUTHENTICATION API 
 * 
 * Authenticate user via ajax request and return a json reponse
 * Response status must contain code,token and profile of the user
 * 
 * STATUS CODE:
 * 200- OK
 * 500 - SERVER ERROR
 * 404 - NOT FOUND
 * 300 - ERROR
 */
header('Access-Control-Allow-Origin: *');


use SDFT\AuthenticationOffice365 as Authentication;
//https://github.com/serbanghita/Mobile-Detect/
// Include and instantiate the class.
require_once '../../../src/Mobile_Detect.php';
$detect = new Mobile_Detect;

require_once('../../../vendor/autoload.php');
require_once('../../../config/database.php');




#default configuration	
$method=$_SERVER['REQUEST_METHOD'];
$response=array('status'=>300);


#token default
$__salt='searca_sdft_application';
$__salt_random=5;



#machine details
$ip_address=@filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
$user_agent=htmlentities(htmlspecialchars($_SERVER['HTTP_USER_AGENT']));
$device_uuid=isset($_POST['uuid'])?htmlentities(htmlspecialchars($_POST['uuid'])):NULL; //for mobile only


#--------------------------------------------------------------------------------------
#AUTHENTICATION GOES HERE
#--------------------------------------------------------------------------------------

$authentication=new Authentication();



if(isset($_POST['id'])&&isset($_POST['email'])&&isset($_POST['token'])){

	$uuid=isset($_POST['id'])?htmlentities(htmlspecialchars($_POST['id'])):NULL; 
	$username=isset($_POST['email'])?htmlentities(htmlspecialchars($_POST['email'])):NULL; 
	$token=@htmlentities(htmlspecialchars($_POST['token']));


	//hash
	$token=$authentication->generate_token($__salt,mt_srand($__salt_random),@$username);
	$hash = password_hash($token, PASSWORD_BCRYPT);

	//profile information

	$full_name=@htmlentities(htmlspecialchars($_POST['full_name']));
	$last_name=@htmlentities(htmlspecialchars($_POST['surname']));
	$first_name=@htmlentities(htmlspecialchars($_POST['first_name']));
	$image=@htmlentities(htmlspecialchars($_POST['image']));
	$department=@htmlentities(htmlspecialchars($_POST['department']));
	$office=@htmlentities(htmlspecialchars($_POST['office']));
	$position=@htmlentities(htmlspecialchars($_POST['position']));
	

	//generate department alias
	$department_array=explode(' ',$department);

	$alias='';

	for($x=0;$x<count($department_array);$x++){
		$alias.= @strtoupper($department_array[$x][0]);	
	}





	$account_local_id=($authentication->account_exists_in_local($db,$username,$uuid));
	$account_local_profile_id=NULL;

	#create account if not exists in local database
	if(count($account_local_id)<=0){
		$account_local_id=$authentication->create_account($db,$username,$uuid);

		#if created local account successfully
			if($account_local_id>0){
				#create new profile
				$account_local_profile_id=$authentication->create_local_profile($db,$account_local_id,$full_name,$last_name,$first_name,$image,$department,$alias,$position,$office,$username);	
			}

	}else{


			$local_profile=$authentication->get_local_profile($db,$account_local_id[0]->id);

			//get local IDs
			$account_local_id=$account_local_id[0]->id;
			$account_local_profile_id=$local_profile[0]->id;


			if($local_profile[0]->profile_name!=$full_name||$local_profile[0]->department!=$department||$local_profile[0]->position!=$position||$local_profile[0]->profile_image!=$image){

				#create new profile
				$account_local_profile_id=$authentication->create_local_profile($db,$account_local_id,$full_name,$last_name,$first_name,$image,$department,$alias,$position,$office,$username);
			}
			
		}


	/*--------------------------------------------------
	| SESSION
	| This will create a new session for this account
	|---------------------------------------------------*/

	if($authentication->create_account_session($db,$account_local_profile_id,$device_uuid,$ip_address,$user_agent,$hash)){

		$response['status']=200;
		$response['token']=$hash;
		$response['details']=array();
		$response['details']['full_name']=$full_name;
		$response['details']['last_name']=$last_name;
		$response['details']['first_name']=$first_name;
		$response['details']['image']=$image;
		$response['details']['department']=$department;
		$response['details']['alias']=$alias;
		$response['details']['position']=$position;
		$response['details']['uid']=$account_local_id;
	}

}



	//return response
	echo json_encode($response);







?>