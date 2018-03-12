<?php
class pmsAPI extends pms
{
	function dispPmsProjectWorkListAPI(&$oModule  ){
debugPrint("sssss");
/*
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
			
			try{$obj->progress = $val->progress/100; } catch(Exception $e){ $obj->progress = 0;} // 혹 에러 나면 0 
			$obj->open = true;
			$obj->users =  array("John", "Mike", "Anna");
			//$obj->priority = 2;
			$obj->start_date  = $sdt->format('d-m-Y') ;
			$dInterval = date_diff( $sdt , $edt ); 
			$obj->duration = $dInterval->days ;//date_diff($edt , $sdt ) ;
			array_push($data , $obj );
		}
			$obj = new StdClass();
			$obj->id = 1;
			$obj->source =1 ;
			$obj->target = 2 ;
			$obj->type =1 ;
		array_push($links , $obj );			
		$oModule->add('data' , $data );
		$oModule->add('links' , $links  );
*/
	}
	
	/** Project List 를 가져 온다....   **/
	function dispPmsProjectListAPI(&$oModule){
debugPrint("dddd");		

		$args = Context::getRequestVars();
		$args->project_srl = "";
		$data = array();

		$output = executeQueryArray("pms.getProject" , $args );

		foreach( $output->data as $key => $val){	
			$obj = new stdClass();
			$obj->project_srl = $val->project_srl ;
			$obj->title = $val->title ;
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
			$obj = new stdClass();
			$obj->task_srl = $val->task_srl  ;
			$obj->parent = $val->parent_task_srl;
			$obj->title = $val->title ;
			array_push( $data , $obj );
		}
debugPrint( $output );		
		$oModule->add('data',$data);
		
	}
}
?>