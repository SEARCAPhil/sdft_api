<?php

use SDFT\Activities;
use PHPUnit\Framework\TestCase;

/**
* 
*/
class ActivitiesTest extends TestCase
{
	function testlog_activity(){
		#database connection
		require 'config/database.php';

		$activities=new Activities();
		#@param(db,author,reciever,messsage)

		$insertions_result_id=($activities->log_activity($db,10,null,'This is a test log from PHPUnit'));

		$this->assertGreaterThan(0,$insertions_result_id);

	}	


}



?>