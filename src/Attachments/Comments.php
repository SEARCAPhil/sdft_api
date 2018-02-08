<?php
namespace SDFT\Attachments;

/**
* 
*/
class Comments
{
	
	function __construct(){}

	function create($db,$id,$attachment_id,$comment){
		#start transaction
		try{
				
				$db->beginTransaction();
	
				$attach_sql='INSERT INTO attachments_comment(attachment_id,profile_id,comment) values(:attachment_id,:profile_id,:comment)';

		
				$attach_statement=$db->prepare($attach_sql);

				$attach_statement->bindParam(':profile_id',$id);
				$attach_statement->bindParam(':attachment_id',$attachment_id);
				$attach_statement->bindParam(':comment',$comment);
	
				$attach_statement->execute();

				$lastId=(integer)$db->lastInsertId();
				$db->commit();
				
				return $lastId;

		}catch(PDOException $e){echo $e->getMessage();$db->rollback(); echo $e->getMessage();}
	}



	function get_comments($db,$id){

		#get all collaborators for a certain basket,excluding the author of the basket
		$sql='SELECT attachments_comment.*,account_profile.profile_name , account_profile.uid from attachments_comment LEFT JOIN account_profile on account_profile.id = attachments_comment.profile_id where attachment_id=:id and status!=1';
		$sth=$db->prepare($sql);
		$sth->bindValue(':id',$id);
	
		$sth->execute();

		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			$result[]=$row;
		}

		return $result;

	}

	function details($db,$id){

		#get all collaborators for a certain basket,excluding the author of the basket
		$sql='SELECT attachments_comment.*,attachments.basket_id FROM attachments_comment LEFT JOIN attachments on attachments.id = attachments_comment.attachment_id where attachments_comment.id=:id';
		$sth=$db->prepare($sql);
		$sth->bindValue(':id',$id);
	
		$sth->execute();

		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			$result[]=$row;
		}

		return $result;

	}

	function remove($db,$id){
		return $this->update_status($db,$id,1);
	}

	function update_status($db,$id,$status){
		$sql="UPDATE attachments_comment set status=:status where id=:id";
		$sth=$db->prepare($sql);
		$sth->bindParam(':status',$status);
		$sth->bindParam(':id',$id);

		$sth->execute();

		return $sth->rowCount();

	}



}

?>