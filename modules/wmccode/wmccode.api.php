<?php
class wmccodeAPI extends wmccode
{
	
	
	function dispWmccodeBySubCodeAPI(){
		
		$args = Context::getRequestVars();
		$output =executeQueryArray('',$args);  // code_gu , code_gu2 , code_var_val 세가지가 필요하다. 
		$oModule->add('progress' , $output->data);
	}
	
}// End of wmccode.api.php 
