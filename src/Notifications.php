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

	function __changed_category__message($from,$basket_name,$subject){

		$message="{$subject} in basket <u><b>{$basket_name}</b></u>";

		return $message;
	}


	function __changed_description__message($from,$basket_name){

		$message="changed description of <u><b>{$basket_name}</b></u>";

		return $message;
	}

	function __published_message($from,$basket_name){

		$message="published <u><b>{$basket_name}</b></u>";

		return $message;
	}


	function __closed_message($from,$basket_name){

		$message="Closed <u><b>{$basket_name}</b></u>";

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


			if($row->action==='file_category'){ 
				$row->message=self::__changed_category__message($sender_name,$row->name,$row->subject);
			}


			if($row->action==='changed_description'){ 
				$row->message=self::__changed_description__message($sender_name,$row->name);
			}


			if($row->action==='published'){ 
				$row->message=self::__published_message($sender_name,$row->name);
			}

			if($row->action==='closed'){ 
				$row->message=self::__closed_message($sender_name,$row->name);
			}


			$result[]=$row;
		}

		

		return $result;

	}


	function notify($db,$sender_uid,$receiver_uid,$basket_id,$action,$subject=''){
		#start transaction
		try{
			$basket_id=htmlentities(htmlspecialchars($basket_id));
			$sender_id=htmlentities(htmlspecialchars($sender_uid));
			$receiver_id=htmlentities(htmlspecialchars($receiver_uid));
			$action=htmlentities(htmlspecialchars($action));

			$db->beginTransaction();
			
			$sql='INSERT INTO notifications(basket_id,receiver_id,sender_id,action,subject) values (:basket_id,:receiver_id,:sender_id,:action,:subject)';


			$statement=$db->prepare($sql);

			$statement->bindParam(':basket_id',$basket_id);
			$statement->bindParam(':receiver_id',$receiver_id);
			$statement->bindParam(':sender_id',$sender_id);
			$statement->bindParam(':action',$action);
			$statement->bindParam(':subject',$subject);

			$statement->execute();

			$lastId=(integer)$db->lastInsertId();
			$db->commit();
			
			return $lastId;

		}catch(PDOException $e){echo $e->getMessage();$db->rollback(); echo $e->getMessage();}
	}


	function set_read($db,$id){
		$sql='UPDATE notifications set flag="read" where id=:id';
		$sth=$db->prepare($sql);
		$sth->bindValue(':id',$id);
		$sth->execute();

		return $sth->rowCount();
	}


}