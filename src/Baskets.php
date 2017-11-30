<?php
namespace SDFT;

/**
* 
*/
class Baskets
{
	
	function __construct(){}


	function get_list($db,$id,$status='open',$page=1){

		define("LIMIT",20);

		$page=$page>1?$page:1;
		#set starting limit(page 1=10,page 2=20)
		$start_page=$page<2?0:( integer)($page-1)*LIMIT;



		if($status=='open'||$status=='closed'){
			$sql='SELECT basket.id,basket.description,basket_category.category,basket.default_route,basket.current_route,basket.status,basket.date_created,basket.date_modified,basket_name as name,account_profile.uid as collaborators_id,account_profile.id as collaborators_profile_id,account_profile.profile_name,account_profile.last_name,account_profile.first_name,account_profile.department,account_profile.position,account_profile.department_alias as alias,account_profile.profile_image as image,basket.profile_id as author_profile_id FROM basket_collaborators LEFT JOIN account_profile on account_profile.id=basket_collaborators.profile_id LEFT JOIN basket on basket_collaborators.basket_id=basket.id LEFT JOIN basket_category on basket.category=basket_category.id where account_profile.uid=:uid and basket.status=:status and basket.is_deleted=0 ORDER BY date_modified DESC LIMIT :start_page,:LIMITS';	
		}else{
			$sql="SELECT basket.id,basket.description,basket_category.category,basket.default_route,basket.current_route,basket.status,basket.date_created,basket.date_modified,basket_name as name,account_profile.uid as collaborators_id,account_profile.id as collaborators_profile_id,account_profile.profile_name,account_profile.last_name,account_profile.first_name,account_profile.department,account_profile.position,account_profile.department_alias as alias,account_profile.profile_image as image,basket.profile_id as author_profile_id FROM basket_collaborators LEFT JOIN account_profile on account_profile.id=basket_collaborators.profile_id LEFT JOIN basket on basket_collaborators.basket_id=basket.id LEFT JOIN basket_category on basket.category=basket_category.id where account_profile.uid=:uid and basket.status!='draft' and basket.is_deleted=0 ORDER BY date_modified DESC LIMIT :start_page,:LIMITS";		
		}


		//if draft
		if($status=='draft'){
			$sql="SELECT basket.id,basket.description,basket_category.category,basket.default_route,basket.current_route,basket.status,basket.date_created,basket.date_modified,basket_name as name,account_profile.uid as collaborators_id,account_profile.id as collaborators_profile_id,account_profile.profile_name,account_profile.last_name,account_profile.first_name,account_profile.department,account_profile.position,account_profile.department_alias as alias,account_profile.profile_image as image,basket.profile_id as author_profile_id FROM basket_collaborators LEFT JOIN account_profile on account_profile.id=basket_collaborators.profile_id LEFT JOIN basket on basket_collaborators.basket_id=basket.id LEFT JOIN basket_category on basket.category=basket_category.id where account_profile.uid=:uid and basket.status='draft' and basket.is_deleted=0 ORDER BY date_modified DESC LIMIT :start_page,:LIMITS";
		}



		$sql1='SELECT profile_name as name, department_alias as alias,department,position,uid,profile_image as image,first_name,last_name FROM account_profile where id=:id ORDER BY id DESC LIMIT 1';

		$sth=$db->prepare($sql);

		$sth->bindValue(':uid',$id);

		//if status is present
		if($status=='open'||$status=='closed'){
			$sth->bindValue(':status',$status);
		}

		$sth->bindValue(':LIMITS',LIMIT,\PDO::PARAM_INT);

		$sth->bindValue(':start_page',$start_page,\PDO::PARAM_INT);



		$sth->execute();

		$sth2=$db->prepare($sql1);

		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			if(strlen($row->description)>200) $row->description=substr($row->description, 0,200).'...';

			$basket=$row;

			$basket->author=array();

			$sth2->bindValue(':id',$row->author_profile_id);

			$sth2->execute();


			while($row2=$sth2->fetch(\PDO::FETCH_ASSOC)){

				#override name
				if(empty($row2['name'])) $row2['name']=$row2['first_name'].' '.$row2['last_name'];

				$basket->author=($row2);
			}

			$result[]=$basket;
		}


