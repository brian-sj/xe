<?php
class eapprvController extends eapprv
{
	/**
	*	 결재선을 추가 등록함. 
	*/
	function procEapprvLineInsert(){
		$args = Context::getRequestVars();

		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;
		$args->line_title ="결재선";
		$oModel = &getModel('eapprv');
		$output =$oModel -> insertUpdateEapprvLine( $args ); // Update Insert 둘다 함
		
		debugPrint( $logged_info );
		debugPrint($output );
		debugPrint( $logged_info->member_srl ); 
		debugPrint($args );
	   //$url = "?module=admin&act=dispBookcAdminInsert&module_srl=".$args->module_srl ."&user_yn=".$args->user_yn ;
	   $this->setRedirectUrl(    $args->error_return_url  );
	}

	/**
	*	 결재를 진행을 위해서 결재선정보를 load 함. 
	*   1) eapprv          ->  에 추가하고 
	*   2) eapprv_proc  ->  에다가 loading 함. 
	*/
	function procEapprvLoadProcess(){
		$args = Context::getRequestVars(); 
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;
		$args->module='eapprv';
		$oModel = &getModel('eapprv');
		//   args 1. member_srl      2. eapprv_line_srl  3. document_srl  세개가 넘어 갑니다. 		
		$output =$oModel ->getEapprvLine( $args );

		$line = json_decode( $output->data[0]->line_content  );
		
		if(!$line){$msg="ERROR : 결재선이 잘못되었습니다. 관리자에게 문의 바랍니다. " ; };
		
		// 1. 결재를 태운다.    결재 번호를 받아 온다. ... 
		// 2. 결재자를 세운다. 

		$args->eapprv_srl = getNextSequence() ;
		$args->stt =  APP_PROCESS ;      //   P start  ,    Reject , Approved ,    N no                                한글 번호 ...   00000023
		$output = $oModel->insertEapprv( $args);
		//  항상 입력 입니다. 	
		// dept , user member_srl , title 
		$i = 0 ;
		foreach( $line as $val )
		{
			$i++;
			$args->order = $i ; 
			$args->stt =  $i ==1? APP_PROCESS : APP_NOTINITIALIZED;
			$args->member_srl   = $val->member_srl ;        
			$args->eapprv_user =   json_encode( $val );   
			
			$args->eapprv_proc_srl = getNextSequence();
			$output =$oModel->loadLine( $args );
		}
		$this->setRedirectUrl(  $args->error_return_url  );
	}

	/**
	*   
	*/
	function procEapprvLineSettingSave(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;
		
		$output = executeQuery("eapprv.deleteEapprvLinePre", $args );
		if ($args->eapprv_line_pre_srl=='') $args->eapprv_line_pre_srl = getNextSequence();
		$output = executeQuery("eapprv.insertEapprvLinePre", $args );

		$this->setRedirectUrl(  $args->error_return_url  );
		
	}

	/*@ 
	* 	설정을 저장한다.   권한 체크 필요 없음.
	*/
	function procEapprvShareSettingSave(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;
		
		$args->shared_members_str = implode(',', $args->shared_members);
		
		$output = executeQuery("eapprv.deleteEapprvSharePre", $args );
		if ($args->eapprv_share_pre_srl=='') $args->eapprv_share_pre_srl = getNextSequence();
		$output = executeQuery("eapprv.insertEapprvSharePre", $args );
		$this->setRedirectUrl(  $args->error_return_url  );		
	}

	/*@ 
	* 	정보를 저장한다.   
	*/	
	function procEapprvShareListSave(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;
		
		$output = executeQuery( 'eapprv.getEapprv' , $args ); 
		$oMemberModel = &getModel('wmccode');


		//if( $oMemberModel->pm_members[$logged_info->member_srl]    ||  $logged_info->member_srl ==  $output->data->member_srl  ) {
		// 일단 모든 사람들이 다 공유 할 수 있다. 
		if(1){	
			$output = executeQuery("eapprv.deleteEapprvShareNotIn", $args );
			$obj->eapprv_srl = $args->eapprv_srl ;
			foreach( $args->shared_members as $key => $val ){
				$obj->member_srl = $val ;
				$output = executeQuery("eapprv.insertShareNow", $obj );				
				
				debugPrint( $output );
			}	
		}else{
			debugPrint('NO AUTH ');		
		}

		$this->setRedirectUrl(  $args->error_return_url  );		
	}
	
