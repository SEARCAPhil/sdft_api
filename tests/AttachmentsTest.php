<?php

use SDFT\Attachments;
use SDFT\Baskets;
use PHPUnit\Framework\TestCase;
require 'vendor/autoload.php';

/**
* 
*/
class AttachmentsTest extends TestCase{

	function testCreateAttachment(){
		#require database
		require 'config/database.php';

		$baskets= new Baskets();
		$attachments= new Attachments();

		$basket_id=$baskets->create($db,0,'$name','$description - THIS IS A TEST FROM PHPUNIT','$category','$keywords');

		$this->assertGreaterThan(0,$attachments->create($db,$basket_id,0,0,'$type','$old_filename','$new_filename'));

		#set deleted to database
		$baskets->remove($db,$basket_id);
	}

}