		return $result;


	}


	function get_details($db,$id){

		$sql='SELECT *,basket_name as name from basket where id=:id';
		$sql1='SELECT profile_name as name,first_name,last_name,department_alias as alias,department,position,uid,profile_image as image FROM account_profile where id=:id ORDER BY id DESC LIMIT 1';

		$sth=$db->prepare($sql);
		$sth->bindValue(':id',$id);
		$sth->execute();

		$sth2=$db->prepare($sql1);

		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			
			$basket=$row;
			$basket->author=array();

			$sth2->bindValue(':id',$row->profile_id);
			$sth2->execute();


			while($row2=$sth2->fetch(\PDO::FETCH_ASSOC)){
				#override name
				if(empty($row2['name'])) $row2['name']=$row2['first_name'].' '.$row2['last_name'];
				$basket->author=($row2);
			}

			$basket->attachments=self::get_attachments($db,$id);

			$result[]=$basket;
		}

	

		return $result;

	}




	function get_attachments($db,$id){

		$sql='SELECT attachments.id,filename as unique_name,original_filename as name,date_created,status,account_profile.uid,profile_id,basket_id,attachments.type,basket_category.category,attachments.size,profile_name,first_name,last_name,department_alias as alias,department,position,profile_image as image, attachments.date_modified from attachments LEFT JOIN account_profile on account_profile.id=attachments.profile_id LEFT JOIN basket_category on category_id=basket_category.id where basket_id=:id and attachments.is_deleted=0 ORDER BY attachments.date_modified DESC';

		$sth=$db->prepare($sql);

		$sth->bindValue(':id',$id);

		$sth->execute();

		$results=array();

		//set directory url
		$file_dir='127.0.0.1/system/files/';

		$image_dir='127.0.0.1/system/files/images/';

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){

			//saved to author array
			$author=array();
			$author['id']=$row->uid;

			#override name
			if(empty($row->profile_name)) $row->profile_name=$row->first_name.' '.$row->last_name;
			$author['name']=$row->profile_name;
			$author['position']=$row->position;
			$author['department']=$row->department;
			$author['alias']=$row->alias;
			//$author['image']=$image_dir.''.$row->image;
			$author['image']='http://192.168.80.53/SDFT_CORDOVA_APP/www/assets/images/user.png';

			$file=array();
			$file['id']=$row->id;
			$file['name']=$row->name;
			$file['category']=$row->category;
			$file['unique_name']=$row->unique_name;
			$file['type']=$row->type;
			$file['size']=$row->size;
			$file['status']=$row->status;
			$file['url']=$file_dir.''.$row->unique_name;
			$file['date_modified']=$row->date_modified;

			$data=array('files'=>$file,'author'=>$author);
			$results[]=$data;
		}

		return $results;

	}



	function create($db,$profile_id,$name,$description,$category,$keywords){

		try{
			$db->beginTransaction();
			#get all collaborators for a certain basket,excluding the author of the basket
			$sql='INSERT INTO basket(basket_name,profile_id,description,category,keywords)values(:basket_name,:profile_id,:description,:category,:keywords)';
			

			$sth=$db->prepare($sql);
			$sth->bindValue(':basket_name',$name);
			$sth->bindValue(':profile_id',$profile_id);
			$sth->bindValue(':description',$description);
			$sth->bindValue(':category',$category);
			$sth->bindValue(':keywords',$keywords);
			
			$sth->execute();
			

			$last_insert_id=$db->lastInsertId();

			$sql2='INSERT INTO basket_collaborators(basket_id,profile_id)values(:basket_id,:profile_id)';
			$sth2=$db->prepare($sql2);
			$sth2->bindValue(':basket_id',$last_insert_id);
			$sth2->bindValue(':profile_id',$profile_id);

			$sth2->execute();


			$db->commit();

			return $last_insert_id;

		}catch(Exception $e){
			$db->rollback();
		}

	}


	function remove($db,$id){
		try{

			$sql2="UPDATE basket set is_deleted=1 where id=:id";
			$sth3=$db->prepare($sql2);
			$sth3->bindParam(':id',$id);
			$sth3->execute();

			return $sth3->rowCount();

		}catch(Exception $e){
			return array();
		}

	}

	function update_status($db,$id,$status='closed'){
		$sql="UPDATE basket set status=:status where id=:id";
		$sth=$db->prepare($sql);
		$sth->bindParam(':status',$status);
		$sth->bindParam(':id',$id);

		$sth->execute();

		return $sth->rowCount();

	}


	function update_description($db,$id,$description){
		$sql="UPDATE basket set description=:description where id=:id";
		$sth=$db->prepare($sql);
		$sth->bindParam(':description',$description);
		$sth->bindParam(':id',$id);

		$sth->execute();

		return $sth->rowCount();

	}


	function update_keywords($db,$id,$keywords){
		$sql="UPDATE basket set keywords=:keywords where id=:id";
		$sth=$db->prepare($sql);
		$sth->bindParam(':keywords',$keywords);
		$sth->bindParam(':id',$id);

		$sth->execute();

		return $sth->rowCount();

	}


	function search($db,$id,$param,$status='open',$page=1){

		define("LIMIT",30);

		$page=$page>1?$page:1;
		#set starting limit(page 1=10,page 2=20)
		$start_page=$page<2?0:( integer)($page-1)*LIMIT;

		#param
		$param='%'.$param.'%';

		if($status=='open'||$status=='closed'){
			$sql='SELECT basket.id,basket.description,basket_category.category,basket.default_route,basket.current_route,basket.status,basket.date_created,basket.date_modified,basket_name as name,account_profile.uid as collaborators_id,account_profile.id as collaborators_profile_id,account_profile.profile_name,account_profile.last_name,account_profile.first_name,account_profile.department,account_profile.position,account_profile.department_alias as alias,account_profile.profile_image as image,basket.profile_id as author_profile_id FROM basket_collaborators LEFT JOIN account_profile on account_profile.id=basket_collaborators.profile_id LEFT JOIN basket on basket_collaborators.basket_id=basket.id LEFT JOIN basket_category on basket.category=basket_category.id where (account_profile.uid=:uid and basket.status=:status and basket.is_deleted=0) and (basket.basket_name LIKE :param OR basket.description LIKE :param OR basket.keywords LIKE :param)  ORDER BY date_modified DESC LIMIT :start_page,:LIMITS';	
		}else{
			$sql="SELECT basket.id,basket.description,basket_category.category,basket.default_route,basket.current_route,basket.status,basket.date_created,basket.date_modified,basket_name as name,account_profile.uid as collaborators_id,account_profile.id as collaborators_profile_id,account_profile.profile_name,account_profile.last_name,account_profile.first_name,account_profile.department,account_profile.position,account_profile.department_alias as alias,account_profile.profile_image as image,basket.profile_id as author_profile_id FROM basket_collaborators LEFT JOIN account_profile on account_profile.id=basket_collaborators.profile_id LEFT JOIN basket on basket_collaborators.basket_id=basket.id LEFT JOIN basket_category on basket.category=basket_category.id where (account_profile.uid=:uid and basket.status!='draft' and basket.is_deleted=0) and (basket.basket_name LIKE :param OR basket.description LIKE :param OR basket.keywords LIKE :param) ORDER BY date_modified DESC LIMIT :start_page,:LIMITS";		
		}


		//if draft
		if($status=='draft'){
			$sql="SELECT basket.id,basket.description,basket_category.category,basket.default_route,basket.current_route,basket.status,basket.date_created,basket.date_modified,basket_name as name,account_profile.uid as collaborators_id,account_profile.id as collaborators_profile_id,account_profile.profile_name,account_profile.last_name,account_profile.first_name,account_profile.department,account_profile.position,account_profile.department_alias as alias,account_profile.profile_image as image,basket.profile_id as author_profile_id FROM basket_collaborators LEFT JOIN account_profile on account_profile.id=basket_collaborators.profile_id LEFT JOIN basket on basket_collaborators.basket_id=basket.id LEFT JOIN basket_category on basket.category=basket_category.id where (account_profile.uid=:uid and basket.status='draft' and basket.is_deleted=0) and (basket.basket_name LIKE :param OR basket.description LIKE :param OR basket.keywords LIKE :param)  ORDER BY date_modified DESC LIMIT :start_page,:LIMITS";
		}



		$sql1='SELECT profile_name as name, department_alias as alias,department,position,uid,profile_image as image,first_name,last_name FROM account_profile where id=:id ORDER BY id DESC LIMIT 1';

		$sth=$db->prepare($sql);
		$sth->bindValue(':uid',$id);
		$sth->bindValue(':param',$param);

		//if status is present
		if($status=='open'||$status=='closed'){
			$sth->bindValue(':status',$status);
		}

		$sth->bindValue(':LIMITS',LIMIT,\PDO::PARAM_INT);
		$sth->bindValue(':start_page',$start_page,\PDO::PARAM_INT);



		$sth->execute();

		$sth2=$db->prepare($sql1);

		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			if(strlen($row->description)>200) $row->description=substr($row->description, 0,200).'...';
			$basket=$row;
			$basket->author=array();
			$sth2->bindValue(':id',$row->author_profile_id);
			$sth2->execute();


			while($row2=$sth2->fetch(\PDO::FETCH_ASSOC)){
				#override name
				if(empty($row2['name'])) $row2['name']=$row2['first_name'].' '.$row2['last_name'];
				$basket->author=($row2);
			}

			$result[]=$basket;
		}


		return $result;


	}



}

?>