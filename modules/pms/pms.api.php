<?php
class pmsAPI extends pms
{
	
	/* 
	*@ 만약에 일상 업무라고 한다면 완료되고 1개월 넘은것은 뺀다.
	*/
	function dispPmsProjectWorkListAPI(&$oModule  ){

		$links = array();
		$data = array();

		$args->project_srl = Context::get("project_srl");
		$output = executeQueryArray('pms.getTasks',$args);
		foreach( $output->data as $key => $val){
			$obj = new StdClass();
			$obj->id = $val->task_srl;
			$obj->parent =  $val->parent_task_srl ;
			$obj->text = $val->title;
			$sdt = new DateTime($val->start_date) ;
			$edt = new DateTime($val->end_date);
			$obj->planed_mh = $val->planed_mh ;
			$obj->mh = $val->mh ; 
			try{$obj->progress = $val->progress/100; } catch(Exception $e){ $obj->progress = 0;} // 혹 에러 나면 0 
			$obj->open = true;
			//$obj->users =  explode(',',  $val->users );
			$obj->work_member_srl =  $val->work_member_srl ;
			$obj->member_srl =  $val->member_srl ;
			
			$obj->code_urgency = $val->code_urgency?$val->code_urgency:1 ;
			$obj->code_importance = $val->code_importance?$val->code_importance:1 ; 
			$obj->sort_order = $val->sort_order; 
			$obj->memo = $val->memo; 
			$obj->stt = $val->stt; 
			
			// stt== P project 이거는 시작일 끝날이 없음.  대신 자식이 없는 project는 에러를 냄....  
			if( $val->stt =="P"){
				$obj->type = "project" ;
				$obj->open = true ; 
				//$obj->start_date  = $sdt->format('d-m-Y') ;
			}else if( $val->stt =="M"){
				/// milestone 임  
				$obj->type = "milestone";
				$obj->start_date  = $sdt->format('d-m-Y') ;
			}else{
				// 일반 타스크는 시작일 끝일이 있음. 
				$obj->type="task"; 
				$obj->start_date  = $sdt->format('d-m-Y') ;
				$dInterval = date_diff( $sdt , $edt ); 
				$obj->duration = $dInterval->days ;//date_diff($edt , $sdt ) ; 
			}
			//$obj->priority = 2; 
			array_push($data , $obj );
		}
		
		$output = executeQueryArray('pms.getTasksLinks',$args);	
		foreach( $output->data as $key => $val){
			$obj = new StdClass();
			$obj->id = $key;
			$obj->source = $val->source_task_srl ; 
			$obj->target = $val->target_task_srl ;
			$obj->type =$val->type  ;
			array_push($links , $obj );
		}
		$oModule->add('data' , $data );
		$oModule->add('links' , $links  );
		$oModule->add('data',$data);
	}
	
	/** Project List 를 가져 온다....   **/
	function dispPmsProjectListAPI(&$oModule){

		$args = Context::getRequestVars();
		$args->project_srl = "";
		$data = array();
		$logged_info = Context::get("logged_info");
		if( $logged_info->is_admin && $args->member_srl ) {
			//  관리자인 경우는 남의 member_srl을 받는다.  
		}else{
			$args->member_srl = $logged_info->member_srl ;	
		}
		$args->list_count = 100;
		/// 즐겨 찾기 업무인 경우 pms_project_category 테이블을 뒤저야 한다. 
		if( $args->category ==   __PMS_CATEGORY_OUT_FAVORITE__ ){
			$args->project_srl = null ;
			$output = executeQueryArray("pms.getProjectFavoriteList" , $args);
			//$output = executeQueryArray("pms.getProjectsFavorite" , $args);
		}else if( $args->category ==   __PMS_CATEGORY_OUT_MYDUTY__ ){  // Z 내 업무 리스트는요 특별한 항목 입니다. 
			//$output = executeQueryArray("pms.getProjectsMyDuty" , $args );
			$args->category = null ;
			$output = executeQueryArray("pms.getProjectBySharedNocode",$args , array("p.project_srl", "p.category" ,"p.title"));
		}else{  /// 나머지는 그냥 간다.. 
			//$output = executeQueryArray("pms.getProject" , $args );
			$output = executeQueryArray("pms.getProjectBySharedNocode",$args , array("p.project_srl", "p.category" ,"p.title"));
		}
 		foreach( $output->data as $key => $val){	
			$obj = new stdClass();
			$obj->project_srl = $val->project_srl ;
			$obj->title = $val->title ;
			$obj->category = $val->category ;
			array_push( $data , $obj );
		}
		$oModule->add('data',$data);
	}
	
