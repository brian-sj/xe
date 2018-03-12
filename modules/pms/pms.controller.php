<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * @class  tagController
 * @author NAVER (developers@xpressengine.com)
 * @brief tag module's controller class
 */
class pmsController extends pms
{
	/**
	 * @brief Initialization
	 */
	function init()
	{
	}

	/**
	 * @brief , (Comma) to clean up the tags attached to the trigger
	 */
	function procPmsTaskSave(){
		$args = Context::getRequestVars();
		$logged_info = Context::get("logged_info");
		
		$ret ="";
		$args->title = $args->text ;
		$args->parent_task_srl = $args->parent ;
		$sdt = new DateTime ($args->s_date); 
		$args->start_date =  $sdt->format("Ymd") ;
		$args->planed_start_date = $args->start_date ;

		if( !$args->duration ) $args->duration = 1;
		$args->end_date = $sdt->add( new DateInterval("P".$args->duration."D") )->format("Ymd");

		
		if( $args->update =="Y") {
			$args->task_srl = $args->id;
			$args->progress= $args->progress *100;
			$output = executeQuery("pms.updateTask" , $args );
			$ret = "-UPDATE";
		}else {
			$args->task_srl = getNextSequence();
			$args->member_srl = $logged_info->member_srl ; // 작업을 등록한 등록자
			$output = executeQuery('pms.insertTask' , $args );
			$ret = "-INSERT";
		}
		/////   task 해당하는 user를 지운다음에 다시 입력한다.
		$output1 = executeQuery('pms.deletePmsUser' , $args );
		foreach( $args->users as $key => $value  ){
			$args->member_srl = $value ;
			$output1 = executeQuery('pms.insertPmsUser' , $args) ;
		}
		//저장 합니다.
		if(!$output->toBool()){
			return $output ;
		}

		
		$obj->title = "Task 저장 /" . $obj->task_srl ."/" . $logged_info->nick_name ;
		$obj->content = "저장내용".$obj->text ;
		
		$obj->comment_srl = getNextSequence();
		$obj->type = __PMS_COMMENT_TYPE_LINK_LOG__;
		$this->_addLog( $obj );

		$this->add('success', "OK".$ret);
		$this->add('task_srl' , $args->task_srl);
		//$this->setRedirectUrl( $args->error_return_url );
	}

	function procPmsTaskDelete(){
		$args = Context::getRequestVars();
		if( !$args->id ) return ;
		else $args->task_srl = $args->id ;

		$logged_info = Context::get("logged_info");
		$args->title = "Task 삭제 /" . $args->task_srl ."/" . $logged_info->nick_name ;
		$args->content = "삭제내용".$args->text ;
		$args->member_srl = $logged_info->member_srl ;
		$args->comment_srl = getNextSequence();
		$args->type = __PMS_COMMENT_TYPE_TASK_LOG__;

		$output = executeQuery("pms.insertComment", $args ); // title , content , type , task_srl , project_srl
		$output = executeQuery("pms.deleteTask", $args);
		
		
	}