	/// 공유자가 agree할것인지 아닌지를 저장한다.  
	function procEapprvAgreeOrNotInShare(){
		
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;
		$output = executeQuery("eapprv.updateEapprvShareToRead", $args);

	}
	
	
	/***
	* 부장만 결재 라인을 바꿀 수 있다. , 
	2015.11.2. 팀장도 바꿀 수 있다. 
	*/
	function procEapprvLineGmChange(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;
		
		$oModel = &getModel('wmccode'); 
		// 부서장일때문 

		if( $oModel->pm_members[$logged_info->member_srl] || $oModel->gm_member[$logged_info->member_srl]    ){
			//$args->line_stt = __LINE_STT_2__ ;
			/// 1/3 해당 라인을 지운다. 
			$output = executeQuery('eapprv.deleteLineNow' , $args );
			/// 2/3 해당 라인을 새로 고친다.
			$this->_InsertlineNow( $args );
			
			/// 3/3 상태값은 1 로 바꾼다. 
			$obj->eapprv_srl = $args->eapprv_srl 	;
			
			if( $args->line_stt == __LINE_STT_2__ )  $obj->stt2 = __STT_READY__ 				;
			if( $args->line_stt == __LINE_STT_3__ )  $obj->stt3 = __STT_READY__ 				;
			
			$output = executeQuery('eapprv.updateEapprv' , $obj );
		}	
/***################# 제목을 메신저로 보내려고 쿼리 한다. ***/
/// 다음단계로 이동일때 결재선 변경일때 두번 보낸다. 
		$args->member_srl = null ;
		$output = executeQuery('eapprv.getEapprv' , $args );

		
		$this->_setNextPerson($args->eapprv_srl , "(결재선 변경)".$output->data->title ,true );
		$this->setRedirectUrl(  $args->error_return_url  );		
	}
	
	/***
	*    권한체크 필요 없음. 
	*/
	function procEapprvLineDelete(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;
		
		$output = executeQuery("eapprv.deleteEapprvLinePre", $args );
	
		$this->setRedirectUrl(  $args->error_return_url  );		
	}
	
	/***
	* 권한체크 필요함. 
	*  부서장 , 담당 , 관리자   
	*/
	
	function procEapprvShareDelete(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;
		$output = executeQuery("eapprv.deleteEapprvSharePre", $args );
	
		$this->setRedirectUrl(  $args->error_return_url  );		
	}
	
