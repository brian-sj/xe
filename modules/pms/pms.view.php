<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * @class  tagController
 * @author NAVER (developers@xpressengine.com)
 * @brief tag module's controller class
 */
class pmsView extends pms
{
	/**  ,스킨 없다... */
	function init()
	{
		$template_path = sprintf("%sskins/%s/",$this->module_path, $this->module_info->skin);
		if(!is_dir($template_path)||!$this->module_info->skin)
		{
			$this->module_info->skin = 'default';
			$template_path = sprintf("%sskins/%s/",$this->module_path, $this->module_info->skin);
		}
		$this->setTemplatePath($template_path);
		//$this->setTemplatePath($this->module_path.'skins/default/'  );
	}
	/*  내 페이지 */
	function dispPmsIndex(){
		Context::set('layout','HTML');
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl;

		/// * my_list 는 내가 작성한 리스트 입니다. 
		//// list는 내가 즐겨 찾기 한것입니다.
		$output = executeQueryArray('pms.getProject', $args);

		Context::set('my_list',$output->data);
		$output = executeQueryArray('pms.getProjectsFavorite', $args);
		Context::set('list',$output->data);
		if($args->report_date ) {
			$d = new DateTime( $args->work_date );
		}else{
			$d = new DateTime();
		}

		$args->end_date = $d->format('Ymd999999');
		$args->start_date = $d->modify("-7 day")->format('YmdHis');		
		
		// 요청한 업무 : 모든 카테고리 나옴
		if ($args->ask_complete == 'Y') {
			$args->progress_c = 100;
		}else if ($args->ask_complete == 'N') { 
			$args->progress_p = 99;
		}
		$output = executeQueryArray('pms.getTaskMyAskList', $args);
		Context::set('ask_list',$output->data); 

		// 해야할 업무 : 진행, 완료 중에서만 나옴
		$args->category= "D,R";
			
		if ($args->complete == 'Y') {
			$args->progress_c = 100;
		}else if ($args->complete == 'N') { 
			$args->progress_p = 99;
		}

		$output = executeQueryArray('pms.getTaskMyDutyList', $args);		
		Context::set('duty_list',$output->data);

		// 모든 카테고리를 다 검색한다.  앞뒤에서 카테고리 필터가 넘어 오는 경우가 있다. 
		$d = new DateTime();
		$args->start_date = $d->format('Y-m-d');
		$args->end_date   = $d->modify("+1 day")->format('Y-m-d'); 	
		$args->category   = "";
		$output = executeQueryArray("pms.getWorksByMemberSrl" , $args ); 

		Context::set('work_list',$output->data);
		Context::set('page_navigation', $output->page_navigation);
		
		$output = executeQuery("pms.getWorkTimeByProject" , $args );

		if( !$output->data->mh ) $output->data->mh = 0 ;
		Context::set('work_time',$output->data );

		$this->setTemplateFile('index_favorite');
	}

	function dispPmsTestView(){
		$document_srl =0;
		$oDocumentModel = &getModel('document');
		$oDocument = $oDocumentModel->getDocument( $document_srl , $this->grant->manager);

		Context::set('oDocument', $oDocument );

		Context::set('editor', $editor);
		$this->setTemplateFile('write_test');
	}

