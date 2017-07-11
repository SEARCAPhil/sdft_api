<?php
namespace SDFT;

/**
* 
*/
class Activities
{
	
	function __construct(){}

	function log_activity($db,$author_id,$basket_id,$message){
		#start transaction
		try{
			$basket_id=htmlentities(htmlspecialchars($basket_id));
			$author_id=htmlentities(htmlspecialchars($author_id));
			$message=htmlentities(htmlspecialchars($message));

			$db->beginTransaction();
			
			$sql='INSERT INTO basket_activities(basket_id,profile_id,logs) values (:basket_id,:profile_id,:logs)';


			$statement=$db->prepare($sql);

			$statement->bindParam(':basket_id',$basket_id);
			$statement->bindParam(':profile_id',$author_id);
			$statement->bindParam(':logs',$message);

			$statement->execute();

			$lastId=(integer)$db->lastInsertId();
			$db->commit();
			
			return $lastId;

		}catch(PDOException $e){echo $e->getMessage();$db->rollback(); echo $e->getMessage();}
	}

	function get_activities($db,$id,$page=1){

		#get all collaborators for a certain basket,excluding the author of the basket
		$sql='SELECT basket_activities.*,profile_name as name,profile_image as image,department,department_alias as alias,last_name,first_name FROM basket_activities LEFT JOIN account_profile on account_profile.id=basket_activities.profile_id where basket_id=:basket_id ORDER BY date_created DESC';
		

		$sth=$db->prepare($sql);
		$sth->bindValue(':basket_id',$id);
		
		$sth->execute();


		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			if(empty($row->name)) $row->name=$row->first_name.' '.$row->last_name;
			$result[]=$row;
		}

		

		return $result;

	}

}