	/****
	*  권한체크 필요함. 
	*  Owner인지 확인 해야함. 
	*/	
	function procEapprvWrite(){
		$obj = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$obj->member_srl = $logged_info->member_srl  ;		
		$obj->module_srl = $this->module_srl ;
		$obj->comment_status = "ALLOW";
		// generate document module model object
		$oDocumentModel = getModel('document');

		// generate document module의 controller object
		$oDocumentController = getController('document');

		// check if the document is existed
		$oDocument = $oDocumentModel->getDocument($obj->document_srl, $this->grant->manager);
		
		// update the document if it is existed
		$is_update = false;
		if($oDocument->isExists() && $oDocument->document_srl == $obj->document_srl)
		{
			$is_update = true;
		}

		// update the document if it is existed
		if($is_update)
		{
			if(!$oDocument->isGranted())
			{
				return new Object(-1,'msg_not_permitted');
			}

			if($this->module_info->protect_content=="Y" && $oDocument->get('comment_count')>0 && $this->grant->manager==false)
			{
				return new Object(-1,'msg_protect_content');
			}

			if(!$this->grant->manager)
			{
				// notice & document style same as before if not manager
				$obj->is_notice = $oDocument->get('is_notice');
				$obj->title_color = $oDocument->get('title_color');
				$obj->title_bold = $oDocument->get('title_bold');
			}
			
			// modify list_order if document status is temp
			if($oDocument->get('status') == 'TEMP')
			{
				$obj->last_update = $obj->regdate = date('YmdHis');
				$obj->update_order = $obj->list_order = (getNextSequence() * -1);
			}

			$output = $oDocumentController->updateDocument($oDocument, $obj);
			$msg_code = 'success_updated';

		// insert a new document otherwise
		} else {
			$output = $oDocumentController->insertDocument($obj, $bAnonymous);
			$msg_code = 'success_registed';
			$obj->document_srl = $output->get('document_srl');
		}

		$is_update = $obj->eapprv_srl ? true : false ;
		//// [1]   정보를 저장한다. 		
		if($obj->eapprv_srl ) {
			// 업데이트 
			$output = executeQuery("eapprv.updateEapprv", $obj );
		}else{
			// 인서트 
			$obj->eapprv_srl = getNextSequence();
			$output =executeQuery('eapprv.insertEapprv',$obj);
		
		} 

		
		//// [2]   결재 라인을 저장한다.  3개까지 저장할 수 있다. 
		$output = executeQuery('eapprv.deleteLineNow' , $obj );

		$this->_InsertlineNow( $obj );
		//// [3]   공유자를 지정한다... 공유자는 담당자 및 공유 받은 사람들이 저장할 수 있다.  
		//executeQuery(); 
		$output = executeQuery("eapprv.deleteEapprvShare" , $obj );
	
		$sobj->eapprv_srl = $obj->eapprv_srl ;
		foreach( $obj->eapprv_share as $key => $val ){
			$sobj->member_srl = intval($val) ;
			$output = executeQuery("eapprv.insertShareNow" , $sobj );
		}
		
debugPrint($obj);		
		
		$wmc_msg =new  stdclass();
		$wmc_msg->recvIdList =array();
		$obj->member_srl = null ;  
		// 다음 단계일때 메신저 보내지 않는다.   그런데 12.16일 등록할때 보내지 않으니까.. 이제는 다음단계일때 보내자... 
		//array_push( $wmc_msg->recvIdList ,	$this->_setNextPerson($obj->eapprv_srl , false )  );
		$this->_setNextPerson($obj->eapprv_srl, $obj->title , false ) ;

		/////////   $$$$$$$$$$$$$$$$$   WMC Message를 보낸다. /////
		
		if(!$is_update ){
			/// 입력 했으면 메세지 보낸다. 
			$wmc_msg->recvIdList =array();
			$wmc_msg->to = array();
			$wmc_msg->cc = array();
			$output = executeQueryArray('eapprv.getEapprvLines' , $obj );
			foreach( $output->data as $key => $val ){

				if($val->member_srl != $logged_info->member_srl ){
					array_push( $wmc_msg->recvIdList , $val->user_id   ) ;
					array_push( $wmc_msg->to        , $val->user_name ) ;
				}
			}
			$output = executeQueryArray('eapprv.getEapprvShares' , $obj );
			foreach( $output->data as $key => $val ){
				if($val->member_srl != $logged_info->member_srl ){
					array_push($wmc_msg->recvIdList , $val->user_id   ) ;
					array_push($wmc_msg->cc 	  , $val->user_name );
				}
			}
/*  등록 할때는 잠시 보내지 말자. 2015.12.16*/
			//if ( !$this->_sendMessage( $obj->eapprv_srl , $wmc_msg  ) ) {
				//$this->setMessage('wmcmsg_sendwmc_fail');
			//}
		}else{
/*  등록 할때는 잠시 보내지 말자. 2015.12.16 */			
			//if ( !$this->_sendMessage( $obj->eapprv_srl , $wmc_msg   ) ) {
				//$this->setMessage('wmcmsg_sendwmc_fail');
			//}
		}
		$this->setRedirectUrl( "/index.php?act=dispEapprvContent&eapprv_srl=".$obj->eapprv_srl ."&mid=".$this->mid   );	
	}

	
	/****
	*  결재 처리를 한다. 
	*
	*/
	function procEapprvProc(){
	
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;		
		$args->module_srl = $this->module_srl ;

		//// 1  update 한다. 
		$output = executeQuery('eapprv.updateEapprvLine',$args );
		if($args->stt == __STT_REJECT__ ) 
			$this->_rejectProc( $args );
		else if( $args->stt == __STT_OK__ ) 
			$this->_acceptProc( $args );
		
		// 제목을 문자 메세지로 보낼라면 이렇게 한다. 
		$args->member_srl = null ;
		$output = executeQuery('eapprv.getEapprv' , $args );
		

		$this->_setNextPerson($args->eapprv_srl , "(결재요망)". $output->data->title , true );
		$this->setRedirectUrl( $args->error_return_url );
		
	}
	