	/** 프로젝트 리스트를 봅니다.  **/
	function dispPmsProjectListView(){
		//Context::set('layout','HTML');
		$args = Context::getRequestVars();
		$logged_info = Context::get("logged_info");
		//$args->member_srl = $logged_info->member_srl ; 
		//if(!$args->page) $args->page = 1;
		if($this->grant->manager){
			$args->member_srl = null ; // 관리자는 모든 프로젝트 목록 보기. 
			$output = executeQueryArray('pms.getProject', $args);
		}	
		else{ 
			$output = executeQueryArray('pms.getProject', $args);
			// 나중에 공유된 것만 보이게 해야한다. 
			//$output = executeQueryArray('pms.getProjectByShared', $args);
		}
		
		

		
		//$output = executeQueryArray('pms.getProjectByShared', $args);

    	Context::set('list', $output->data);
		Context::set('page_navigation', $output->page_navigation);

		$oMember = &getModel('wmccode');
		Context::set('teams', $oMember->teams);
		Context::set('request_depts', $oMember->request_dept);
		Context::set('dev_depts', $oMember->teams);
		Context::set('g_category' , $this->g_category);

		$obj->code_gu = __CODE_PMS__;
		$obj->code_gu2= __CODE_PMS_MAIN__;
		$output = executeQuery('wmccode.getCodeList', $obj);
		Context::set('main_code_list',$output->data);

		$obj->code_gu2 = __CODE_PMS_SUB__  ;
		$obj->code_var_val = $args->gu1 ;

		if($obj->code_var_val){
			$output = executeQueryArray('wmccode.getCodeListBySubCode' , $obj );
			Context::set('sub_code_list' , $output->data);
		}

		/// 고정 업무는 다른것과 다르게 담당팀. 요청 부서 , 규모등이 없다.
		$this->setTemplateFile('index');
	}
	function dispPmsListView(){
		$args = Context::getRequestVars();

		$this->setTemplateFile('list');
	}
	/**  예산 보기  .. 아직 미완 **/
	function dispPmsBudgetView(){
		
		$args = Context::getRequestVars();
		
		$oPmsModel = &getModel('pms');
		$this->setTemplateFile('budget');
	}
	
	/** 파일 목록 보기   **/
	function dispPmsFileView(){
		$args = Context::getRequestVars();
		$output = executeQuery("pms.getFiles", $args);
		Context::set('type', $args->type);
		Context::set('list', $output->data);
		$this->setTemplateFile('file');
	}
    
