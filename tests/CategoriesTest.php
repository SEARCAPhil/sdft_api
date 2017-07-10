<?php 
use SDFT\categories as categories;
use PHPUnit\Framework\TestCase;
require 'vendor/autoload.php';


/**
* Category Test
*/
class CategoriesTest extends TestCase
{
	
	function testGetParentCategoriesMustNotBeEmpty(){

		#require database
		require 'config/database.php';

		$a=new categories();

		$this->assertNotEmpty(count($a->get_parent_categories($db)));

	}

	function testSubCategoriesMustBeEmpty(){
		#require database
		require 'config/database.php';

		$a=new categories();

		$this->assertEmpty(count($a->get_sub_categories($db,100)));


	}
}

?>