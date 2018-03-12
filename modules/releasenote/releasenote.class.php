<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * @class  tag
 * @author NAVER (developers@xpressengine.com)
 * @brief high class of the tag module
 */
 
 

class releasenote extends ModuleObject
{
	/**
	 * @brief Implement if additional tasks are necessary when installing
	 */
	 
	 
	 private $triggers = array(
		array(
				'name' => 'menu.getModuleListInSitemap' ,
				'module' => 'releasenote',
				'type' => 'model',
				'func' => 'triggerModuleListInSitemap',
				'position' => 'after',
		)
	 );
	 
	 
	 
	function moduleInstall()
	{
		
		//$oModuleController->insertTrigger('member.getMemberMenu', 'board', 'controller', 'triggerMemberMenu', 'after');
		
		$oModuleController = getController('module');
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
	
}/* End of file releasenote.class.php */
/* Location: ./modules/releasenote/releasenote.class.php */