	/**
	* @ brief :  2015.05.20 일에 work_srl = 13 인 업무에 대해서
	                                        member_srl 4 인 사람이
											work_time 3시간 일을 했다.
											progess는 30으로 수정이 된다.
	*/
	function procPmsWorkTimeSave(){
		$args = Context::getRequestVars();
		$ret = "";
debugPrint( $args );
		//  $args->  work_srl , project_srl , work_time , work_date
		//  VALUES (project_srl, task_srl, member_srl, report_work_srl, 'work_date', work_time, 'regdate');

		$logged_info = Context::get("logged_info");
		if( $logged_info->is_admin && $args->member_srl ) {
			//  관리자인 경우는 남의 member_srl을 받는다.  
		}else{
			$args->member_srl = $logged_info->member_srl ;	
		}
		/// pms_report_work_time 테이블에 있는것을 가져 온다. 
		$output = executeQuery("pms.getWorks" , $args);
		if( sizeof($output->data )  == 1 ) {
			$args->project_srl     = $output->data->project_srl ;
			$args->report_work_srl = $output->data->report_work_srl ;
		}
		$output = executeQuery("pms.getTaskDetail", $args);
		
		if( $output->data){
				$extra_vars->mh = $output->data->mh ;  		  // 이제것 누적 mh 
				$extra_vars->p =  $output->data->progress ;   // 저장당시 진행률 
				$extra_vars->pmh = $output->data->planed_mh ; // 저장 당시 planed_mh 
				$extra_vars->amh = $args->work_time  ; 		  // 이번에 저장한 added_mh 
				$extra_vars->title = $args->content ;
				$extra_vars->dt = date("Y-m-d H:i");
				
				$msg->title = $output->data->title ;
				/// 내가 작업자라면 요청자에게 내가 요청자라면 작업자에게 쪽지를 날린다. 
				if( $logged_info->member_srl == $output->data->member_srl )
				{$msg->member_srl = $output->data->work_member_srl;}
				else{$msg->member_srl  = $output->data->member_srl ;}

 		}
		$args->title = serialize( $extra_vars );
		///  1/4 먼저 있는지 없는지 검사를 한다.
		if( !$args->report_work_srl && $args->work_time != 0  ){ ///2 없으면 인서트 하고
			$args->report_work_srl = getNextSequence();
			$output =executeQuery("pms.insertReportWork", $args);
			$ret = "INSERT";
		}else {
			///3 있으면 업데이트 한다.   만약 time이 0이면 삭제를 한다.
			if( $args->work_time != 0 ){
				$output=executeQuery("pms.updateReportWork", $args);
				$ret = "UPDATE";
			}else {
				$output=executeQuery("pms.deleteReportWork", $args);
				$ret = "DELETE";
			}
		}
		// 2/4업무 진행 시간 구하기. 
		$output = executeQuery("pms.getSumMHByTask", $args );	
		$obj->sum_mh = $output->data->mh;

		// 3/4진행율도 저장한다.  task_srl , progress 두개만 업데이트 한다.
		$obj->task_srl = $args->task_srl ;
		$obj->progress = $args->progress ; 
		$obj->end_date = $args->end_date ; 
		$obj->code_urgency = $args->code_urgency ;
		$obj->code_importance = $args->code_importance ;
		$obj->sort_order = $args->sort_order ;
		$output =executeQuery("pms.updateTask", $obj);
debugPrint($output );		
debugPrint($obj );		
		///## 4/4 즐겨 찾기에 추가한다.  즐겨 찾기라고 해도 이것은 task_srl 이 들어 있는 것이다..
		if($args->favorite=="Y"){
			$output = executeQuery("pms.deleteFavorite", $args );
			$output = executeQuery("pms.insertFavorite", $args);
		}
		
		///## 4/5 일상 업무자를 위한 메신저 기능... 
		if( $args->wmc_msg =='Y'){
		    //메시지를 발송 합니다.  내가 요청한 사람이라면 작업자에게 메시지 보낸다. . 
			$output = executeQuery("member.getMemberInfoByMemberSrl" , $msg);
			$obj->recvIdList = $output->data->user_id ;
			$obj->title  ="[PMS] ". $msg->title." \n ";
			$obj->contents .= "\n ". htmlspecialchars_decode( $args->content ) ."\n\n" ;
			$obj->sendId = $logged_info->user_id;
			sendWMCMessage($obj);
		}
		$this->add('success', "OK-".$ret);
		$this->add('report_work_srl', $args->report_work_srl );
	}
	/** 프로젝트 수정 */
	function procPmsProjectSave() {
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		//$args->member_srl = $logged_info->member_srl  ;
		$args->module_srl = $this->module_srl ;
		$member_srl = $args->member_srl ;
		/** document_srl 을 다른이름으로 저장했음. */
		//$args->document_srl = $args->pms_document_srl ;

		$output = $this->_saveDocument( $args );
		$args->document_srl = $output->document_srl ;
		// 받은사람으로 교체 
		
		/// document 모듈이 member_srl 을 바꾸니까 다시 원복 ..
		$args->member_srl = $member_srl ;
		
		if( $args->update=="Y" ) {
			$output = executeQuery("pms.updateProject", $args);
		}
		else {
			//$args->project_srl = getNextSequence();
			$output = executeQuery("pms.insertProject", $args);
			//share에 넣어 주자.. 		
		}

		$wobj->member_srl = $member_srl  ;
		$wobj->target_srl = $args->project_srl ;
		$wobj->stt="PMS";
		//share에 넣어 주자...  있으면 중복 에러 나겠지... 
		$output = executeQuery('wmccode.insertShareUser' , $wobj) ; 
		
		if( $args->member_srl != $logged_info->member_srl  ){
			$wobj->member_srl = $logged_info->member_srl ;
			$output = executeQuery('wmccode.insertShareUser' , $wobj) ; 
		}	
		//$this->setRedirectUrl( $args->error_return_url );
		$this->setRedirectUrl( "./?act=dispPmsCalendarView&project_srl=" . $args->project_srl . "&page=".$args->page ."&mid=".$this->mid );
	} 
	/** 진행율 만 간단히 수정   */
	function procPmsProjectProgressSave(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl ;
		$args->type ="L";
		$args->content  = "";
		if($args->progress) $args->content .="진행률->" . $args->progress ;
		if($args->end_date) $args->content .= " 날짜 :-> " .$args->end_date ;
		if($args->planed_end_date) $args->content .= " 예상종료일자 :->".$args->planed_end_date ;
		if($args->planed_mh) $args->content .= ": 예정시간 :->".$args->planed_mh ;
		$args->comment_srl = getNextSequence();
		//$args->title = "By-"  . $logged_info->user_name ;
		$args->title ="."; 
		$output = executeQuery("pms.insertComment", $args ); // title , content , type , task_srl , project_srl
		$output = executeQuery("pms.updateProjectSimple", $args );
		$this->setRedirectUrl( $args->error_return_url );
	}
	