	function procEapprvDrop(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;		
		$args->module_srl = $this->module_srl ;

		$output = executeQuery('eapprv.getEapprv' , $args ); //// member_srl 을 넣을 경우 내가 쓴것이 아닌글은 안보인다. 
		
		$args->document_srl = $output->data->document_srl ;
		
		if( $output->data->member_srl  == $logged_info->member_srl){
			
			$output  = executeQuery('document.deleteDocument' , $args);
			$output  = executeQuery('eapprv.deleteEapprvShare' , $args);
			$output  = executeQuery('eapprv.deleteLineNow' , $args);
			$output  = executeQuery('eapprv.deleteEapprv' , $args);						
		}else{

		}

		$this->setRedirectUrl( "/index.php?mid=".$this->mid   );
	}
	
	/**
	@ depricated .
	@ 옛날에 memo 필드를 채웠었는데 이제는 procEapprvInsertComment 로 대체 한다. 
	*/
	function procEapprvMemoSave(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;

		$output = executeQuery("eapprv.updateEapprv", $args );

		$this->setRedirectUrl( $args->error_return_url );
	
	}
	/**
	*@ 누구나 몇개든지 comment를 달 수 있도록 수정한다. 
	*/
	function procEapprvInsertComment(){
		
		$obj = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
				// check if the doument is existed
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

		if(!$output->toBool())
		{
			return $output;
		}

		$this->setMessage('success_registed');
		$this->add('mid', Context::get('mid'));
		$this->add('document_srl', $obj->document_srl);
		$this->add('comment_srl', $obj->comment_srl);
		$this->add('eapprv_srl', $obj->eapprv_srl);
		$this->add('eapprv_srls', 'ddd');
		
	}
	/*
	*@  코멘트 삭제 
	*/
	function procEapprvDeleteComment(){
				// get the comment_srl
		$args = Context::getRequestVars();
		$comment_srl = Context::get('comment_srl');
		
		
		if(!$comment_srl)
		{
			return $this->doError('msg_invalid_request');
		}
		// generate comment  controller object
		$oCommentController = getController('comment');

		$output = $oCommentController->deleteComment($comment_srl, $this->grant->manager);
		if(!$output->toBool())
		{
			return $output;
		}

		$this->add('mid', Context::get('mid'));
		$this->add('page', Context::get('page'));
		$this->add('document_srl', $output->get('document_srl'));
		$this->setMessage('success_deleted');
		
		
		$url ='/?act=dispEapprvContent&eapprv_srl=' . $args->eapprv_srl .'&mid='.$args->mid .'&page='.$args->page;
		$this->setRedirectUrl($url );
	}
	
	/**
	*   똑같은 문서를 계속 만들기 귀찮으니까 그냥 복사를 한다. 
	*   문서는 복사를 하지 않고 , 1. 결재라인 , 2.구분값,  3.공유자를 복사 한다. 
	*/
	function procEapprvCopy(){
		$args = Context::getRequestVars();
		///  1. xe_eapprv 테이블 복사 
		///  2. xe_eapprv_line 테이블 복사
		///  3. xe_eapprv_share 테이블 복사 
	
	}
	
	function procEapprvSendMessage(){
		$args= Context::getRequestVars(); 
		
		$logged_info = Context::get('logged_info');
		$args->member_srl = null;
		$output = executeQuery('eapprv.getEapprv' , $args );
		$title = $output->data->title;
		
		if( !$logged_info->user_id ){
			$this->setMessage('실패');
			return ;
		}else {
			$obj->recvIdList = $args->member_id ;
			
			$obj->title  ="[업무 협조 요청] 확인부탁드립니다. \n";
			$obj->contents .= "\n 제목 : ". htmlspecialchars_decode($title) ."\n\n" ;
			$obj->contents .= "\n".__DEFAULT_URL__ ."/?eapprv_srl=" . $args->eapprv_srl ."&mid=".$args->mid."&act=dispEapprvContent  \n";
			$obj->contents .= "\n".$args->plane_memo."\n";
			$obj->contents .= _EAPPRV_MSG_SCRIPT_HEAD__ . $this->_getRandomScript() . _EAPPRV_MSG_SCRIPT_TAIL__; 		
		
			$obj->sendId = $logged_info->user_id;

			//sendWMCMessage($obj);
			$this->setMessage('추후 채팅으로 나감니다. ');	
		}
		
	}
	
