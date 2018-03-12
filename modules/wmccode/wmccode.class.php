<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */

/**
 * trash class
 * trash the module's high class
 *
 * @author NAVER (developers@xpressengine.com)
 * @package /modules/trash
 * @version 0.1
 * 
 *
 * 
 */
class wmccode extends ModuleObject
{
	/**
	 * Implement if additional tasks are necessary when installing
	 * @return Object
	 */
	public $ceo = array(7243=>"Aron" , 15251=>"Harry");

	public $gm_member = array( 
			15251=>"Harry"
			,12509 =>'Z01. Gilbird'
			,15268=>"Timothy"
			,10960 => 'Runa'
			,20740=>"Peter"
			,15246 =>"Cindy"
             );
	//public $gm_member = array( 4=>"강명희");

	// project_manager
	public $pm_members = array(
		  12509 =>'Z01. Gilbird'
		 ,1709=>"Sally"
		 ,385 => "김준호(ssk)" 
		 ,388 =>"Emma"
		 ,13336 =>"Ivy"
		 ,15246=>"Cindy"
		 
		 ,16473 =>'D51. Terry'
		 ,15252 =>'D52. Heather'
		 ,15248 =>'D53. Eiden'
		 
		,15249 =>'D11. Eoin'
		,15259 =>'D12. Kevin'
		,15805 =>'D13. Jin'
		,15262 =>'D14. Marina'
		,15804 =>'D15. Jess'
		 // Eoin, Kevin, Jin, Marina, Jess
		 // Terry, Heather, Eiden
		 // Gilbird 
	);
	
	public $releaser_members = array(
		 385 => "김준호(K)"
	);
	
	public $cfo_members = array(
		12509 =>'Z01. Gilbird'
	);
	
	/**
	*  실제 멤버에 있는 사람들이다. 
	*/
	public $wmc_members = array(

		7243=>"Z00. Aron"
		,15251 =>'Z10. Harry'
		,1709=>"Z11. Sally"		
		,16165=>"Z11. Jane"		
		,12509 =>'Z01. Gilbird'
		,10960 => 'Z20. Runa'
		,15264 =>'Z01. Paul'		
		,15254 =>'D51. John'

		,385 =>"T31. K"
		,386 =>"T31. Seok"
		,6129=>"T31. J.K" 
		,13336 => "T32. Ivy" 
		,1764 => "T32. Sehee"  
		,388  => "T33. Emma" 		
		,1765 => "T33. Mira"
		,7458 => "T33. vivian"	
		,23841 => "T33. ksi"	

		,15246 =>"Z90. Cindy"		
		,15243 =>"Z91. Amy"
		,6127=>"Z91. Sara"
		,15812 =>"Z91. Lina"		
		,15265 =>"Z91. Rio"

		,15268 =>'D10. Timothy'
		,15249 =>'D11. Eoin'
		,15261 =>'D11. Lukas'
		,15263 =>'D11. Patrick'
		,15259 =>'D12. Kevin'
		,15805 =>'D13. Jin'
		,21948 =>'D13. Chris'
		,15806 =>'D13. Sean'
		,15262 =>'D14. Marina'
		,15804 =>'D15. Jess'
		
		,20740 =>'D50. Peter'
		,16473 =>'D51. Terry'
		,15255 =>'D51. June'
		,23462 =>'D51. Keith'
		,23701 =>'D51. Vicky'
		,15813 =>'D51. Maru'
		
		,15252 =>'D52. Heather'
		,16651 =>'D52. Anna'
		,16652 =>'D52. Heize'
		,16653 =>'D52. Sandy'
		,22274 =>'D52. Nana'

		,15248 =>'D53. Eiden'
		,15245 =>'D53. Charles'
		,15824 =>'D53. Sam'

	); 
	/** 
	*  그룹에 해당 팀이 있어야 한다. 
	*  ,323 => "인프라"
	*  
	*/
	public $teams = array(
		6859 => "기획조정실"
		,15788 => "Drogen R&D"
		,8050 => "드로젠영업,마케팅"
		,3 => "제품기획"
		,324 => "개발1팀"
		,323 => "개발2팀"
		,325 => "디자인"
		,322 => "컨텐츠"
	);
	/***
	* WMC_CODE 에 들어 있는 데이터 들이다. 
	*
	*/
	public $request_dept = array(
		6859 => "기획조정실"
		,15788 => "Drogen R&D"
		,8050 => "드로젠영업,마케팅"
		,3 => "제품기획"
		,324 => "개발1팀"
		,323 => "개발2팀"
		,325 => "디자인"
		,322 => "컨텐츠"
		
	);

	private $triggers = array(
		array(
				'name' => 'menu.getModuleListInSitemap' ,
				'module' => 'wmccode',
				'type' => 'model',
				'func' => 'triggerModuleListInSitemap',
				'position' => 'after',
		)
	 );

	function moduleInstall()
	{
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
		return new Object();
	}

	/**
	 * A method to check if successfully installed
	 * @return bool
	 */
	function checkUpdate()
	{
		//$oDB = &DB::getInstance();
		//$oModuleModel = getModel('module');
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
		return false;
	}

	/**
	 * Execute update
	 * @return Object
	 */
	function moduleUpdate()
	{
		//$oDB = &DB::getInstance();
		//$oModuleModel = getModel('module');
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
		
		return new Object(0,'success_updated');
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
}
/* End of file wmccode.class.php */
/* Location: ./modules/wmccode/wmccode.class.php */