	/** 간트 차트로 프로젝트 내용보기...   **/
	function dispPmsCalendarView(){
		//Context::setResponseMethod('JSON');

		$args = Context::getRequestVars();
		if( !$args->project_srl) return ;

		$logged_info = Context::get('logged_info');
		$args->target_srl = $args->project_srl ;
		$output = executeQueryArray("wmccode.getShareUser", $args);

		$shared_members	= array();
		foreach( $output->data as  $key => $val  ){
			array_push( $shared_members , $val->member_srl   ); 
		}

		Context::set('shared_members',$shared_members);
		$args->member_srl = null ;
		$output = executeQuery('pms.getProjectSummary', $args);
		/// 만약에 이게 비밀 프로젝트 인경우에는 권한 체크 한다. 
		if( $output->data->gu4 == "Y"){
			if(  $logged_info->is_admin =="Y"
				 || $output->data->member_srl == $logged_info->member_srl  
				 ||  in_array( $logged_info->member_srl , $shared_members ) ){
				/// 권한 있음. 
			}else{
				/// 권한 없음
				return $this->setTemplateFile('content_noauth');
				//return ;	
			}
		}

		if( $output->data ){
			//$shared_members=explode(" , ",$output->data->shared_members);
			$category = $output->data->category;
			$document_srl = $output->data->document_srl ;
		}
		Context::set('data',$output->data);
		$oDocumentModel = &getModel('document');
		$oDocument = $oDocumentModel->getDocument( $document_srl , $this->grant->manager);
 
		Context::set('oDocument', $oDocument );

		$output = executeQuery('pms.getFiles', $args);
		Context::set('file_list', $output->data);

		//$output = executeQuery('pms.getProjectUsers', $args);
		Context::set('g_category' ,   $this->g_category);
		Context::set('g_type' , $this->g_type);
		
		$output = executeQuery("pms.getWorkTimeByProject" , $args );

		if( !$output->data->mh ) $output->data->mh = 0 ;
		Context::set('work_time',$output->data );

		$args->member_srl = $logged_info->member_srl;


		$output = executeQuery('pms.getProjectsFavoriteByMemberSrl', $args );

		if( sizeof( $output->data) > 0  ){
			Context::set('favorite','Y' );
		}else{
			Context::set('favorite','N' );
		}
		
		/// 일상 업무인경우 

		$oEditorModel = &getModel('editor');
		$option->height=200;
		$option->primary_key_name = 'project_srl';
		$option->content_key_name = 'content';
		$option->enable_default_component = true ;
		$option->allow_fileupload = true ;
		
		$editor = $oEditorModel->getEditor($args->project_srl, $option);
		Context::set('editor', $editor);
			
		//$oEapprvModel = &getModel('wmceapprv');
		//$args->allDnMember = $this->module_info->all_dn_member 	;
		//$args->pmDnRoll = $this->module_info->pm_dn_member 		;
		//$args->gmDnRoll = $this->module_info->gm_dn_member 		;
		//$args->target_srl = $args->project_srl ;

		//$args->second_title = "완료 결재";
		//$output = $oEapprvModel->getEappContent($args );		
		//Context::set('eapp',$output );
		
		//$output = getLdapOuMembers( $args->allDnMember );
		//Context::set('shared_members' , $output->data);
		//Context::set('wmc_members' ,  $output);
		//debugPrint( $output );
		//debugPrint( $args->allDnMember );
		
		//Context::set('wmc_devdepts' , $oModel->teams);
		
		// if( $category =='R' ){
			// $this->setTemplateFile('content_routine');
		// }else{   //추진 업무 인경우. 
			// $this->setTemplateFile('content');
		// }
		$oMember = &getModel('wmccode');
		Context::set('sub_code_list' , $output->data);
		Context::set('wmc_members',$oMember->wmc_members);
		Context::set('pm_members' , $oMember->pm_members ) ;
		Context::set('dev_depts', $oMember->teams);
		Context::set('request_dept', $oMember->request_dept);

		
		$this->setTemplateFile('content');
		
	}
    /**  아직없다...  프로젝트 별로 업무 시간보기   **/
	function dispPmsWorkViewByProject(){

	}

/*
*@ 수발주 보고서 보기 페이지 입니다.
   수발주 모듈이 없으면 안됩니다.
*/
	function dispPmsWinventory(){


		$args->start_date = Context::get('start_date'); ;
		$args->end_date = Context::get('end_date');

		$d = new DateTime( $args->start_date );
		if( !$args->start_date ) $args->start_date = $args->start_date = $d->modify('last Sunday')->format('Y-m-d');
		$d = new DateTime( $args->end_date );
		if( !$args->end_date )   $args->end_date = $d->modify('next Sunday')->format('Y-m-d');


		Context::set('start_date', $args->start_date);
		Context::set('end_date',  $args->end_date );

		if ($args->end_date) $args->end_date.='9999';

		$args->belong = 'H';
		$output = executeQueryArray('winventory.getReport', $args );
		Context::set('hlist' , $output->data);

		$args->belong = 'C';
		$output = executeQueryArray('winventory.getReport', $args );
		Context::set('clist' , $output->data);

		$oWinventoryModel = &getModel('winventory');

		Context::set('g_gubun' , $oWinventoryModel->g_gubun);
		Context::set('g_kind' , $oWinventoryModel->g_kind);
		$this->setTemplateFile("report_winventory");
	}

/*
*@ 보고서 보기 페이지 입니다.  report.html 
*/
	function dispPmsWorkProgressView(){
		$args = Context::getRequestVars();

		//$args->start_date = date("Y-m-d" ,strtotime( $args->work_date .'last Sunday'  )   );
		//$args->end_date   = date("Y-m-d" ,strtotime( $args->work_date .'next Sunday'  )   );

		//$output = executeQuery("pms.getMantisLongterm" , $args);
		//$dt = strtotime('monday this week');

		if($args->report_date ) {
			$d = new DateTime( $args->report_date );
		}else{
			$d = new DateTime();
		}

		$args->start_date = $d->modify('last Sunday')->format('Ymd');
		$args->end_date = $d->modify('next Sunday')->format('Ymd');
		$output = executeQueryArray("pms.getReleasenoteReport", $args);
		Context::set("releasenote_list" , $output->data);

		///$args->week = (int) date('W');
		// 맨티스는 월요일  날짜로 찾는다.
		$args->weekday =  $d->modify('last Monday')->format('Y-m-d');
		$output = executeQuery('pms.getMantisStatus', $args);
		Context::set('m_list', $output->data);

		$oMember = &getModel('wmccode');
		Context::set('wmc_members',$oMember->wmc_members);
		Context::set('request_dept', $oMember->request_dept);
			
		$args->start_date = $d->modify('last Sunday')->format('Y-m-d');
		Context::set( 'start_date' , $args->start_date);
		$args->end_date = $d->modify('this Saturday')->format('Y-m-d');
		Context::set( 'end_date' , $args->end_date);

		$this->setTemplateFile('report');
	}
/**
* 수발주 통계를 보여 줍니다. 
*/
	function dispPmsWorkProgressOperView(){
		$args = Context::getRequestVars();

		if($args->report_date ) {
			$d = new DateTime( $args->report_date );
		}else{
			$d = new DateTime();
		}

	//debugPrint( $d->modify('last Sunday')  );

		$args->start_date = $d->modify('last Sunday')->format('Ymd');
		$args->end_date   = $d->modify('next Sunday')->format('Ymd');

		$oMember = &getModel('wmccode');
		Context::set('wmc_members',$oMember->wmc_members);

		$output = executeQueryArray("pms.getReportLmp", $args);
		Context::set("lmp_list", $output->data);
		
		$args->start_date = $d->modify('last Sunday')->format('Y-m-d');
		Context::set( 'start_date' , $args->start_date);
		$args->end_date = $d->modify('this Saturday')->format('Y-m-d');
		Context::set( 'end_date' , $args->end_date);
		$this->setTemplateFile('report_operate');
	}
/**
* 쓰기 
*/
	function dispPmsProjectWrite(){
		Context::set('layout','HTML');
		$args = Context::getRequestVars();
		
		$output = executeQuery('pms.getProjectSummary', $args);

		///// project 수정 인가 추가인가?
		if($output->data ) { // 수정 
			$args->sub_code = $output->data->sub_code ;
			$document_srl = $output->data->document_srl ;
			$args->code_var_val = $output->data->gu1 ;
			$output->data->update ="Y";
			Context::set('data',$output->data);
		}else{               //추가 
			$output->project_srl = getNextSequence();
			$output->update ="N"; 
			Context::set('data',$output);
			Context::set('project_srl', $output->project_srl);
		}

		$obj->code_gu = __CODE_PMS__;
		$obj->code_gu2= __CODE_PMS_MAIN__;
		$output = executeQuery('wmccode.getCodeList', $obj);
		Context::set('main_code_list',$output->data);

		$output = executeQueryArray('pms.getProjectUsers', $args);
		$user_list = array();
			foreach( $output->data as $value ){
				array_push( $user_list , $value->member_srl ) ;
			}
		Context::set('user_list', $user_list);
		$obj->code_gu2 = __CODE_PMS_SUB__  ;
		$obj->code_var_val = $args->code_var_val ;
		if($obj->code_var_val) $output = executeQueryArray('wmccode.getCodeListBySubCode' , $obj );
		
		
		$oMember = &getModel('wmccode');
		Context::set('sub_code_list' , $output->data);
		Context::set('normal_members',$oMember->wmc_members);
		Context::set('pm_members' , $oMember->pm_members ) ;
		Context::set('dev_depts', $oMember->teams);
		Context::set('request_dept', $oMember->request_dept);
		
		$oDocumentModel = &getModel('document');
		$oDocument = $oDocumentModel->getDocument( $document_srl , $this->grant->manager);

		Context::set('oDocument', $oDocument );
		

		$args->allDnMember = $this->module_info->all_dn_member 	;
		$args->pmDnRoll = $this->module_info->pm_dn_member 		;
		$args->gmDnRoll = $this->module_info->gm_dn_member 		;
		$args->target_srl = $args->project_srl ;

		$args->second_title = "완료 결재";
	

		Context::set('module_srl', $this->module_info->module_srl );
		$this->setTemplateFile('write');
		//$this->setTemplateFile('a');
	}

/**  
comment 쓰기... comment 에는 한글 댓글 쓰기가 따라 붙는다. 
*/
	function dispPmsCommentWrite(){
		Context::set('layout','HTML');
		$args = Context::getRequestVars();
		$this->setTemplateFile('comment_write');
	}
	/**
	*  PMS CommentWorkLogDetail 메모에서 한개씩보기 또는 , 수정하기 화면 
	*/
	function dispPmsCommentWorkLogDetail(){
		Context::setResponseMethod('JSON');
		$args = Context::getRequestVars();

		$output = executeQueryArray("pms.getCommentByCommentSrl", $args );
		if( $output->data ){
			Context::set('data' , $output->data[1] ); 
			Context::set('is_update', 'Y'  );			
		}else{  // 만약 수정이 아니라 신규 입력이라면 새로운 번호를 제공한다. 
			$data->comment_srl = getNextSequence();
			Context::set('data', $data  );			
			Context::set('is_update', 'N'  );			
		}
		
		
		if( $args->type=="edit" && $logged_info->member_srl == $output->data[1]->member_srl ) {
			Context::set("type", "edit");
		}
		//저장할때 권한 체크 하자.... 
		$args->upload_target_srl = $args->comment_srl ; 
		if( !$args->upload_target_srl ) $args->upload_target_srl = -1; // 아무것도 나오지 않게 한다. 
		$output = executeQueryArray("pms.getFiles", $args );
		Context::set('file_list', $output->data);

		$args->target_srl = $args->comment_srl ;
		$output = executeQueryArray("pms.getCommentOneline", $args );
		Context::set('comment_list' , $output->data  );
		///// 회람기능은 좀 있다가 넣겠습니다....   2016년 9월 12일

		$output = executeQueryArray('pms.getTasks',$args);
		Context::set('task_list', $output->data);
		
		$this->setTemplateFile('_comment_worklog_detail');
	}
	
/*
* 업무이력 _comment_table.html 이다... 다른 페이지에  이페이지도 html 로 동적 인클루두됨 
*/	
	function dispPmsWorkTimeCommentList(){
		Context::setResponseMethod('JSON');
		$args = Context::getRequestVars();
		
		$args->list_count =20; 
		$output = executeQueryArray('pms.getWorkTimeList', $args);

		Context::set('comment',$output->data);
		Context::set('page_navigation', $output->page_navigation);
		
		/// * 2016년 8월 10일 _comment_worklog 스킨을 없앴다. 
		/// 나중에 skintype 이 추가 되지 않는다면 이것은 단일파일로 처리한다. 
		/// 2016년 8월 11일 단일 파일로 처리 했다. 
		Context::set("title","업무이력(업무일지)");
		$this->setTemplateFile("_comment_table");		
		/*
		if($args->skintype=="timeline"){
			$this->setTemplateFile("_comment_table");		
			//$this->setTemplateFile("_comment_worklog");	   // 
		}else {
			$this->setTemplateFile("_comment_table");		
		}
		*/
	}
	/**
	 PMS 편집 하기 위해서 딱 에디터 만큼만 보여줌. 
	 _edit_info.html  이페이지도 html 로 동적 인클루두됨 
	*/
	function dispPmsEditWorkInfoDiv(){
		Context::setResponseMethod('JSON');
		$logged_info = Context::get('logged_info');
		$args = Context::getRequestVars();
		$args->member_srl = $logged_info->member_srl;
		
		if( !$args->work_date ) $args->work_date = (new DateTime())->format("Y-m-d");
		
		$output = executeQuery("pms.getReportWorkTimeByDate" , $args );
		Context::set('work_time',$output->data);
		
		$data = unserialize( $output->data->title);
		Context::set('extra_vars', $data);
		
		$output = executeQuery("pms.getTaskDetail" , $args );
		Context::set('data', $output->data);
		// project_srl 
		// task_srl  
		// work_date  , member_srl 
		$this->setTemplateFile("_edit_info");
	}
	/**
	 PMS comment 리스트를 다른 html에 포함 하기 위해서   이페이지도 html 로 동적 인클루두됨 
	 _comment_worklog
	*/
	function dispPmsCommentList(){
		Context::setResponseMethod('JSON');
		$args = Context::getRequestVars();

		//$args->type = __PMS_COMMENT_TYPE_DAILY_REPORT__ ;
		//$args->type = __PMS_COMMENT_TYPE_LINK_LOG__ ;
		$types=array(__PMS_COMMENT_TYPE_DIRECT_WRITE__,__PMS_COMMENT_TYPE_MEETING_REPORT__
						,__PMS_COMMENT_TYPE_ISSUE__,__PMS_COMMENT_TYPE_DIRECT_WORK__
						,__PMS_COMMENT_TYPE_CHECKLIST__,__PMS_COMMENT_TYPE_ETC__);
						
		if( !$args->type ) $args->type = $types;
		$output = executeQuery('pms.getComment', $args);
//debugPrint( $output );
		Context::set('comment',$output->data);
		Context::set('page_navigation', $output->page_navigation);
		Context::set('type', $args->type);
		Context::set('g_type' , $this->g_type);
		
		$this->init();
		//if( $args->skintype=="table"){
		Context::set("title","메모");
		$this->setTemplateFile("_comment_worklog");	

	}
	/**
	 PMS comment 리스트를 다른 html에 포함 하기 위해서  _comment_worklog
	*/
	function dispPmsCommentListWeeklyData(){
		Context::setResponseMethod('JSON');
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		if( $this->grant->is_admin  && $args->member_srl ) {
			//  관리자인 경우는 남의 member_srl을 받는다. 
		}else{
			$args->member_srl = $logged_info->member_srl ;	
		}
		
		if( !$args->report_date ) $args->report_date = date('Y-m-d');
		$d = new DateTime( $args->report_date );
		$dayofweek  = date('w' , strtotime($args->report_date) );

		if( $dayofweek == 0 ){ $d->modify('+1 day'); } // 일요일이이면 last sunday가 지난주가 되므로 월요일로 저장해서 last sunday를 찾는다.
		
		$args->start_date = $d->modify('last Sunday')->format('Y-m-d');
		$args->end_date = $d->modify('next Sunday')->format('Y-m-d');
	 	
		$dt->sunday = $d->modify('last sunday')->format('Y-m-d');
		$dt->monday = $d->modify('+1 day')->format('Y-m-d');
		$dt->tuesday = $d->modify('+1 day')->format('Y-m-d');
		$dt->wednesday = $d->modify('+1 day')->format('Y-m-d');
		$dt->thursday = $d->modify('+1 day')->format('Y-m-d');
		$dt->friday = $d->modify('+1 day')->format('Y-m-d');
			
		Context::set('dt', $dt);
        //$args->start_date = date("Y-m-d" ,strtotime( $args->report_date .'last Sunday'  )   );
		//$args->end_date   = date("Y-m-d" ,strtotime( $args->report_date .'next Sunday'  )   );
		$args->member_srls = $args->member_srl ; 
		$output = executeQueryArray("pms.getReportWorkByMember" , $args);
		$data = array();
		foreach( $output->data as $key => $val )
		{
			$wod = "w".$val->dayofweek;
			// 위의업무와 같은것일때   ...
			$ret = $this->_hasTaskSrl ( $val->task_srl , $data) ;
			if( $ret >-1 ) {
				$data[ $ret ]-> $wod = $val->mh ;
			//if(1){
			}else{
				$data[$key]= $val ;
				$data[$key]->$wod = $val->mh ;
			}
		}
		Context::set('list' , $data);
		$this->setTemplateFile('_work_view_weeklydata');
	}	
	
