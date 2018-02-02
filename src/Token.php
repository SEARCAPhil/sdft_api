<?php
namespace SDFT;

/**
* 
*/
class Token
{
	
	function __construct(){}

	function get_token($db,$token){
		$sql="SELECT account_session.*,account_profile.uid,account.username FROM account_session LEFT JOIN account_profile on account_session.profile_id=account_profile.id LEFT JOIN account on account_profile.uid = account.id where token=:token ORDER BY id DESC LIMIT 1";
		$sth=$db->prepare($sql);
		$sth->bindParam(':token',$token);
		$sth->execute();
		$row=$sth->fetch(\PDO::FETCH_OBJ);
	
		return $row;
	}
}