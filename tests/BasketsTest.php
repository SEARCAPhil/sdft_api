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
}


?>