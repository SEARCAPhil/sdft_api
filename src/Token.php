<?php
namespace SDFT;

/**
* 
*/
class Token
{
	
	function __construct(){}

	function get_token($db,$token){
		$sql="SELECT * FROM account_session where token=:token ORDER BY id DESC LIMIT 1";
		$sth=$db->prepare($sql);
		$sth->bindParam(':token',$token);
		$sth->execute();
		$row=$sth->fetch(\PDO::FETCH_OBJ);
	
		return $row;
	}
}