	function procPmsProjectConfirmSave(){
		$args = Context::getRequestVars();

		if( $args->project_srl ) {
			$output = executeQuery("pms.updateProjectConfirm", $args);	
		}
		
		$this->setRedirectUrl( $args->error_return_url );
		
	}
	

	function procPmsCommentSave() {
		$args = Context::getRequestVars();

		$logged_info = Context::get("logged_info");
		$args->member_srl = $logged_info->member_srl ;

		if( $args->type == __PMS_COMMENT_TYPE_DAILY_REPORT__ ){
			$args->title .= $logged_info->user_name ."작업관련기록";
			if(!$args->reg_date) $args->reg_date =date('Ymd'); 
			$args->reg_date =  date('Ymd000000',strtotime( $args->reg_date));
		}

		if( $args->is_update =='Y' ) {
			$output = executeQuery("pms.updateComment", $args);
		}
		else {
			//$args->comment_srl = getNextSequence();
			$output = executeQuery("pms.insertComment", $args);
		}

		$this->add('comment_srl', $args->comment_srl );
		$this->setRedirectUrl( $args->error_return_url );
	}
	/* 한줄 메모 입력 */
	function procPmsCommentOnelineSave(){
		$args = Context::getRequestVars();
		
		$logged_info = Context::get("logged_info");
		$args->member_srl = $logged_info->member_srl;
		
		if(!$args->oneline_srl){  // 입력 
			$args->oneline_srl = getNextSequence(); 
			$output = executeQuery("pms.insertCommentOneline", $args );
		}else{
			// 수정은 아직 필요하지 않다. 2016-09-13
		}


		$this->add('user_name' , $logged_info->user_name);
		if( $output->toBool()){
			$this->add('success', "OK");
		}else{
			$this->add('success', "ERROR");
		}
		
		$this->add('oneline_srl', $args->oneline_srl);
	}
	/* 한줄 메모 삭제  */
	function procPmsCommentOnelineDelete(){
		$args = Context::getRequestVars();
		
		$logged_info = Context::get("logged_info");
		$args->member_srl = $logged_info->member_srl;
		
		if( $logged_info->is_admin) $args->member_srl = null ;
		else $args->member_srl = $logged_info->member_srl ;

		$output = executeQuery("pms.deleteCommentOneline", $args );
		if( $output->toBool()){
			$this->add('success', "OK");
		}else{
			$this->add('success', "ERROR");
		}		
	}
	
