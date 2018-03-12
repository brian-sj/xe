<?php
/**
 * @class  releasenoteController
 * @author NAVER (developers@xpressengine.com)
 * @brief releasenote module's controller class
 releasenote.controller.php
 */
class releasenoteController extends releasenote
{
	/**
	 * @brief Initialization
	 */
	function init()
	{
		debugPrint('init....');
	}

	/**
	 * @brief , (Comma) to clean up the tags attached to the trigger
	 */
	function procReleasenoteWrite()
	{
		$args = Context::getRequestVars();
		$output = executeQuery("releasenote.insertReleasenote", $args);
		if( $output->toBool() )
			$this->add("result", "0");	
		else 
			$this->add("result" , "-1");
	}

	function procReleasenoteDeleteCode()
	{
		$oModel = &getModel('wmccode');
		$output =$oModel->deleteCode($args);
debugPrint( $output );
	}

	function procReleasenoteSaveCode() 
	{
		$oModel = &getModel('wmccode');
		$output =$oModel->saveCode($args);
debugPrint( $output );		
exit(0);
	}
	
	/*
	procReleasenoteChP
	procReleasenoteChFrm
	procReleasenoteChQuota
	procReleasenoteSvrCheck
	*/

}/* End of file releasenote.controller.php */
/* Location: ./modules/releasenote/releasenote.controller.php */