	/**
	* favorite 화면등에서 끼워넣을 페이지를 구성함. 
	*/
	function dispPmsWorkViewByDate(){
		Context::setResponseMethod('JSON');
		$args = Context::getRequestVars();
		$logged_info = Context::get("logged_info");
		$d = new DateTime( $args->report_date );
		$args->start_date = $d->format('Y-m-d');
		//$args->end_date   = $d->modify("+1 day")->format('Y-m-d'); 	
		$args->end_date   = $d->format('Y-m-d'); 	
		$args->category   = "";
		$args->member_srl = $logged_info->member_srl;
		
		$output = executeQueryArray("pms.getWorksByMemberSrl" , $args ); 

		
		Context::set('work_list',$output->data);
		$this->setTemplatePath();
		$this->setTemplateFile('_work_time_bydate');
	}
	/**
	   일상 업무 보고 보고서..
	   report_routine.html 
	*/
	function dispPmsWorkRegularView(){
		$args = Context::getRequestVars();
		if( !$args->report_date ) $args->report_date = date('Y-m-d');
		$d = new DateTime( $args->report_date );
		$args->start_date = $d->modify('last Sunday')->format('Y-m-d');
		$args->end_date = $d->modify('next Sunday')->format('Y-m-d');
        //$start_date = date("Y-m-d" ,strtotime( ' last Sunday' , strtotime($args->report_date)  )   );
		//$end_date   = date("Y-m-d" ,strtotime( ' next Sunday' , strtotime($args->report_date)  )   );

		Context::set( 'start_date' , $args->start_date);
		$args->end_date = $d->modify('last Saturday')->format('Y-m-d');
		Context::set( 'end_date' , $args->end_date);
		$this->setTemplateFile("report_routine");
	}