	/**
	*@ 만약에 누군가 반려 버튼을 눌렀을 경우 이렇게 한다. 
	*/
	function _rejectProc(&$args){
		
		//// 2. eapprv 테이블에 __STT_REJECT__ 상태로 입력한다. 

		$obj->eapprv_srl = $args->eapprv_srl ;
		$obj->stt_int = __STT_REJECT__ ;
		
		if( $args->line_stt == __LINE_STT_1__  ) 
			$obj->stt1 = __STT_REJECT__ ;
		else if ( $args->line_stt == __LINE_STT_2__  ) 
			$obj->stt2 = __STT_REJECT__ ;
		else if ( $args->line_stt == __LINE_STT_3__  ) 
			$obj->stt3 = __STT_REJECT__ ;
		$output = executeQuery('eapprv.updateEapprv' , $obj );		
	}
	function _acceptProc( &$args){
		
		
		///  모든 상태값이 다 __STT_OK__ 라면    Complete 이고   아니면 진행중(__STT_ACCEPT__ )이다. 
		$obj->eapprv_srl = $args->eapprv_srl ;
		$obj->line_stt = $args->line_stt ;
		$obj->stt = __STT_OK__;
/*
	@ _setNextPerson() 함수로 이전한다. 		
		//// 3. 다음 담당자를 ACCEPT 상태로 바꾼다. 
		if( $next_person ){
			$obj->member_srl = $next_person;
			$obj->eapprv_srl = $args->eapprv_srl ;
			$obj->stt_line   = $args->stt_line ;
			$obj->stt 		 = __STT_ACCEPT__ ;
			$output = executeQuery(  'eapprv.updateEapprvLine',  $obj ); 
		}
*/
		///   1/3 내꺼를 OK로 칠한다. 
		$output = executeQuery(  'eapprv.updateEapprvLine',  $obj ); 
		
		//// 2/3  작은 라인것을 가져온다. 결재라인,접수라인,처리라인. 
		/// 이중에서 모든것이 __STT_OK__ 가 아니면 3이 아니다. 
		$obj->stt = null ;
		$output = executeQueryArray('eapprv.getEapprvLine' , $obj);
		$stt = __STT_OK__ ;
		foreach( $output->data as $key => $val ){
			if($val->stt != __STT_OK__ ) $stt = __STT_ACCEPT__ ;
		}
				//// 3. eapprv 테이블에 상태를 입력한다. 
		if( $args->line_stt == __LINE_STT_1__  ) 
			$obj->stt1 = $stt ;
		else if ( $args->line_stt == __LINE_STT_2__  ) 
			$obj->stt2 = $stt ;
		else if ( $args->line_stt == __LINE_STT_3__  ) 
			$obj->stt3 = $stt ;


		////  3/3   그리고 모든 사람들의 상태가 __STT_OK__인지 확인 한다. 
		$obj2->eapprv_srl = $args->eapprv_srl ;
		$obj2->line_stt = null ;
		$obj2->member_srl = null ;
		$output = executeQueryArray('eapprv.getEapprvLineCheck' , $obj2);
		$stt_int = __STT_OK__ ;
		foreach( $output->data as $key => $val){
			if( $val->stt != __STT_OK__ ) $stt_int = __STT_ACCEPT__ ;
		}
		/////  모든 stt 가 __STT_OK__  라면  전체 완료라고 표기 한다. 
		$obj->stt_int = $stt_int ;
		$output = executeQuery('eapprv.updateEapprv' , $obj );
		
		
		if( $stt_int == __STT_OK__ ){
			//완료이다.... 
		}
		
	}
	
