<?php

use SDFT\Baskets\Collaborators as Collaborators;
use PHPUnit\Framework\TestCase;
require 'vendor/autoload.php';

/**
* 
*/
class CollaboratorsTest extends TestCase
{
	
	function testAddCollaborator(){
		#require database
		require 'config/database.php';

		$collaborators=new Collaborators();

		#@param($db,$basket_id,$id)
		$collaborator_id=$collaborators->create($db,372,8);

		$this->assertGreaterThan(0,$collaborator_id);

	}
}



?>