	/**API.. 그룹 별로 기간에 따라 일한 리스트를 보여줌.  */
	function dispPmsTeamWorkViewTable(){
		// 일간 , 주간 , 월간
	}
	function dispPmsWorkView(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		if( $this->grant->is_admin  && $args->member_srl ) {
			//  관리자인 경우는 남의 member_srl을 받는다. 
		}else{
			$args->member_srl = $logged_info->member_srl ;	
		}


		$args->member_srl = $logged_info->member_srl ;
		$output = executeQueryArray("pms.getBookmark", $args );
		Context::set("bookmarklist" , $output->data);

		$output = executeQueryArray("pms.getProjectsFavorite", $args);
		Context::set("favoriteproject_list", $output->data);
		
		$oMember = &getModel('wmccode');
		Context::set('wmc_members',$oMember->wmc_members);
		Context::set('grant', $this->grant );
	
		$this->setTemplateFile('work_view');
	}
	
	/**
	   업무분류
	*/
	function dispPmsWmccode(){
		$args = Context::getRequestVars();
		$obj->code_gu = __CODE_PMS__;
		$obj->code_gu2 = $args->code_gu2;
		if( !$obj->code_gu2) $obj->code_gu2 = __CODE_PMS_MAIN__;
		$obj->sort_index = 'sort_seq';
		$obj->list_count = 100;
		$output = executeQueryArray('wmccode.getCodeList', $obj);
		Context::set('code_list',$output->data);

		$this->setTemplateFile('index_wmc_code'); 
	}
	
