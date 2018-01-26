<?php 

namespace SDFT;

/**
* 
*/
class Categories 
{
	
	function __construct(){}


	function get_parent_categories($db){

		$sql='SELECT *, basket_category.category as name FROM basket_category where parent_id IS NULL';
		
		$sth=$db->query($sql);

		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			$result[]=$row;
		}

		return $result;
	}


	function get_sub_categories($db,$parent_id){

		#get all categories unde $parent_id
		$sql='SELECT *, basket_category.category as name FROM basket_category where parent_id=:parent_id';

		$sth=$db->prepare($sql);

		$sth->bindParam(':parent_id',$parent_id);

		$sth->execute();

		$result=array();

		while($row=$sth->fetch(\PDO::FETCH_OBJ)){
			$row->category=utf8_encode($row->category);
			$row->name=utf8_encode($row->name);
			$result[]=$row;
		}

		return $result;
	}
}

?>