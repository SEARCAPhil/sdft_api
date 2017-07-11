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

}