	function dispPmsWmccodeWrite() {
		$args = Context::getRequestVars();

		if ($args->code_int_val) {
		$output = executeQuery('wmccode.getWmccode', $args);
		Context::set('data', $output->data);		
		}
	
		$this->setTemplateFile('write_wmc_code');
	
	}

	/**API.. 개인별로 기간에 따라 일한 리스트를 보여줌.  */
	function dispPmsPersonalWorkViewTable(){
		// 일간 , 주간 , 월간
		Context::set('layout' , "HTML");
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');

		/**  manager 가 아니면 자기 꺼만 본다. */
		if(!$this->grant->team_manager && !$this->grant->gm_manager && !$this->grant->gp_manager)
		{
			//############일단 모두 다 보게 합니다... 
			// $args->member_srls = $logged_info->member_srl ;
		}else {
		// 매니저 이면 모든 과를 다 보게 하고...
		// 자기과의 모든 사람의 업무를 보게 한다.
			//// 특정인이 지정되지 않았으면 팀 전체 결과를 보여 준다.
				$obj->group_srl = $args->team ;
				$output = executeQueryArray('pms.getMembersByGroupSrl',$obj);
				Context::set('team_members', $output->data);
			
			if(!$args->member_srl){				
				$args->member_srls = array();
				foreach( $output->data as $key => $val){
					array_push( $args->member_srls , $val->member_srl);
				}
			}else{
				$args->member_srls = $args->member_srl ;
			}
		}
		//$args->member_srls = array(4, 113);
		$oModel = &getModel('pms');

		if( !$args->work_date ) $args->work_date = date('Y-m-d');

		$d = new DateTime( $args->work_date );
		$args->start_date = $d->modify('last Sunday')->format('Y-m-d');
		$args->end_date = $d->modify('this Saturday')->format('Y-m-d'); 
		$output = executeQueryArray('pms.getReportWorkByMember', $args );
		
		$data = array(); 
		foreach( $output->data as $key => $val )
		{
			$wod = "w".$val->dayofweek;
			// 위의업무와 같은것일때...
			$ret = $this->_hasTaskSrl ( $val->task_srl , $data) ;
			if( $ret >-1 ) {
				$data[ $ret ]-> $wod += $val->mh ;
			//if(1){
			}else{
				$data[$key]= $val ;
				$data[$key]->$wod = $val->mh ;
			}
		}
		
		$oMember = &getModel('wmccode');
		Context::set('teams', $oMember->teams);

//debugPrint( $oMember->teams);
		Context::set('list' , $data);
		Context::set('start_date',$args->start_date );
		Context::set('end_date',$args->end_date );


		$this->setTemplateFile('work_view_report');
	}

	
	
	
// 배열이 특정 값을 가지고 있는지 확인... 통계때 공통된거 합칠라고..

