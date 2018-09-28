<?php
namespace SDFT\Baskets;

/**
* 
*/
class Notes
{
	
	function __construct(){}

	function get_notes($db,$id,$page=1){

		#get all collaborators for a certain basket,excluding the author of the basket
		$sql='SELECT notes.*,profile_name as name,profile_image as image,department,department_alias as alias,last_name,first_name,uid FROM notes LEFT JOIN account_profile on account_profile.id=notes.profile_id where basket_id=:basket_id and is_deleted=0 ORDER BY notes.date_created ASC';
		

		$sth=$db->prepare($sql);
		$sth->bindValue(':basket_id',$id);
		
		$sth->execute();


		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			if(empty($row->name)) $row->name=$row->first_name.' '.$row->last_name;
			// parse date
			if(@$row->date_modified) {
				$time = explode (' ', $row->date_modified);
				$time = date("g:i a", strtotime($time[1]));
				$row->date_modified = date( "M. d, Y", strtotime($row->date_modified));
				$row->date_modified.= " {$time}";
			}

			$result[]=$row;
		}

		return $result;

	}

	function create($db,$profile_id,$id,$notes){

		try{
			$db->beginTransaction();
			#get all collaborators for a certain basket,excluding the author of the basket
			$sql='INSERT INTO notes(basket_id,profile_id,notes)values(:basket_id,:profile_id,:notes)';
			

			$sth=$db->prepare($sql);
			$sth->bindValue(':basket_id',$id);
			$sth->bindValue(':profile_id',$profile_id);
			$sth->bindValue(':notes',$notes);

			$sth->execute();

			$last_insert_id=$db->lastInsertId();
			$db->commit();

			return $last_insert_id;

		}catch(Exception $e){
			$db->rollback();
		}

	}

	function remove($db,$profile_id,$id){
		try{
			$sql2="UPDATE notes set is_deleted=1 where id=:id";
			$sth3=$db->prepare($sql2);
			$sth3->bindParam(':id',$id);
			$sth3->execute();

			return $sth3->rowCount();

		}catch(Exception $e){
			return array();
		}
	}

	function update($db,$id,$notes){
		try{

			$sql2="UPDATE notes set notes=:notes where id=:id";
			$sth3=$db->prepare($sql2);
			$sth3->bindParam(':id',$id);
			$sth3->bindParam(':notes',$notes);
			$sth3->execute();

			return $sth3->rowCount();

		}catch(Exception $e){
			return array();
		}

	}

}

?>