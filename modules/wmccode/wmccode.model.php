<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * @class  tagModel
 * @author NAVER (wmccode@wmc.org)
 * @brief tag model class of the module
 */
class wmccodeModel extends wmccode
{
	public function triggerModuleListInSitemap(&$arr)
	{
		array_push($arr , 'wmccode');
	}
	/**
	 * @brief Initialization
	 */
	function init()
	{
	}
	/**
	 * @brief Imported Tag List
	 * Many of the specified module in order to extract the number of tags
	 */
	function saveCode($args )
	{
		$output =executeQuery('wmccode.insertCode', $args );
		return $output ;
	}
	function deleteCode( $args ){
		$output = executeQuery('wmccode.deleteCode', $args ); 
		return $output ;
	}
	
	function getCode($args)
	{
		$output = executeQuery('wmccode.getCode', $args );	
		return $output ;
	}
	
	function getCodeList($args)
	{
		$output = executeQueryArray('wmccode.getCodeList', $args );
		return $output ;
	}
	/****
	* LDAP 에서 가져 오기... 
	*/
	function getMemberLdap($args)
	{
		
	}
	function getMemberList($args )
	{
		$output = executeQueryArray('wmccode.getMemberList', $args );
		return $output ;
	}
	function saveEapp( $srls , $args){
		$out = false ;
		foreach( $srls as $key => $val){
			$args->member_srl = $val ;
			$output = executeQuery('wmccode.insertEapp' , $args);
			$out = $output->toBool();
		}
		return $out;
	}
	
	function saveLineShare( $srls , $args ){
 
	}
	function deleteEappAll( $srl){
		$obj->eapp_srl = $srl ;
		$output = executeQuery('wmccode.deleteEapp' , $obj);
		return $output ;
	}
	function getCodeListBySub( $args ){
		
		$output =executeQueryArray('',$args);  // code_gu , code_gu2 , code_var_val 세가지가 필요하다. 
		$oModule->add('progress' , $output->data);
	}

	/*
	*@ module_srl 즉 업무 페이지 마다 해당하는 그룹이 있다. 
	*/
	function getTeams( $args ){
		$args->module_srl ;
		$args->code_gu = "PMS" ;
		$args->code_gu2 = "TEAMS" ;
		return executeQueryArray("wmccode.getCodeList",$args );
	}
}
/* End of file wmccode.model.php */
/* Location: ./modules/wmccode/wmccode.model.php */