	function _hasTaskSrl( $value , $arr ) {
		foreach( $arr as $key => $val){
			if( $val->task_srl == $value  ) return $key ;
		}
		return -1 ;
	}

	/**   API 테스트용 입니다.
	*/
	function dispPmsProjectWorkListAPI(){}
	/**API.. 일한 시간 표시할때(수정을 위해) 카테고리에 맞게 프로젝트 리스트를 보여줌 */
	function dispPmsProjectListAPI(){}
	/** Task List  일한 시간 표시할때(수정을 위해) 현재 프로젝트에 있는 task list 보여줌. **/
	function dispPmsTaskListAPI(){}
	/**일한 시간 표시할때(수정을 위해) 상황 뿌려 주기.*/
	function dispPmsReportWorkTimeAPI(){}
	/**  각 대 분류별로 하위 분류 보여 주기  **/
	function dispPmsBySubCodeAPI(){	}
	/** 주간 업무 보고 내역 **/
	function dispPmsWeeklyReportProjectAPI(){}

	/**  주간 일상 업무 보고 내역 **/
	function dispPmsWeeklyReportRoutineProjectAPI(){}
	/**  help 통계  **/
	function dispPmsWeeklyReportHelpAPI(){}
	
	/* 이번주 한사람이 몇시간 업무 일지 등록 했는지 보여주는 함수  */
	function dispPmsWeeklyReportWorkTimeByMemberSrl(){}

}/*
pms.view.php*/
