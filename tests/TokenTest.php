<?php
use SDFT\Token;
use PHPUnit\Framework\TestCase;

/**
* 
*/
class TokenTest extends TestCase
{	

	function testGetToken(){
		#require database
		require 'config/database.php';

		$token=new Token();

		$result=$token->get_token($db,'$2y$10$1L3EjbJcy2ILU4HF/rbYF.JQblPqE1XM4Fl27Bx/xZR7WbrEaLSH.');
		
		$this->assertTrue(isset($result->token));
	}
}


?>