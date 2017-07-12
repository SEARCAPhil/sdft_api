<?php
namespace SDFT\Baskets;

/**
* 
*/
class Collaborators
{
	
	function __construct(){}

	function create($db,$basket_id,$id){

		try{

			$sql2='INSERT INTO basket_collaborators(basket_id,profile_id)values(:basket_id,:profile_id)';
			$sth2=$db->prepare($sql2);
			$sth2->bindValue(':basket_id',$basket_id);
			$sth2->bindValue(':profile_id',$id);

			$sth2->execute();
			$last_insert_id=$db->lastInsertId();
			return $last_insert_id;

		}catch(Exception $e){
			
		}

	}


	function remove($db,$id){

		try{

			$sql2='DELETE FROM basket_collaborators where id=:id';
			$sth2=$db->prepare($sql2);
			$sth2->bindValue(':id',$id);

			$sth2->execute();
			$last_insert_id=$sth2->rowCount();
			return $last_insert_id;

		}catch(Exception $e){
			
		}

	}

	function get_collaborators($db,$id,$author_id,$exclude_author=true){

		#get all collaborators for a certain basket,excluding the author of the basket
		$sql='SELECT profile_name as name,first_name,last_name,department_alias as alias,department,position,profile_image as image,basket_collaborators.profile_id,basket.basket_name,basket.status,basket_collaborators.id as collaborator_id,account_profile.uid,basket_id from basket_collaborators LEFT JOIN account_profile on basket_collaborators.profile_id=account_profile.id LEFT JOIN basket on basket.id=basket_collaborators.basket_id where basket_id=:id';
		

		$sth=$db->prepare($sql);
		$sth->bindValue(':id',$id);
		
		$sth->execute();


		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			if(empty($row->name)) $row->name=$row->first_name.' '.$row->last_name;
			

			#prevent sending own profile info
			if($exclude_author){
				if($row->uid!=$author_id){
					$result[]=$row;
				}
			}
			
		}

		return $result;
	}


}

?>