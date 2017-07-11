<?php

use SDFT\Baskets;
use PHPUnit\Framework\TestCase;

/**
* 
*/
class BasketsTest extends TestCase
{
	function testGetBasketList(){

		#require database
		require 'config/database.php';

		$baskets= new Baskets();
		
		$this->assertGreaterThan(1,count($baskets->get_list($db,10)));
	}

	function testGetDetails(){
		#require database
		require 'config/database.php';

		$baskets= new Baskets();

		$this->assertTrue(isset($baskets->get_details($db,357)[0]->basket_name));
	}

	function testCreateBasket(){
		#require database
		require 'config/database.php';

		$baskets= new Baskets();

		$this->assertGreaterThan(0,$baskets->create($db,0,'$name','$description - THIS IS A TEST FROM PHPUNIT','$category','$keywords'));
	}


	function testRemoveBasket(){
		#require database
		require 'config/database.php';

		$baskets=new Baskets();

		$new_basket_id=$baskets->create($db,0,'$name','$description - THIS IS A TEST FROM PHPUNIT','$category','$keywords');

		$this->assertGreaterThan(0,$baskets->remove($db,$new_basket_id));


	}

	function testUpdateStatus(){
		#require database
		require 'config/database.php';

		$baskets= new Baskets();

		$new_basket_id=$baskets->create($db,0,'$name','$description - THIS IS A TEST FROM PHPUNIT','$category','$keywords');	

		#@param ($db,$id,$status='closed')

		$update_basket_result=$baskets->update_status($db,$new_basket_id,'open');


		$this->assertEquals(1,$update_basket_result);

		#remove after testing
		$baskets->remove($db,$new_basket_id);
	}

}


?>