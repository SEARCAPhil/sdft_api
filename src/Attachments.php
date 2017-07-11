<?php
namespace SDFT;

/**
* 
*/
class Attachments
{
	
	function __construct(){}

	function create($db,$basket_id,$category_id,$author_id,$type,$old_filename,$new_filename,$size=0){
		#start transaction
		try{
				$basket_id=htmlentities(htmlspecialchars($basket_id));
				$filename=htmlentities(htmlspecialchars($new_filename));
				$category_id=htmlentities(htmlspecialchars($category_id));
				$profile_id=htmlentities(htmlspecialchars($author_id));
				$type=htmlentities(htmlspecialchars($type));
				$file_size=htmlentities(htmlspecialchars($size));

				

				$db->beginTransaction();
				$utf8_filename=utf8_encode($old_filename);
				$attach_sql='INSERT INTO attachments(filename,original_filename,size,type,basket_id,profile_id,category_id) values (:filename,:original_filename,:size,:type,:basket_id,:profile_id,:category_id)';

		
				$attach_statement=$db->prepare($attach_sql);

				$attach_statement->bindParam(':filename',$filename);
				$attach_statement->bindParam(':original_filename',$utf8_filename);
				$attach_statement->bindParam(':size',$file_size);
				$attach_statement->bindParam(':type',$type);
				$attach_statement->bindParam(':basket_id',$basket_id);
				$attach_statement->bindParam(':profile_id',$profile_id);
				$attach_statement->bindParam(':category_id',$category_id);

				$attach_statement->execute();

				$lastId=(integer)$db->lastInsertId();
				$db->commit();
				
				return $lastId;

		}catch(PDOException $e){echo $e->getMessage();$db->rollback(); echo $e->getMessage();}
	}

	function remove($db,$id){
		try{

			$sql2="UPDATE attachments set is_deleted=1 where id=:id";
			$sth3=$db->prepare($sql2);
			$sth3->bindParam(':id',$id);
			$sth3->execute();

			return $sth3->rowCount();

		}catch(Exception $e){
			return array();
		}

	}


	function get_parent_basket($db,$id){
		$sql="SELECT * from attachments where id=:id";
		$sth=$db->prepare($sql);
		$sth->bindParam(':id',$id);
		$sth->execute();
		$result=array();
		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			$result[]=$row;
		}
		return $result;
	}


	function get_attachment_category($db,$id){
		$sql="SELECT category from basket_category where id=:id LIMIT 1";
		$sth=$db->prepare($sql);
		$sth->bindParam(':id',$id);
		$sth->execute();

		$result=$sth->fetch(\PDO::FETCH_OBJ);
		return @$result->category;

	}


	function update_attachment_status($db,$id,$status='closed'){
		$sql="UPDATE attachments set status=:status where id=:id";
		$sth=$db->prepare($sql);
		$sth->bindParam(':status',$status);
		$sth->bindParam(':id',$id);

		$sth->execute();

		return $sth->rowCount();

	}

	function update_attachment_category($db,$id,$category_id){
		$sql="UPDATE attachments set category_id=:status where id=:id";
		$sth=$db->prepare($sql);
		$sth->bindParam(':status',$category_id);
		$sth->bindParam(':id',$id);

		$sth->execute();

		return $sth->rowCount();

	}


}

?>