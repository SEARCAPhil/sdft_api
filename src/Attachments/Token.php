<?php
namespace SDFT\Attachments;

/**
* 
*/
class Token
{
	
	function __construct(){}

	function create_public($db,$id,$token){
		#start transaction
		try{
				
				$db->beginTransaction();
	
				$attach_sql='INSERT INTO attachments_token(attachments_id,token) values(:attachments_id,:token)';

		
				$attach_statement=$db->prepare($attach_sql);

				$attach_statement->bindParam(':attachments_id',$id);
				$attach_statement->bindParam(':token',$token);
	
				$attach_statement->execute();

				$lastId=(integer)$db->lastInsertId();
				$db->commit();
				
				return $lastId;

		}catch(PDOException $e){echo $e->getMessage();$db->rollback(); echo $e->getMessage();}
	}


	function create_private($db,$id,$token,$email){
		#start transaction
		try{
				
				$db->beginTransaction();
	
				$attach_sql='INSERT INTO attachments_token(attachments_id,token,email,visibility) values(:attachments_id,:token,:email,1)';

		
				$attach_statement=$db->prepare($attach_sql);

				$attach_statement->bindParam(':attachments_id',$id);
				$attach_statement->bindParam(':token',$token);
				$attach_statement->bindParam(':email',$email);

	
				$attach_statement->execute();

				$lastId=(integer)$db->lastInsertId();
				$db->commit();
				
				return $lastId;

		}catch(PDOException $e){echo $e->getMessage();$db->rollback(); echo $e->getMessage();}
	}


	function get_tokens($db,$id){

		#get all collaborators for a certain basket,excluding the author of the basket
		$sql='SELECT * from attachments_token where attachments_id=:id and status!=1';
		$sth=$db->prepare($sql);
		$sth->bindValue(':id',$id);
		
		$sth->execute();


		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			$result[]=$row;
		}

		return $result;

	}


	function get_tokens_email($db,$id){

		#get all collaborators for a certain basket,excluding the author of the basket
		$sql='SELECT * from attachments_token where id=:id and status!=1';
		$sth=$db->prepare($sql);
		$sth->bindValue(':id',$id);
		
		$sth->execute();


		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			$result[]=$row;
		}

		return $result;

	}


	function update_tokens_email($db,$id,$email){

		#get all collaborators for a certain basket,excluding the author of the basket
		$sql='UPDATE attachments_token set email=:email where id=:id and status!=1';
		$sth=$db->prepare($sql);
		$sth->bindValue(':id',$id);
		$sth->bindValue(':email',$email);
		
		$sth->execute();

		return $sth->rowCount();

	}

	function view_attachments($db,$token){

		#get all collaborators for a certain basket,excluding the author of the basket
		$sql='SELECT * from attachments_token where token=:token and status!=1';
		$sth=$db->prepare($sql);
		$sth->bindValue(':token',$token);
		
		$sth->execute();


		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			$result[]=$row;
		}

		return $result;

	}


	function details($db,$id){
		$sql="SELECT visibility , attachments.* from attachments_token LEFT JOIN attachments on attachments_token.attachments_id = attachments.id where attachments_token.id=:id";
		$sth=$db->prepare($sql);
		$sth->bindParam(':id',$id);
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
		$sql="UPDATE attachments_token set status=:status where id=:id";
		$sth=$db->prepare($sql);
		$sth->bindParam(':status',$status);
		$sth->bindParam(':id',$id);

		$sth->execute();

		return $sth->rowCount();

	}



}

?>