<?php
class bookcAdminModel extends bookc
{
	function deleteBookcSiteGrantGroup($args)
	{
		// Parameter   :   $args -> site_srl 
		// 모든 그룹을 지운다. 0 은 지우지 않는다. 
debugPrint("-----");		
		$output = executeQuery('bookc.deleteBookcSiteGrantGroup' , $args);
		return $output ;
		
	}
	function insertBookcSiteGrantGroup($args){
		// Parameter    $args->site_srl   , $args->group_srl  ; 
		$output = executeQuery('bookc.insertBookToSite', $args );
		return $output ;
	}
}


?>
