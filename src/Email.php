<?php
namespace SDFT;

/**
* 
*/
class Email
{
	
	function __construct(){}

	function search($db,$email){
		$mail = '%'.$email.'%';
		$result = [];
		$sql="SELECT account.username, account_profile.profile_name from account LEFT JOIN account_profile on account.id = account_profile.uid where account.username LIKE :email and account.username > '' ORDER BY account.username ASC LIMIT 10";
		$sth=$db->prepare($sql);
		$sth->bindParam(':email',$mail,\PDO::PARAM_STR);
		$sth->execute();
		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			$result[]=$row;
		}
		return $result;
	}
}