<?php
namespace SDFT;

/**
* 
*/
class AuthenticationOffice365
{
	
	function __construct(){}

	function account_exists_in_local($db,$username,$uid){
		$sql="SELECT * FROM account where username=:username and uid=:uid ORDER BY id DESC LIMIT 1";
		$sth=$db->prepare($sql);
		$sth->bindParam(':username',$username);
		$sth->bindParam(':uid',$uid);
		$sth->execute();
		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)) {
			$result[]=$row;
		}

		return $result;

	}

	function create_account($db,$username,$uid){
		$sql="INSERT INTO account(username,uid)values(:username,:uid)";
		$sth=$db->prepare($sql);
		$sth->bindParam(':username',$username);
		$sth->bindParam(':uid',$uid);
		$sth->execute();
		
		return $db->lastInsertId();
	}


	function create_local_profile($db,$uid,$full_name,$last_name,$first_name,$image,$department,$alias,$position,$date_modified,$email){
				
		$sql="INSERT INTO account_profile(profile_name,last_name,first_name,profile_image,department,department_alias,position,date_modified,profile_email,uid)values(:profile_name,:last_name,:first_name,:profile_image,:department,:department_alias,:position,:date_modified,:email,:uid)";

		$sth=$db->prepare($sql);
		$sth->bindParam(':profile_name',$full_name);
		$sth->bindParam(':last_name',$last_name);
		$sth->bindParam(':first_name',$first_name);
		$sth->bindParam(':profile_image',$image);
		$sth->bindParam(':department',$department);
		$sth->bindParam(':department_alias',$alias);
		$sth->bindParam(':position',$position);
		$sth->bindParam(':date_modified',$date_modified);
		$sth->bindParam(':email',$email);
		$sth->bindParam(':uid',$uid);
		$sth->execute();

		return $db->lastInsertId();

	}


	function create_account_session($db,$profile_id,$uuid,$ip_address,$user_agent,$token){
		$sql="INSERT INTO account_session(profile_id,uuid,ip_address,user_agent,token)values(:profile_id,:uuid,:ip_address,:user_agent,:token)";
		$sth=$db->prepare($sql);

		$sth->bindParam(':profile_id',$profile_id);
		$sth->bindParam(':uuid',$uuid);
		$sth->bindParam(':ip_address',$ip_address);
		$sth->bindParam(':user_agent',$user_agent);
		$sth->bindParam(':token',$token);

		$sth->execute();
		
		return $db->lastInsertId();
	}


	function get_local_profile($db,$uid){
				
		$sql="SELECT * FROM account_profile where uid=:uid ORDER BY id DESC LIMIT 1";
		$sth=$db->prepare($sql);
		$sth->bindParam(':uid',$uid);
		$sth->execute();
		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)) {
			$result[]=$row;
		}

		return $result;

	}

	function generate_token($salt,$random_number,$character){

		return sha1($character.'XXXXXX'.$salt.'XXXXXX'.$character);

	}


}

?>