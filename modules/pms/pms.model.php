<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * @class  tagModel
 * @author NAVER (developers@xpressengine.com)
 * @brief tag model class of the module
 */
class pmsModel extends pms
{

	public function triggerModuleListInSitemap(&$arr)
	{
		array_push($arr , 'pms');
	}
/*
		통계를 낸다  개인별 
*/		
	function getReportWorkTime($args)
	{
debugPrint("dddd");		
		if( !$args->page)  $args->page=1;
		if( !$args->page_size ) $args->page_size = 100 ; 
		if( !$args->site_srl )  $args->site_srl = 0 ; 

		if( !$args->start_date) {
			$dt = new DateTime('last Sunday');
			$args->start_date =    date_format( $dt ,"Y-m-d" )  ;
			$args->end_date =      date_format( $dt->modify('7 days') ,"Y-m-d"   )  ;  // 1주일을 더한다. 
		}  
debugPrint( $args );		
		$query_str->query_head = " select project_name , task_name , nick_name, ifnull (sum(MON) ,0) AS MON,  ifnull (sum(TUE) ,0)  AS TUE   ,  ifnull (sum(WED) ,0)  as WED,  ifnull (sum(THU) ,0) as THU  , ifnull ( sum(FRI) ,0) as FRI ";
		//$count_head = " select count(0) as count ";
		$query_str->querystr = " From xe_pms_report_time_view  as view   ";
		$query_str->querystr .= " Where member_srl = ". $args->member_srl."    and work_date between '". $args->start_date."' and '".$args->end_date."' ";   // D는 폐기된것임. 
		$query_str->query_tail = " group by project_name ,task_name ,nick_name ";
debugPrint( $query_str );		
		// $query_str->querystr = sprintf(  $query_str->querystr , $args->project_srl );
		return $this->getExecuteQuery( $args , $query_str);
	}
/*
	통계를 낸다  프로젝트별 , 주별   
*/
	function getReportWorkTimeWeeklyByProject( $args){
		
		$query_str->query_head =	"select   p.title ";
		$query_str->query_head .=			",t.title   ";
		$query_str->query_head .=			", adddate( work_date  , INTERVAL 1-DAYOFWEEK(work_date ) DAY) WeekStart ";
		$query_str->query_head .=			", sum(w.work_time) ";
		$query_str->querystr =	"from xe_pms_project p  ";
		$query_str->querystr .=		"left join xe_pms_report_work w On ( p.project_srl = w.project_srl  ) ";
		$query_str->querystr .=		"left join xe_pms_task t On ( w.task_srl = t.task_srl )  ";
		$query_str->querystr .=		"left join xe_member m On ( w.member_srl = m.member_srl )  ";
		$query_str->querystr .=	"where p.project_srl = %d ";
		$query_str->query_tail =	"group by p.title ,t.title  , WeekStart  ";
		
		$query_str->querystr = sprintf(  $query_str->querystr , $args->project_srl );
		return $this->getExecuteQuery( $args , $query_str);
	}
	
	
	
/*
	통계를 낸다  프로젝트별  , 인별 
*/
	function getReportWorkTimeByProject( $args){
		
		$query_str->query_head =	"select p.title ,t.title  , m.nick_name ,  sum(w.work_time) AS work_time ";

		$query_str->querystr =	"from xe_pms_project p  ";
		$query_str->querystr .=		"left join xe_pms_report_work w On ( p.project_srl = w.project_srl  ) ";
		$query_str->querystr .=		"left join xe_pms_task t On ( w.task_srl = t.task_srl ) ";
		$query_str->querystr .=		"left join xe_member m On ( w.member_srl = m.member_srl )  ";
		$query_str->querystr .=	"where p.project_srl = %d ";
		$query_str->query_tail =	"group by p.title ,t.title  , m.nick_name  ";

		$query_str->querystr = sprintf(  $query_str->querystr , $args->project_srl );
		return $this->getExecuteQuery( $args , $query_str);
	}
	
	
/**
	프로젝트별로만 통계를 낸다.  모든 프로젝트에 대한 결과값이 나온다. 
	하나님의교회 소개 웹사이트 개편	2015-04-26	3
	하나님의교회 소개 웹사이트 개편	2015-06-07	24
*/
	function getReportWorkTimeAllProjectWeeklyByProject($args){
		// FULL =="Y" 이면 진행 되지 않은 프로젝트도 포함 한다. 
		
		$query_str->query_head =	"select p.title ,t.title ,   adddate( work_date  , INTERVAL 1-DAYOFWEEK(work_date ) DAY) WeekStart   , m.nick_name  , sum(w.work_time) ";
		$query_str->querystr    =	"from xe_pms_project p  ";
		$query_str->querystr     .=	"	left join xe_pms_report_work w On ( p.project_srl = w.project_srl  ) ";
		$query_str->querystr    .=	"	left join xe_pms_task t On ( w.task_srl = t.task_srl )  ";
		$query_str->querystr      .=	"	left join xe_member m On ( w.member_srl = m.member_srl )  ";
		$query_str->querystr    .=	"group by p.title ,t.title    ,WeekStart  , m.nick_name ";	
		if( !$args->FULL ) $query_str->querystr    .= " having WeekStart is not null";	
		return $this->getExecuteQuery(  $args , $query_str);	
		
		
	}
	/*  개인의 업무 시간을 표로 보기 **/	
	function getPersonalWorkViewTable( $args ){
		$query_str =  sprintf(" call getPersonalWorkReport( %d , '%s' ) " , $args->member_srl , $args->work_date) ;
		return $this->getExecuteProcedure( $query_str );
	}
	
