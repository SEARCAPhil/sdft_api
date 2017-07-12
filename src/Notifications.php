<?php
namespace SDFT;

/**
* 
*/
class Notifications
{
	
	function __construct(){}

	function __added_as_collaborator_message($from,$basket_name){

		$message="added you to <b>{$basket_name}</b>";

		return $message;
	}


	function __uploaded_message($from,$basket_name){

		$message="added new attachment to <b>{$basket_name}</b>";

		return $message;
	}


	function get_notifications($db,$id,$page=1){

		define("LIMIT",30);

		$page=$page>1?$page:1;
		#set starting limit(page 1=10,page 2=20)
		$start_page=$page<2?0:( integer)($page-1)*LIMIT;

		#get all collaborators for a certain basket,excluding the author of the basket
		$sql='SELECT notifications.*,basket.basket_name as name,basket.id as basket_id FROM notifications LEFT JOIN basket on notifications.basket_id=basket.id where receiver_id=:id ORDER BY notifications.date_created DESC LIMIT :start_page,:LIMITS';
		$sql2='SELECT account_profile.* FROM account_profile where uid=:id ORDER BY date_modified DESC LIMIT 1';




		$sth=$db->prepare($sql);
		$sth->bindValue(':id',$id);
		$sth->bindValue(':LIMITS',LIMIT,\PDO::PARAM_INT);
		$sth->bindValue(':start_page',$start_page,\PDO::PARAM_INT);
		
		$sth->execute();


		$sth2=$db->prepare($sql2);

		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			
			

			$sth2->bindValue(':id',$row->sender_id);
			$sth2->execute();

			$row->sender=new \StdClass;

			$sender_name='';

			while($row2=$sth2->fetch(\PDO::FETCH_OBJ)){
				if(empty($row2->name)) $row2->name=$row2->first_name.' '.$row2->last_name;

				#assign to name
				$sender_name=$row2->name;

				#assign to sender object
				$row->sender=$row2;
			}

			/*---------------------------------
			| MESSAGE TEMPLATE
			|
			|---------------------------------*/

			if($row->action==='added_as_collaborator'){
				$row->message=self::__added_as_collaborator_message($sender_name,$row->name);
			}


			if($row->action==='uploaded'){
				$row->message=self::__uploaded_message($sender_name,$row->name);
			}

			$result[]=$row;
		}

		

		return $result;

	}

}