	function procPmsShareListSave(){
		$args = Context::getRequestVars();
		$logged_info = Context::get("logged_info");

		// 내꺼가 관리자 이상이거나....
		// 문서의 주인이라면 수정이 가능하다.
		/*
		if( !$this->grant->team_manage && !$grant->is_admin ){
			return new Object(-1,'msg_not_permitted');
		}
		*/
		///###1/2 자동으로 Favorite에 등록을 해준다...  빼는건 개인이 뺄 수 있다.
		$obj->project_srl = $args->project_srl ;
		$wobj->target_srl = $args->project_srl ;
		$wobj->stt ="PMS";
		
		$output1 = executeQuery('wmccode.deleteShareUser' , $wobj) ; 

		//일단... 나는 기본적으로 넣는다. 
		$wobj->member_srl = $logged_info->member_srl;
		$output1 = executeQuery('wmccode.insertShareUser' , $wobj) ;

		foreach( $args->shared_members as $key => $value  ){
			$obj->member_srl = $value;
			$output  = executeQuery("pms.insertFavorite", $obj )  ; //중복 입력시 입력이 않된다. 
			// target_srl , member_srl 
			$wobj->member_srl = $value ;
			$output1 = executeQuery('wmccode.insertShareUser' , $wobj) ; 
		}
		///###2/2 읽기 권한을 준다. 아직은 모두 보기로 권한별 차단은 없다. 2016.06.07
		//$args->shared_members = implode(" , ",$args->shared_members);
		//$output = executeQuery("pms.updateProjectSimple", $args);  
		$this->setRedirectUrl( $args->error_return_url );
	}

	/* 답글 지우기  */
	function procPmsCommentDelete(){
		$args = Context::getRequestVars();

		if( $args->comment_srl ) {
			$output = executeQuery("pms.deleteComment", $args);
		}
		
		$this->setRedirectUrl( $args->error_return_url );
		
	}
	

	/** 태스트에 대한 링크를  추가/삭제합니다. */
	function procPmsLinkSave() {
		$args = Context::getRequestVars();
		/// source , target , type , project_srl
		if( $args->link_act =="A") {
			$output = executeQuery("pms.insertTaskLink", $args );

		}else if($args->link_act=="D"){
			$output = executeQuery("pms.deleteTaskLink", $args );
		}
		$this->add('success', "OK");
	}

	function procPmsFavoriteToggle(){

		$args = Context::getRequestVars();

		$logged_info= Context::get("logged_info");
		$args->member_srl = $logged_info->member_srl;

		if($args->add=="Y") {  // 더하기
			$output = executeQuery("pms.insertFavorite", $args );
		}else if($args->add=="N"){  // 즐겨찾기에서 제외
			$output = executeQuery("pms.deleteFavorite", $args );
		}else if( $args->add=="X"){  // 바로 가기만 제외
			$output = executeQuery("pms.updateFavorite", $args );
		}

		if( $output->toBool() ){
			$this->add("add" , $args->add);
		}else{
			$this->add("add" , "ERROR");
		}
	}
	/** 파일을 업로드 한다...  */
	function procPmsFileUpload(){
		$args = Context::getRequestVars();
		$param->upload_root_path="files/pms/";
		$logged_info = Context::get('logged_info');

		/// real name 즉 서버에서도 구분을 넣어 주고...
		if( !$args->prefix) $args->prefix ="[ETC]";
		if( $args->prefix=="[NON]" ) $args->prefix ="";

		$param->prefix = $args->prefix .date('Y_m_d__');
		$output = fileUpload( $param );

		/// disp_name 에다가(DB) 에다가고 구분을 넣는다.
		$output->file_disp_name = $param->prefix .$output->file_disp_name ;
		$output->project_srl 	= $args->project_srl;
		$output->upload_target_type = $args->prefix;
		$output->source_filename = $output->file_disp_name ;
		$output->uploaded_filename = $output->file_real_name .".". $output->ext ;
		$output->pms_file_srl = getNextSequence();
		$output->member_srl = $logged_info->member_srl ;
		$output->module_srl = $this->module_srl ;
		$output->upload_target_srl = $args->upload_target_srl ; 
		$output = executeQuery('pms.insertFile', $output );

	}

