<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * @class  tagAdminController
 * @author Brian (admin@watv.org)
 * @brief admin controller class of the pmsk
 */
class pmsAdminController extends pms
{
	/**
	 * @brief Delete all tags for a particular module
	 */
	function procPmskAdminFsvrAdd(){
	
		$args = Context::getRequestVars();
		//$args->ip = $_SERVER['REMOTE_ADDR']  ;
		$args->user_id ="관리자";
		$args->module_srl = $module_srl;
debugPrint( $args );			
	
		if( $args->fsvr_srl == 0 ) $args->fsvr_srl = null ;

		if($args->fsvr_srl ) {
			$output = executeQuery("pmsk.updateFsvr" , $args );
			$args->memo ="서버 갱신: server->". $args->fsvr_srl . " name ". $args->srv_name . "_".$args->svr_desc ; 
			$output = executeQuery("pmsk.addLog",$args );
		}else{
			$output = executeQuery("pmsk.addFsvr" , $args );
			$args->memo ="서버 추가: server->". $args->fsvr_srl . " name ". $args->srv_name . "_".$args->svr_desc ; 
			$output=executeQuery("pmsk.addLog",$args );
			
		}
debugPrint( $output );
		$this->setRedirectUrl("/index.php?module=admin&act=dispPmskAdminFsvrList");
	}
	
	function procPmskAdminClubAdd(){
		$args = Context::getRequestVars();
		$args->user_id ="관리자";
debugPrint( $args );		

		if( $args->club_srl == 0 ) $args->club_srl = null ;

		if($args->club_srl ) {
			$output = executeQuery("pmsk.updateClub" , $args );
			$args->memo ="클럽 갱신: id->". $args->club_name . ": club_srl".$args->club_srl  . " ". $args->club_name  ;
			executeQuery("pmsk.addLog",$args );
		
		}else{
			$output = executeQuery("pmsk.addClub" , $args );
			$args->memo ="클럽 추가: samba_id->". $args->smb_id . ": club_name".$args->club_name   ;
			executeQuery("pmsk.addLog",$args );
		}
		
		debugPrint( $args );
		debugPrint( $output );


		$this->setRedirectUrl("/index.php?module=admin&act=dispPmskAdminClubList");	

	}
	
	function procPmskAdminFsvrDel(){
		
		$args = Context::getRequestVars();
		$args->ip = $_SERVER['REMOTE_ADDR']  ;
		$args->module_srl = $module_srl;
		$args->user_id ="관리자";
		executeQuery("pmsk.deleteFsvr" , $args ); 
		
		$args->memo ="서버 삭제 : id ". $args->user_id . ": fsvr_srl".$args->fsvr_srl   ;
		$output = executeQuery("pmsk.addLog",$args );
		
		$this->setRedirectUrl("/index.php?module=admin&act=dispPmskAdminFsvrList");	
	}
	
		function procPmskAdminClubDel() {
	
		$args = Context::getRequestVars();
		$args->ip = $_SERVER['REMOTE_ADDR']  ;
		$args->user_id = "관리자";
		$output = executeQuery( "pmsk.deleteClub",$args );
		$args->memo ="클럽 삭제  : club_srl".$args->club_srl   ;
		executeQuery("pmsk.addLog",$args );
		$this->setRedirectUrl("/index.php?module=admin&act=dispPmskAdminClubList");	
	}
	
	function procPmskAdminPrivAdd(){
		
		header("content-type:text/html; charset=utf-8");
		$args = Context::getRequestVars();
		$args->ip = $_SERVER['REMOTE_ADDR']  ;
		
		$args->memo ="권한 추가 : id ". $args->user_id . ": club_srl".$args->club_srl   ;
//debugPrint( $args);		
		$output = executeQuery("pmsk.addLog",$args );
//debugPrint( $output ); 		
		$output = executeQuery('pmsk.addPriv' , $args );
		
		$msg ="OK";
		$this->add("result",$output->error);
		$this->add("user_id", $args->user_id);
		$this->add('memo' , $output->message);
		$this->setMessage('MSG' , $msg);
	}
	
	function procPmskAdminPrivDel(){
		$args = Context::getRequestVars();
		$args->ip = $_SERVER['REMOTE_ADDR']  ;
debugPrint( $output ); 

		$user_id = explode( ",",$args->user_id );
debugPrint( $user_id);		
		
		$args->memo ="권한 삭제  : id->". $args->user_id . ": club_srl".$args->club_srl   ;
		executeQuery("pmsk.addLog",$args );
		
debugPrint($args ); 

		foreach( $user_id  as $key => $val  ){
			$obj->club_srl = $args->club_srl ;
			$obj->user_id = $val ;
			$output = executeQuery( "pmsk.deletePriv",$obj );
			debugPrint($output );	
		}
		
		
		$msg ="성공";
		$this->add("result", "0");
		//$this->add("user_id", $args->user_id);
		$this->setMessage('MSG' , $msg);
	}
}/* End of file tag.admin.controller.php */
/* Location: ./modules/tag/tag.admin.controller.php */