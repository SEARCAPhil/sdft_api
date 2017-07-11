<?php
namespace SDFT;

/**
* 
*/
class Contacts
{
	
	function __construct(){}
	function get_contacts_by_department($db,$page=1){

		try{
			define("LIMIT",20);

			$page=$page>1?$page:1;
			#set starting limit(page 1=10,page 2=20)
			$start_page=$page<2?0:( integer)($page-1)*LIMIT;

			$sql='SELECT * FROM (SELECT account_profile.id, account.username,account_profile.profile_name,account_profile.last_name,account_profile.first_name,account_profile.department,account_profile.department_alias as alias, account_profile.position, account_profile.profile_image as image, account_profile.profile_email as email FROM account LEFT JOIN account_profile on account_profile.uid=account.id ORDER BY account_profile.date_modified DESC) sc GROUP BY username  ORDER BY sc.first_name LIMIT :start_page,:LIMITS';
			$sth=$db->prepare($sql);
			$sth->bindValue(':LIMITS',LIMIT,\PDO::PARAM_INT);
			$sth->bindValue(':start_page',$start_page,\PDO::PARAM_INT);
			$sth->execute();

			$result=array();

			while ($row=$sth->fetch(\PDO::FETCH_OBJ)) {

				$row->name=strlen($row->profile_name)<=1?$row->first_name.' '.$row->last_name:$row->profile_name;
				$result[]=$row;
			}

			return $result;
		}catch(Exception $e){}
	}


	function search($page=1,$param,$db){

		try{

			$sql='SELECT * FROM (SELECT account_profile.id, account.username,account_profile.profile_name,account_profile.last_name,account_profile.first_name,account_profile.department,account_profile.department_alias as alias, account_profile.position, account_profile.profile_image as image, account_profile.profile_email as email FROM account LEFT JOIN account_profile on account_profile.uid=account.id ORDER BY account_profile.date_modified DESC) sc where sc.last_name LIKE :param OR sc.first_name LIKE :param OR sc.profile_name LIKE :param GROUP BY username  ORDER BY sc.first_name';

			$sth=$db->prepare($sql);

			$parameter=$param.'%';



			$sth->bindParam(':param',$parameter,\PDO::PARAM_STR);

			$sth->execute();
			$result=array();

			while ($row=$sth->fetch(\PDO::FETCH_OBJ)) {

				$row->name=strlen($row->profile_name)<=1?$row->first_name.' '.$row->last_name:$row->profile_name;
				$result[]=$row;
			}


			return $result;
		}catch(Exception $e){}
	}
}

?>