	/*** Task List 를 가져 온다.  tree  구조로 가져 온다.  ***/
	function dispPmsTaskListAPI( &$oModule ){

		$args=Context::getRequestVars();
		$output = executeQueryArray("pms.getTasks" , $args ); 
		$data = array();
		foreach( $output->data as $key => $val){	
			$obj = array( $val->task_srl  ,  $val->parent_task_srl ,  $val->title
					, $val->start_date , $val->end_date , $val->code_urgency 
					, $val->code_importance , $val->memo , $val->sort_order );
			//$obj->task_srl = $val->task_srl  ;
			//$obj->parent = $val->parent_task_srl;
			//$obj->title = $val->title ;
			array_push( $data , $obj );
		}
		$oModule->add('data',$data);
	}
	
	function dispPmsReportWorkTimeAPI(&$oModule){
		//// Input 일시 , 프로젝트 srl ., task_srl 
		//// Output => 업무 시간과 진행률 
		$args=Context::getRequestVars();
		$logged_info = Context::get('logged_info');

		if( $logged_info->is_admin && $args->member_srl ) {
			//  관리자인 경우는 남의 member_srl을 받는다.  
		}else{
			$args->member_srl = $logged_info->member_srl ;	
		}
		
		$args->type = __PMS_COMMENT_TYPE_DAILY_REPORT__;
		$output = executeQuery("pms.getReportWorkTimeByDate" , $args );

		$mh = $output->data->mh; 
		$report_work_srl = $output->data->report_work_srl ;
		$extra_vars = unserialize($output->data->title) ;		
		$count = $output->data->count ;
		$count= $count?$count :0;
		$output = executeQuery("pms.getTaskDetail" , $args );		
		$progress = $output->data->progress;
		$stt = $output->data->stt ;

		$oModule->add('count' , $count);
		$oModule->add('stt' , $stt);
		$oModule->add('progress' , $progress);
		$oModule->add('mh',$mh);
		$oModule->add('report_work_srl',$report_work_srl);
		
		$oModule->add('extra_vars',$extra_vars);
		// $oModule->add('pmh',$extra_vars->pmh);
		// $oModule->add('amh',$extra_vars->amh);
		// $oModule->add('content',$extra_vars->title);
		// $oModule->add('p',$extra_vars->p);
		// $oModule->add('dt',$extra_vars->dt);
		
		// 일일 한줄 보고 
		/* 일정은 한개 밖에 없지만 루프 돌린다.
		 2016년 8월 더이상 comment 테이블을 뒤지지는 않는다. 9월부터는 이 코드를 뺍시다. 
		*/
		/*
		$args->reg_date = str_replace('-','' , $args->work_date);
		
		if( $args->report_work_srl == 0) $args->report_work_srl = null  ;
		$output = executeQueryArray('pms.getCommentByday', $args);

		foreach( $output->data as $key => $val){
			$oModule->add('content', $val->content );
			$oModule->add('comment_srl', $val->comment_srl );
		}
		*/

	}
	
