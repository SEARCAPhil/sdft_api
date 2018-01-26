<?php
namespace SDFT;

/**
* 
*/
class Storage
{
	
	function __construct(){}


	function get_list_personal($db,$id,$page=1){

		define("LIMIT",30);

		$page=$page>1?$page:1;
		#set starting limit(page 1=10,page 2=20)
		$start_page=$page<2?0:( integer)($page-1)*LIMIT;


		$sql='SELECT attachments.*,basket.basket_name as name,account_profile.uid,account_profile.profile_name FROM attachments LEFT JOIN basket on attachments.basket_id=basket.id LEFT JOIN account_profile on account_profile.id=basket.profile_id where basket.is_deleted=0 and account_profile.uid=:uid and attachments.copy="original" ORDER BY attachments.date_modified DESC LIMIT :start_page,:LIMITS';

		$sth=$db->prepare($sql);

		$sth->bindValue(':uid',$id);

		

		$sth->bindValue(':LIMITS',LIMIT,\PDO::PARAM_INT);

		$sth->bindValue(':start_page',$start_page,\PDO::PARAM_INT);



		$sth->execute();


		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){

			$result[]=$row;
		}


		return $result;


	}


	function get_list_shared($db,$id,$page=1){

		define("LIMIT",30);

		$page=$page>1?$page:1;
		#set starting limit(page 1=10,page 2=20)
		$start_page=$page<2?0:( integer)($page-1)*LIMIT;


		$sql='SELECT attachments.*,account_profile.uid,basket.basket_name as name FROM attachments LEFT JOIN basket_collaborators on basket_collaborators.basket_id=attachments.basket_id LEFT JOIN account_profile on account_profile.id=basket_collaborators.profile_id LEFT JOIN basket on basket.id=basket_collaborators.basket_id WHERE account_profile.uid=:uid and attachments.profile_id!=account_profile.id  ORDER BY attachments.date_modified DESC LIMIT :start_page,:LIMITS';

		$sth=$db->prepare($sql);

		$sth->bindValue(':uid',$id);

		

		$sth->bindValue(':LIMITS',LIMIT,\PDO::PARAM_INT);

		$sth->bindValue(':start_page',$start_page,\PDO::PARAM_INT);



		$sth->execute();


		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){

			$result[]=$row;
		}


		return $result;


	}


	function search($db,$id,$param,$page=1){

		define("LIMIT",30);

		$page=$page>1?$page:1;
		#set starting limit(page 1=10,page 2=20)
		$start_page=$page<2?0:( integer)($page-1)*LIMIT;

		$params='%'.$param.'%';

		$sql='SELECT attachments.*,account_profile.uid,basket.basket_name as name FROM attachments LEFT JOIN basket_collaborators on basket_collaborators.basket_id=attachments.basket_id LEFT JOIN account_profile on account_profile.id=basket_collaborators.profile_id LEFT JOIN basket on basket.id=basket_collaborators.basket_id WHERE account_profile.uid=:uid and attachments.copy="original" and attachments.original_filename LIKE :param ORDER BY attachments.date_modified DESC LIMIT :start_page,:LIMITS';

		$sth=$db->prepare($sql);

		$sth->bindValue(':uid',$id);
		$sth->bindValue(':param',$params);

		

		$sth->bindValue(':LIMITS',LIMIT,\PDO::PARAM_INT);

		$sth->bindValue(':start_page',$start_page,\PDO::PARAM_INT);



		$sth->execute();


		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){

			$result[]=$row;
		}


		return $result;


	}



}

?>