	/* 파일의 부가 설명 정보를 수정한다. */
	function procPmsFileCommentUpdate(){
		$args->pms_file_srl = Context::get('pms_file_srl');
		$args->comment = Context::get('comment');
		$output = executeQuery("pms.updateFileComment", $args);

		if( $output->toBool() ){
			$this->add("result", $args->pms_file_srl);
			$this->add("pms_file_srl" , $args->pms_file_srl);
		}else{
			$this->add("result","error");
		}
	}

	/* 파일을 삭제 한다. . */
	function procPmsFileDelete(){
		$args->pms_file_srl = Context::get('pms_file_srl');
		$output = executeQuery("pms.deleteFile" , $args );
		if( $output->toBool() ){
			$this->add("result",$args->pms_file_srl);
			$this->add("pms_file_srl" , $args->pms_file_srl);
		}else{
			$this->add("result","error");
		}
	}

		/* 파일을 다운로드 한다. . . */
	function procPmsFileDownload(){

		$args->pms_file_srl = Context::get('pms_file_srl');
		$output = executeQueryArray("pms.getFileInfo",$args);
		if(!$output->data[0]) return ;
		$filepath = _XE_PATH_."/files/pms/". $output->data[0]->uploaded_filename ; //'./hello_world.txt';

		$filesize = $output->data[0]->file_size;//filesize($filepath);
		//$path_parts = pathinfo($filepath);
		$filename =  $output->data[0]->source_filename ;
		//$extension = $path_parts['extension'];

		header("Pragma: public");
		header("Expires: 0");
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: $filesize");

		ob_clean();
		flush();
		readfile($filepath);
	}
	/** 금주의 데이터를 갱신 한다.   */
	function procPmsAnalyzeReport(){
		$oModel = &getModel('pms');
		$args->mid = Context::get('mid');
		$args->act = Context::get('ret_act');
		$args->report_date = Context::get('report_date');
		//$args->dt = date('Y-m-d');
		
		if(!$args->report_date) $args->dt = date('Y-m-d');
		else{ $args->dt = $args->report_date ; }

		
		
		$output = $oModel->analyzeReport( $args  );
debugPrint($output );		
		//$output = $oModel->analyzeReportRoutine( $args  );
		//$output = $oModel->analyzeReportMantis( $args  );
debugPrint( $output );		
		$this->add('success','0'); 

		$this->setRedirectUrl( "/?mid=".$args->mid."&act=dispPmsWorkProgressView&report_date=".$args->report_date );
	} 
	/* 금주의 일상 업무 데이터 갱신 */
	function procPmsAnalyzeRoutineReport(){
		$oModel = &getModel('pms');
		$args->mid = Context::get('mid');
		$args->act = Context::get('ret_act');
		$args->report_date = Context::get('report_date');
		if(!$args->report_date) $args->dt = date('Y-m-d');
		else{ $args->dt = $args->report_date ; }
		$output = $oModel->analyzeReportRoutine( $args  );
		$this->setRedirectUrl( "/?mid=".$args->mid."&act=dispPmsWorkRegularView&report_date=".$args->report_date );
	}	
	/* 금주의 맨티스 데이터 통계 작성   */
	function procPmsAnalyzeMantisReport(){
		$oModel = &getModel('pms');
		$args->mid = Context::get('mid');
		$args->act = Context::get('ret_act');
		$args->report_date = Context::get('report_date');
		if(!$args->report_date) $args->dt = date('Y-m-d');
		else{ $args->dt = $args->report_date ;} 
		$output = $oModel->analyzeReportMantis($args);
		$this->add('success','0'); 
		$this->setRedirectUrl( "/?mid=".$args->mid."&act=dispPmsWorkProgressView&report_date=".$args->report_date );
	}

	
	function procPmsWmccodeSave(){
		$args = Context::getRequestVars();

		if( $args->code_int_val ) {
			$output = executeQuery("wmccode.updateCode", $args);
		}
		
		$this->setRedirectUrl( $args->error_return_url );
		
	}
	/*  결재 진행후 결과 상태를 가져온다.   */
	function procPmsEapprvProc(){
debugPrint(" procPmsEapprvProc 를 구현해 주세요.... ");
	}
	/* 관련된 문서를 저장한다.  */
	function _saveDocument( $obj ){
		// generate document module model object
		$oDocumentModel = getModel('document');
		// generate document module의 controller object
		$oDocumentController = getController('document');
		// check if the document is existed
		$oDocument = $oDocumentModel->getDocument($obj->document_srl, $this->grant->manager);

		$is_update = false;
		if($oDocument->isExists() && $oDocument->document_srl == $obj->document_srl)
		{
			$is_update = true;
		}

		// update the document if it is existed
		if($is_update)
		{

			// modify list_order if document status is temp
			if($oDocument->get('status') == 'TEMP')
			{
				$obj->last_update = $obj->regdate = date('YmdHis');
				$obj->update_order = $obj->list_order = (getNextSequence() * -1);
			}
			$output = $oDocumentController->updateDocument($oDocument, $obj);
			//$msg_code = 'success_updated';

		// insert a new document otherwise
		} else {
			$output = $oDocumentController->insertDocument($obj, $bAnonymous);
			//$msg_code = 'success_registed';
			$obj->document_srl = $output->get('document_srl');
		}
		return $obj;
	}