	function _InsertlineNow( &$args){
		//$obj = stdClass;
		
		////#### 첫 사람은 꼭 __STT_ACCEPT__ 로 등록 해주어야 결재가 시작됩니다. 
		$obj->eapprv_srl = $args->eapprv_srl ;
		$obj->line_title = 	__LINE1_TITLE__ ;
		$obj->position   =  __LINE1_CODE__ ;	

/// #### 각 담당자 입니다. 
		
		$obj->stt =  __STT_READY__ ;
		if( $args->line1_1 ) {
			$obj->member_srl = $args->line1_1 ;
			$obj->line_stt = __LINE_STT_1__;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
		
		
		if( $args->line2_1){
			$obj->member_srl = $args->line2_1 ;
			$obj->line_stt = __LINE_STT_2__;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
	
		if( $args->line3_1){
			$obj->member_srl = $args->line3_1 ;
			$obj->line_stt = __LINE_STT_3__;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
	
		/// 2째 결재자의 두번째 
		//$obj->stt =  __STT_READY__ ; 
		$obj->line_title =  __LINE2_TITLE__ ;
		$obj->position   =  __LINE2_CODE__  ;	
		if( $args->line1_2 ) {
			$obj->member_srl = $args->line1_2 ;
			$obj->line_stt = __LINE_STT_1__;
//			if( !$args->line1_1 )   $obj->stt = __STT_ACCEPT__ ;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
		if( $args->line2_2){
			$obj->member_srl = $args->line2_2 ;
			$obj->line_stt = __LINE_STT_2__;
			//if( !$args->line2_1 )   $obj->stt = __STT_ACCEPT__ ;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
		if( $args->line3_2){
			$obj->member_srl = $args->line3_2 ;
			$obj->line_stt = __LINE_STT_3__;
			//if( !$args->line3_1 )   $obj->stt = __STT_ACCEPT__ ;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}

		$obj->line_title = __LINE3_TITLE__ ;
		$obj->position   =  __LINE3_CODE__ ;	
		/// 3째 결재자의 세번째 		
		if( $args->line1_3 ) {
			$obj->member_srl = $args->line1_3 ;
			$obj->line_stt = __LINE_STT_1__;
			//if( !$args->line1_1 && !$args->line1_2 )   $obj->stt = __STT_ACCEPT__ ;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
		if( $args->line2_3){
			$obj->member_srl = $args->line2_3 ;
			$obj->line_stt = __LINE_STT_2__;
			//if( !$args->line2_1 && !$args->line2_2 )   $obj->stt = __STT_ACCEPT__ ;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
		if( $args->line3_3){
			$obj->member_srl = $args->line3_3 ;
			$obj->line_stt = __LINE_STT_3__;
			//if( !$args->line3_1 && !$args->line3_2 )   $obj->stt = __STT_ACCEPT__ ;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}

		$obj->line_title = __LINE4_TITLE__ ;
		$obj->position   =  __LINE4_CODE__ ;		
		if( $args->line1_4 ) {
			$obj->member_srl = $args->line1_4 ;
			$obj->line_stt = __LINE_STT_1__;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
		if( $args->line2_4){
			$obj->member_srl = $args->line2_4 ;
			$obj->line_stt = __LINE_STT_2__;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
		if( $args->line3_4){
			$obj->member_srl = $args->line3_4 ;
			$obj->line_stt = __LINE_STT_3__;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
		
		
		$obj->line_title = __LINE5_TITLE__ ;
		$obj->position   =  __LINE5_CODE__ ;		
		if( $args->line1_5 ) {
			$obj->member_srl = $args->line1_5 ;
			$obj->line_stt = __LINE_STT_1__;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
		if( $args->line2_5){
			$obj->member_srl = $args->line2_5 ;
			$obj->line_stt = __LINE_STT_2__;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
		if( $args->line3_5){
			$obj->member_srl = $args->line3_5 ;
			$obj->line_stt = __LINE_STT_3__;
			$output = executeQuery('eapprv.insertLineNow' , $obj );
		}
		
	}
/*@
@	결재라인 다음에 누가 올것인가를 지정한다.  __STT_ACCEPT__ 상태로 바꾸어 준다.
@
*/	
	function _setNextPerson( $eapprv_srl , $title ="-", $send_msg=true ){
		$args->eapprv_srl = $eapprv_srl ;
		$output = executeQueryArray( 'eapprv.getNextPerson', $args ) ;

		foreach( $output->data as $key => $val  ){	
			$args->member_srl 	= $val->member_srl ;
			$args->line_stt 	= $val->line_stt ;
			$args->stt 			= __STT_ACCEPT__ ;
			$args->user_id 		= $val->user_id ; //// 여거는 쪽지 보낼라고 저장했다. 
			//// setNextPerson 
			$output=executeQuery('eapprv.updateEapprvLineSetNextPerson', $args );
			$obj->recvIdList= array($args->user_id);
			$obj->title = $title ;
			if($send_msg) $this->_sendMessageToNextPerson( $eapprv_srl, $obj );
		}	
		return $args->user_id ;
	}

	function _sendMessage( $eapprv_srl , $wmc_msg ){
		$obj->sendId ="";
        array_unique ($wmc_msg->recvIdList);		
		//unset( $wmc_msg->recvIdList[188] );
		
		$obj->recvIdList = implode(',',array_unique($wmc_msg->recvIdList));
		if( $wmc_msg->to ) $obj->to ="\n[수신] ". implode(',',array_unique($wmc_msg->to));
		if( $wmc_msg->cc ) $obj->cc ="\n[참조] ". implode(',',array_unique($wmc_msg->cc));

		/////
		$obj->title  ="[전자 결재]".$wmc_msg->title." 결재 바랍니다. \n";
		
		$obj->contents  = $obj->to;
		$obj->contents .= $obj->cc;
		$obj->contents .= "\n 제목 : ". $wmc_msg->title ."\n\n" ;
		$obj->contents 	.= "\n".__DEFAULT_URL__ ."/?eapprv_srl=" . $eapprv_srl ."&mid=".$this->module_info->mid."&act=dispEapprvContent  \n";
		
		$obj->contents  .= _EAPPRV_MSG_SCRIPT_HEAD__ . $this->_getRandomScript() . _EAPPRV_MSG_SCRIPT_TAIL__; 
		return sendWMCMessage( $obj );
	}
	
	function _sendMessageToNextPerson( $eapprv_srl, $wmc_msg){
	/*
		$obj->sendId ="";
		$obj->recvIdList = implode(',',array_unique($wmc_msg->recvIdList));
		/////
		$obj->title  ="[결재가 상신되었습니다. 결재 바랍니다.]" ;
		$obj->contents .= "\n 제목 : ". $wmc_msg->title ."\n\n" ;
		$obj->contents 	.= "\n".__DEFAULT_URL__ ."/?eapprv_srl=" . $eapprv_srl ."&mid=".$this->module_info->mid."&act=dispEapprvContent  \n"; 
		$obj->contents  .= _EAPPRV_MSG_SCRIPT_HEAD__ . $this->_getRandomScript() . _EAPPRV_MSG_SCRIPT_TAIL__; 

		
		$ret = sendWMCMessage( $obj ); 
		return $ret ;
	*/	
	}
	function _getRandomScript(){
		$rand = mt_rand(1 , 13);
		switch ( $rand ) {
			case 1 :
				return _EAPPRV_MSG_SCRIPT1__;
			case 2 :
				return _EAPPRV_MSG_SCRIPT2__;
			case 3 :
				return _EAPPRV_MSG_SCRIPT3__;
			case 4 :
				return _EAPPRV_MSG_SCRIPT4__;
			case 5 :
				return _EAPPRV_MSG_SCRIPT5__;
			case 6 :
				return _EAPPRV_MSG_SCRIPT6__;
			case 7 :
				return _EAPPRV_MSG_SCRIPT7__;
			case 8 :
				return _EAPPRV_MSG_SCRIPT8__;
			case 9 :
				return _EAPPRV_MSG_SCRIPT9__;
			case 10 :
				return _EAPPRV_MSG_SCRIPT10__;
			case 11 :
				return _EAPPRV_MSG_SCRIPT11__;
			case 12 :
				return _EAPPRV_MSG_SCRIPT12__;
			case 13 :
				return _EAPPRV_MSG_SCRIPT13__;				
		}
		 
		
		
	}
	
}

?>
