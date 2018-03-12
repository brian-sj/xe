<?php
    /**
     * @class  bookmarkAdminView
     * @author XE스쿨 북마크 모듈 만들기 예제
     * @brief  bookmark 모듈의 admin view class
     **/

class eapprvAPI extends eapprv {
		
	function dispEapprvUsers( &$oModule ){
		
		$oModel = &getModel('eapprv');
		$output = $oModel->getUsers( $args );
		$arr = array();
		foreach(   $output->data as  $val){
			$a = new stdClass();
				$a->user =$val->user_id;								
				$a->name =$val->nick_name;					
				$a->email =$val->email_address;							// = {'first':'kang' ,'middle': 'myoung' ,'last': 'hee'};
				$a->lsn   = 100;
				$a->dept = $val->home_page ;
				$a->member_srl = $val->member_srl ;
				//$oModule->add('hello','world');
				//$oModule->add('brian','kang');
				array_push($arr , $a ) ;
				//$oModule->add($val->user_id  , $a);
	
		}
			$oModule->add('items' , $arr );
			debugPrint( $oModule);			
	}
	function dispEapprvUsersByGroup( &$oModule  ){
	   /**				API  로 갑니다....     */
	
	   $args = Context::getRequestVars();
		if( $args->group_srl )  $args->group_srl = 314 ;
	   $output = executeQuery('eapprv.getMembersByGroup',$args); 
	   
	   $arr = array();
		foreach( $output -> data as $val ) {
			$a = new stdClass();
				$a->member_srl  =   $val->member_srl ;
				$a->name          =  $val->name ;
				$a->dept            = $val->home_page ;
				array_push( $arr , $a );
		}
		$oModule->add('items' , $output->data );
	}
	
	function dispEapprvSharedByPreAPI( &$oModule ){
		
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl ;
		//if( $args->eapprv_share_pre_srl ) $output = executeQuery('eapprv.getEapprvSharedListByPre' , $args ) ;
		if( $args->eapprv_share_pre_srl ) $output = executeQuery('eapprv.getEapprvSharePres' , $args ) ;



		$members = explode(',',$output->data->shared_members);
		$oModule->add('items', $members );
debugPrint( $args ); 		
debugPrint( $members); 		
debugPrint( $output );		
	}
	
	function dispEapprvLineByPreAPI ( &$oModule ){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl ;
		if( $args ->eapprv_line_pre_srl  ) $output = executeQuery('eapprv.getEapprvLinePres' , $args );

		$members = array();
		array_push( $members , $output->data->line1 , $output->data->line2 , $output->data->line3 , $output->data->line4 , $output->data->line5 );

debugPrint( $members); 		
debugPrint( $output );
		$oModule->add('items',$members );
debugPrint( $oModule);		
	}
	
	
	
	
}