	function _addLog( $obj ){
		$output = executeQuery("pms.insertComment", $obj ); // title , content , type , task_srl , project_srl
	}
	
	/** DocumentComment 를 저장한다.  */
	function _saveComment($obj){
		$oDocumentModel = getModel('document');
		$oDocument = $oDocumentModel->getDocument($obj->document_srl);
		if(!$oDocument->isExists())
		{
			return new Object(-1,'msg_not_founded');
		}
		// generate comment  module model object
		$oCommentModel = getModel('comment');
		// generate comment module controller object
		$oCommentController = getController('comment');
		// check the comment is existed

		// if the comment is not existed, then generate a new sequence
		if(!$obj->comment_srl)
		{
			$obj->comment_srl = getNextSequence();
		} else {
			$comment = $oCommentModel->getComment($obj->comment_srl, $this->grant->manager);
		}

		// if comment_srl is not existed, then insert the comment
		if($comment->comment_srl != $obj->comment_srl)
		{
			// parent_srl is existed
			if($obj->parent_srl)
			{
				$parent_comment = $oCommentModel->getComment($obj->parent_srl);
				if(!$parent_comment->comment_srl)
				{
					return new Object(-1, 'msg_invalid_request');
				}

				$output = $oCommentController->insertComment($obj, $bAnonymous);

			// parent_srl is not existed
			} else {
				$output = $oCommentController->insertComment($obj, $bAnonymous);
			}
		// update the comment if it is not existed
		} else {
			// check the grant
			if(!$comment->isGranted())
			{
				return new Object(-1,'msg_not_permitted');
			}
			$obj->parent_srl = $comment->parent_srl;
			$output = $oCommentController->updateComment($obj, $this->grant->manager);
			$comment_srl = $obj->comment_srl;
		}
debugPrint( $output );
		if(!$output->toBool())
		{
			return $output;
		}
		$this->setMessage('success_registed');
		$this->add('mid', Context::get('mid'));
		$this->add('document_srl', $obj->document_srl);
		$this->add('comment_srl', $obj->comment_srl);
		$this->add('note_srl', $obj->note_srl);
	}



}/* End of file pms.controller.php */
/* Location: ./modules/pms/pms.controller.php */
