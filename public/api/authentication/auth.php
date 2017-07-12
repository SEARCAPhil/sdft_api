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

use SDFT\Authentication as Authentication;

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
$uuid=isset($_POST['uuid'])?htmlentities(htmlspecialchars($_POST['uuid'])):NULL; //for mobile only





/*----------------------------------------------------------------
| DATABASE CREDENTIALS
| FOR EXTERNAL Database
|
| **authentication must be done via OAuth in the near future**
|-----------------------------------------------------------------*/

$db_account=new PDO('mysql:host=127.0.0.1;dbname=rfp_db','root');
$db_account->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);







#authentication using username and password
#account credentials are saved in login_db_instance_1
function authenticate($db,$username,$password){

	$sql='SELECT login_db_instance_1.accounts.account_username as username,login_db_instance_1.accounts.id,login_db_instance_1.sdft_sys_privilege.priv,login_db_instance_1.account_profile.profile_image,login_db_instance_1.account_profile.profile_name,login_db_instance_1.account_profile.profile_name,login_db_instance_1.account_profile.position,login_db_instance_1.account_profile.first_name,login_db_instance_1.account_profile.last_name,login_db_instance_1.account_profile.date_modified,login_db_instance_1.department.dept_name as department,login_db_instance_1.department.dept_id,login_db_instance_1.department.dept_alias FROM login_db_instance_1.accounts left join login_db_instance_1.account_profile on login_db_instance_1.account_profile.uid=accounts.id left JOIN login_db_instance_1.department on login_db_instance_1.department.dept_id=account_profile.dept_id left join login_db_instance_1.sdft_sys_privilege on login_db_instance_1.sdft_sys_privilege.uid=login_db_instance_1.accounts.id where login_db_instance_1.accounts.account_username=:username and login_db_instance_1.accounts.account_password=:password';

	$sth=$db->prepare($sql);
	$sth->bindParam(':username',$username);
	$sth->bindParam(':password',$password);
	$sth->execute();

	$result=array();
	while($row=$sth->fetch(PDO::FETCH_OBJ)){
		$result=$row;
	}

	return array('data'=>$result);

}


#--------------------------------------------------------------------------------------
#AUTHENTICATION GOES HERE
#--------------------------------------------------------------------------------------

$authentication=new Authentication();


if(isset($_POST['username'])&&isset($_POST['password'])){
	$username=htmlentities(htmlspecialchars($_POST['username']));
	$password=sha1(htmlentities(htmlspecialchars($_POST['password'])));
	$hash=NULL;

	//authenticate in external database
	$authentication_result=authenticate($db_account,$username,$password);

	
	//check if authenticated
	if(count($authentication_result['data'])>0){

		#token
		$token=$authentication->generate_token($__salt,mt_srand($__salt_random),@$authentication_result['data']->username);
		$hash = password_hash($token, PASSWORD_BCRYPT);

		#profile
		$full_name=@htmlentities(htmlspecialchars($authentication_result['data']->full_name));
		$last_name=@htmlentities(htmlspecialchars($authentication_result['data']->last_name));
		$first_name=@htmlentities(htmlspecialchars($authentication_result['data']->first_name));
		$image=@htmlentities(htmlspecialchars($authentication_result['data']->image));
		$department=@htmlentities(htmlspecialchars($authentication_result['data']->department));
		$alias=@htmlentities(htmlspecialchars($authentication_result['data']->alias));
		$position=@htmlentities(htmlspecialchars($authentication_result['data']->position));
		$date_modified=@htmlentities(htmlspecialchars($authentication_result['data']->date_modified));



		//check from local database
		$account_local_id=($authentication->account_exists_in_local($db,$authentication_result['data']->username));
		$account_local_profile_id=NULL;


		#create account if not exists in local database
		if(count($account_local_id)<=0){
			$account_local_id=$authentication->create_account($db,$username,$authentication_result['data']->id);

			#if created local account successfully
			if($account_local_id>0){
				#create new profile
				$account_local_profile_id=$authentication->create_local_profile($db,$account_local_id,$full_name,$last_name,$first_name,$image,$department,$alias,$position,$date_modified);	
			}

		}else{


			$local_profile=$authentication->get_local_profile($db,$account_local_id[0]->id);

			//override array local id to int
			$account_local_id=$account_local_id[0]->id;

			#check if profile doesnt change
			#create a new
			if($local_profile[0]->date_modified==$date_modified){
				$account_local_profile_id=$local_profile[0]->id;
			}else{
				//create updated profile
				$account_local_profile_id=$authentication->create_local_profile($db,$account_local_id,$full_name,$last_name,$first_name,$image,$department,$alias,$position,$date_modified);
			}
		}
		

		//create session
		if($authentication->create_account_session($db,$account_local_id,$account_local_profile_id,$uuid,$ip_address,$user_agent,$hash)){

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


}

?>