	/*** 추진 업무 통계 잡기 */
	function getProjectWorkViewTable( $args ){
		$query_str = sprintf("call getWeeklyReportByProject('%s' , '%s')",  $args->dt , $args->gu3);
debugPrint( $query_str );		
		return $this->getExecuteProcedure( $query_str );
	}
	/*** 일상 업무 통계 잡기 */
	function getProjectRoutineTable( $args ){
		$query_str = sprintf ("call getWeeklyRoutineReportByWeek('%s');" , $args->dt) ;
debugPrint( $query_str );
		return $this->getExecuteProcedure( $query_str );
	}
	
	/*  HELP 통계를 낸다... 데이터는 매주 가져와야 한다. 김창운님.  */
	function getProjectHelpViewTable( $args ){
		$query_str = sprintf("call getHelpReportByDept('%s' , %d)",  $args->dt , $args->area);

		return $this->getExecuteProcedure( $query_str );
	}
	
	
	/**  통계 테이블에 데이터를 새로 집어 넣는다.  */
	function analyzeReportMantis( $args ){

		$query_str = sprintf("call calcMantisReport('%s')",  $args->dt );
		$output = $this->getExecuteProcedure( $query_str );

		return $output ;
	}
	function analyzeReport( $args){
		$query_str = sprintf("call closingWeeklyReport('%s')",  $args->dt );
 		$output = $this->getExecuteProcedure( $query_str );
		return $output ;
	}
	function analyzeReportRoutine( $args){
		$query_str = sprintf("call closingWeeklyRoutineReport('%s')",  $args->dt );
		$output = $this->getExecuteProcedure( $query_str );
		return $output ;
	}
 
	function getExecuteProcedure( $query_str ){
		$oDB = &DB::getInstance();
		$query  = $oDB->_query($query_str ); 
		$result = $oDB->_fetch($query );  
		$output->data = $result;
		if(  is_array ($result)  ) {
			$output -> data = $result ;
		}else {
			$arr = array();
			$arr[0] = $result ;
			$output->data = $arr ;
		}
		return $output ;
	}
	function getExecuteQuery($args  , $query_str){
		// 이건 중앙에서 만든 모듈만 뽑는다. 즉 m.site_srl = 0 인것만 뽑는다. 
		//  m.site_srl = 0  이 아닌것들은   각 카페에서 만든 리스트 이다. 
	
		if( !$args->page)  $args->page=1;
        if( !$args->page_size ) $args->page_size = 20 ; 
		if( !$args->site_srl )  $args->site_srl = 0 ; 
		
		if(!$query_str->count_head)$query_str->count_head ="       select count(0) as count ";
		$query_str->query_tail .=" limit  " . ($args->page -1) * $args->page_size ." , " . $args->page * $args->page_size  ;
	
		$oDB   = &DB::getInstance();
		$query =$oDB->_query($query_str->query_head . $query_str->querystr . $query_str->query_tail  );	

		$result = $oDB->_fetch( $query );
		$output -> data = $result ;
		if(  is_array ($result)  ) {
			$output -> data = $result ;
		}else {
			$arr = array();
			$arr[0] = $result ;
			$output -> data = $arr ;
		}
		$query= $oDB->_query( $query_str->count_head . $query_str->querystr ) ;
		$result = $oDB->_fetch($query);
		
		$output->query        = $query_str->query_head . $query_str->querystr . $query_str->query_tail ;
		$output->page 		 = $args->page ;
		$output->total_count = $result->count; 
		$output->total_page  = round (  $output->total_count  / $args->page_size)  +1 ;
		$output->page_size  = $args->page_size ;
		$oPageHandler = new PageHandler(  $output->total_count,  $output->total_page   ,  $output->page );
		$output -> page_navigation = $oPageHandler ;
		return $output ;
	}
	
	
}/* End of file pms.model.php */
/* Location: ./modules/pms/pms.model.php */