	function dispPmsBySubCodeAPI(&$oModule){
		$args=Context::getRequestVars();
		$output = executeQueryArray('wmccode.getCodeListBySubCode' , $args ); 
		$oModule->add('code' , $output->data);
	}
	/**
	* 추진 업무 보고서 
	*/
	function dispPmsWeeklyReportProjectAPI(&$oModule){
		
		$oModel = &getModel("pms");
		$args = Context::getRequestVars();
		if(!$args->report_date) $args->dt = date('Y-m-d');
		else{ $args->dt = $args->report_date ; }
		// $args->gu3 = Context::get('gu3');
debugPrint( $args );
		
		$output =$oModel->getProjectWorkViewTable($args);
		$data = array();
		//제목 , 주담당 , 시작일 , 종료일 , 전주 , 금주 , 누적 , 진행율 
		foreach( $output->data as $key => $val){	
			
			$obj = array( cut_str($val->title,58) ,  $val->user_name , $val->start_date , $val->end_date 
					, $val->lastweek_workhour , $val->thisweek_workhour 
					, $val->thisweek_accumulation ,$val->progress , $val->lastweek_progress );
			if( !$args->remove_blank && $val->thisweek_workhour >0 ) {
				array_push( $data , $obj );	
			}
			
		}
		$oModule->add('data',$data);
		$oModule->add('gu3', $args->gu3);
	}
	/** 
	* 일상 업무 보고서    
	* remove_blank  를 Y 로하면 시간 없는 것도 나온다. 
	*/
	function dispPmsWeeklyReportRoutineProjectAPI(&$oModule){
		$oModel = &getModel('pms');
		$args = Context::getRequestVars();
		if(!$args->report_date) $args->dt = date('Y-m-d');
		else{ $args->dt = $args->report_date ; }

		$output = $oModel->getProjectRoutineTable($args);
debugPrint(  $output  );				
		$data = array();
		foreach( $output->data as $key=>$val ){
			$obj = array( $val->weekday , $val->project_srl , $val->project_name 
							, $val->task_srl , $val->task_name  
							, $val->thisweek_work_hour_sum , $val->thisweek_cnt_sum 
							, $val->lastweek_work_hour_sum , $val->lastweek_cnt_sum);
							
			if( !$args->remove_blank && $val->thisweek_work_hour_sum >0 ) {
				array_push( $data , $obj );	
			}				
		}
		$oModule->add('data',$data);
	}
	function dispPmsWeeklyReportHelpAPI( &$oModule){
		$oModel = &getModel("pms");
		$args = Context::getRequestVars();
		if(!$args->report_date) $args->dt = date('Y-m-d');
		else{ $args->dt = $args->report_date ;}
		$data = array(); 
	
		// kind2 =>   0 전체  1 국내 2 총회 3 해외 
		if(!$args->area) 
		$args->area = 0;
		$output = $oModel->getProjectHelpViewTable($args);
		foreach( $output->data as $key => $val  ){
			$obj = NULL;
			$obj->sum = $val->sum ;$obj->cnt = $val->cnt ;$obj->memo = $val->memo ;$obj->kind2 = $val->kind2 ;$obj->area = $args->area ;
			array_push( $data , $obj );
		}
		$oModule->add('data', $data);
	}
	
	/* 이번주 한사람이 몇시간 업무 일지 등록 했는지 보여주는 함수  */
	function dispPmsWeeklyReportWorkTimeByMemberSrl(&$oModule){
		$args = Context::getRequestVars();
		if(!$args->report_date) $args->dt = date('Y-m-d');
		else{ $args->dt = $args->report_date ;}
		
		$d = new DateTime( $args->dt );
		$args->start_date = $d->modify('last Sunday')->format('Y-m-d');
		$args->end_date = $d->modify('next Sunday')->format('Y-m-d'); 
		
		$output = executeQuery('pms.getReportWorkTimeWeeklyByMemberSrl', $args );
		if( !$output->data->sum_mh ) $output->data->sum_mh = 0;
		$oModule->add('sum_mh' , $output->data->sum_mh);
		$oModule->add('member_srl' , $args->member_srl);
		
	}
	
	
}
?>