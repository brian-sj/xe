<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * @class  tag
 * @author NAVER (developers@xpressengine.com)
 * @brief high class of the tag module
 */
define("__REGULAR_WORK_CODE__","R");

define("__CODE_PMS__","PMS");
define("__CODE_PMS_MAIN__","MAIN");
define("__CODE_PMS_SUB__","SUB");
 
define("__PMS_COMMENT_TYPE_DIRECT_WRITE__","A");	//업무메모
define("__PMS_COMMENT_TYPE_DAILY_REPORT__","B");  
define("__PMS_COMMENT_TYPE_TASK_LOG__","D");
 
define("__PMS_COMMENT_TYPE_MEETING_REPORT__","E");  //회의록
define("__PMS_COMMENT_TYPE_ISSUE__","I");  			//이슈
define("__PMS_COMMENT_TYPE_DIRECT_WORK__","W");  	//업무지시
define("__PMS_COMMENT_TYPE_CHECKLIST__","C");  		//체크리스트
define("__PMS_COMMENT_TYPE_ETC__","Z");  			//기타

define("__PMS_COMMENT_TYPE_BUDGET_LOG__","M");

define("__PMS_COMMENT_TYPE_LINK_LOG__","L");
define("__PMS_COMMENT_TYPE_WORKTIME_LOG__","R");
define("__PMS_COMMENT_TYPE_FILE_LOG__","F");

define("__PMS_CATEGORY_OUT_FAVORITE__","F");
define("__PMS_CATEGORY_OUT_MYDUTY__","Z"); 


define("__PMS_CATEGORY_IN_ACCEPT__","A");
define("__PMS_CATEGORY_IN_PLAN__","B");
define("__PMS_CATEGORY_IN_ING__","D");

define("__PMS_CATEGORY_IN_APPLYCLOSE__","P"); /* 완료 신청 */
define("__PMS_CATEGORY_IN_CLOSE__","E");  /* 완료 */
define("__PMS_CATEGORY_IN_HOLDING__","H");
define("__PMS_CATEGORY_IN_DROP__","X");
define("__PMS_CATEGORY_IN_REGULAR__","R");



define("__TIME_MONEY__", 4);
class pms extends ModuleObject
{
	/**
	 * @brief Implement if additional tasks are necessary when installing
	 */
	public $gu1 = array(22=>"신규" , 33 => "기능개선" ) ;
	public $g_category = array( 'A'=>'접수' , 'B'=>'예정' , 'D'=>'진행' , 'P'=>'완료신청' , 'E'=>'완료' , 'H'=>'보류' , 'X'=>'폐기' , 'R'=>'일상' );
	public $g_type = array( 'A'=>'업무메모' , 'E'=>'회의록' , 'I'=>'이슈' , 'W'=>'업무지시' , 'C'=>'체크리스트' , 'Z'=>'기타' );
	public $gm_members = array(
		188=> "안희영" 
	);
	public $pm_members = array(
		   4 => "강명희(Brian)"
		,320 => "이소라(Solar)"
	);
	
	public $wmc_members = array(
		   4 => "강명희(Brian)"
		,320 => "이소라(Solar)"

	);
		
	 private $triggers = array(
		array(
				'name' => 'menu.getModuleListInSitemap' ,
				'module' => 'pms',
				'type' => 'model',
				'func' => 'triggerModuleListInSitemap',
				'position' => 'after',
		)
	 );

	function moduleInstall()
	{
		$oModuleController = getController('module');
		//$oModuleController->insertTrigger('member.getMemberMenu', 'board', 'controller', 'triggerMemberMenu', 'after');
		foreach( $this->triggers as $trigger )
		{
			$oModuleController->insertTrigger(
				$trigger['name'],
				$trigger['module'],
				$trigger['type'],
				$trigger['func'],
				$trigger['position']
			);
		}
		
	}

	/**
	 * @brief a method to check if successfully installed
	 */
	function checkUpdate()
	{
		//return true ;
		$oModuleModel = getModel('module');
		
		foreach( $this->triggers as $trigger ){
				$res = $oModuleModel->getTrigger(
								$trigger['name'],
								$trigger['module'],
								$trigger['type'],
								$trigger['func'],
								$trigger['position']
						);
			if(!$res ){
				return true ;
			}
			
		}
		
		return false ;
	}

	/**
	 * @brief Execute update
	 */
	function moduleUpdate()
	{
		
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');
		
		foreach( $this->triggers as $trigger ){
				$res = $oModuleModel->getTrigger(
				$trigger['name'],
				$trigger['module'],
				$trigger['type'],
				$trigger['func'],
				$trigger['position']
			);
			if(!$res ){
				$oModuleController->insertTrigger(
					$trigger['name'],
					$trigger['module'],
					$trigger['type'],
					$trigger['func'],
					$trigger['position']
				);
			}
			
		}
		
		return new Object(0, 'success_updated');
	}

	/**
	 * @brief Re-generate the cache file
	 */
	function recompileCache()
	{
	}
	
	function moduleUninstall(){
		
		$oModuleController = getController('module');		
		foreach( $this->triggers as $trigger ){
				$res = $oModuleController->deleteTrigger(
								$trigger['name'],
								$trigger['module'],
								$trigger['type'],
								$trigger['func'],
								$trigger['position']
						);
		}
		
		return  new Object();
	}
	
}/* End of file pms.class.php */
/* Location: ./modules/pms/pms.class.php */