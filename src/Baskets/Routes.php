<?php
namespace SDFT\Baskets;

/**
* 
*/
class Routes
{
	
	function __construct(){}

	function get_routes($db,$id,$page=1){

		#get all collaborators for a certain basket,excluding the author of the basket
		$sql='SELECT route.*,account_profile.profile_name,account_profile.uid FROM route LEFT JOIN account_profile on account_profile.id = route.profile_id where basket_id=:basket_id ORDER BY route.date_created DESC';
		

		$sth=$db->prepare($sql);
		$sth->bindValue(':basket_id',$id);
		
		$sth->execute();


		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			$result[]=$row;
		}

		return $result;

	}


	function create($db,$profile_id,$id,$action='in'){

		$action=$action==='in'?0:1;
		try{
			$db->beginTransaction();
			#get all collaborators for a certain basket,excluding the author of the basket
			$sql='INSERT INTO route(basket_id,profile_id,status)values(:basket_id,:profile_id,:action)';
			

			$sth=$db->prepare($sql);
			$sth->bindValue(':basket_id',$id);
			$sth->bindValue(':profile_id',$profile_id);
			$sth->bindValue(':action',$action);

			$sth->execute();

			$last_insert_id=$db->lastInsertId();
			$db->commit();

			return $last_insert_id;

		}catch(Exception $e){
			$db->rollback();
		}

	}


}

?>