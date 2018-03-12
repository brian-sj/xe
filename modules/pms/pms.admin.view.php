<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * The admin view class of the rss module
 *
 * @author NAVER (developers@xpressengine.com)
 */
class pmsAdminView extends pms
{
	/**
	 * Initialization
	 *
	 * @return void
	 */
	function init()
	{
		//Set template path
debugPrint("-------"); 
		$this->setTemplatePath($this->module_path.'tpl');
	}
		/// disk list 
	function dispPmsAdminList(){
debugPrint('444');
		$this->setTemplateFile("index");
	}
	/**** //// 쓰기 및 수정 하기 목록 
	* 
	*/
	function dispPmskAdminFsvrAdd(){
		$args = Context::getRequestVars();
		if( $args->fsvr_srl ) {
			$output = executeQuery('pmsk.getFileServer' , $args );
		}
		debugPrint( $output );		
		Context::set('view', $output->data);
		$this->setTemplateFile("add_fsvr");
	}
	/**
	*  클럽의 목록을 봅니다. 
	*/
	function dispPmskAdminClubList(){
		$args = Context::getRequestVars();
		$output = executeQuery('pmsk.getClubs' , $args );
				Context::set('args' , $args);
debugPrint( $output );		


		//$oHttp = &XEHttpRequest::getInstance(); //  new XEHttpRequest();
		
		//($target, $method, $timeout, $post_vars) 
/*****  test code **/		
		$data = "id=55grayon&pwd=briangold1"; 
		$oHttp= new HttpRequest( __WATV_LOGIN_HOST__  , __WATV_LOGIN_URI__);
		debugPrint( $oHttp);
		$o = $oHttp->sendRecv($data);
		debugPrint( $o->body);
/*****  test code  END **/
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page', $output->page);
		Context::set('list', $output->data);
		Context::set('page_navigation', $output->page_navigation);
		$this->setTemplateFile("club_list");
	}
	/***
		유저를 봅니다. 
	*/
	function dispPmskAdminUserList(){
		$args = Context::getRequestVars();
		$output = executeQuery('pms.getMembers' , $args ); 
		Context::set('args' , $args);
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page', $output->page);
		Context::set('list', $output->data);
		Context::set('page_navigation', $output->page_navigation);
		$this->setTemplateFile("user_list");
	}
	/***
		모달 창으로 띄울 것이므로 setLayout을 없앤다. 
	*/
	function dispPmskAdminClubListById(){
	
		Context::set('layout' ,'none');
		$args = Context::getRequestVars();
		$output = executeQuery('pmsk.getClubsById' , $args ); 
		
debugPrint( $output );		
		Context::set('list', $output->data);
		$this->setTemplateFile('club_list_byId');
	}
	
	function dispPmskAdminClubAdd(){
		$args = Context::getRequestVars();
		
		if( $args->club_srl ){
			$output = executeQuery('pmsk.getClub' , $args );
		}
debugPrint( $output);		
		Context::set('view', $output->data);
		
		/**  서버 IP combobox  */
		$output = executeQuery("pmsk.getFileServers" , $args );
		Context::set('fsrv_list' , $output->data);
		
debugPrint( $output ->data); 		
		
		$this->setTemplateFile("add_club");
	}
	function dispPmskAdminPrivAdd(){
		$args = Context::getRequestVars();
debugPrint( $args ); 
		$output = executeQuery('pmsk.getPrivs' , $args );
debugPrint( $output );		
		
		Context::set("args" , $args);
		//Context::set('total_count', $output->total_count);
		//Context::set('total_page', $output->total_page);
		//Context::set('page', $output->page);
		Context::set('list', $output->data);
		//Context::set('page_navigation', $output->page_navigation);


		$output = executeQuery('pmsk.getMembers' , $args );
			Context::set('total_count', $output->total_count);
			Context::set('total_page', $output->total_page);
			Context::set('page', $output->page);
			Context::set('user_list', $output->data);
			Context::set('page_navigation', $output->page_navigation);
debugPrint( $output );
		$this->setTemplateFile("add_priv");
	}
	
	
	function dispPmskAdminLogList(){
	
		$args = Context::getRequestVars();
		$output = executeQuery('pmsk.getLogs' , $args );
debugPrint( $output );	
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page', $output->page);
		Context::set('list', $output->data);
		Context::set('page_navigation', $output->page_navigation);
			
		$this->setTemplateFile("log_list");
	}

	/**  구분별로 타입을 정하기   **/
	function dispIboardAdminReportTypeApi(){}
	/*
	function dispPmskAdminPrivAdd(){
		$args = Context::getRequestVars();
		$output = executeQuery('pmsk.getPriv'   , $args );
		
		Context::set('view', $output->data);
		$this->setTemplateFile("add_club");
	}
	*/
	
}/* End of file pmsk.admin.view.php */
/* Location: ./modules/pmsk/pmsk.admin.view.php */