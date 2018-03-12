<?php
/**
 * @class  releasenoteController
 * @author NAVER (developers@xpressengine.com)
 * @brief releasenote module's controller class
 releasenote.controller.php
 */
class releasenoteController extends releasenote
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
	function procReleasenoteWrite()
	{
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;		
		$args->module_srl = $this->module_srl ;
		
		$obj = $args ;

	
		$output =$this->_saveDocument ($obj);

////(1/5)   도큐먼트를 입력 한다. 
		
///(2/5) 릴리즈 노트를 입력 한다. 		
		
		$args->tester_srl = array_unique( $args->tester_srl); // 테스터는 중복될 수 없다. 
		$args->sub_worker_srl = array_unique( $args->sub_worker_srl ); // 보조 작업자도 중복 될 수 없다. 
				
		if (!$args->tester_srl)  $args->tester_srl = array();
		if (!$args->sub_worker_srl)  $args->sub_worker_srl = array();

		$args->shared_members = array_merge( $args->tester_srl  , $args->sub_worker_srl ); 
		$args->shared_members = "|".implode( ",",$args->shared_members );

		if( $args->note_srl ) {
			$output = executeQuery("releasenote.updateReleasenote", $args);
			//$output = executeQuery("releasenote.updateReleasenoteTester", $args );
		}
		else {
			$args->note_srl = getNextSequence();
			$output = executeQuery("releasenote.insertReleasenote", $args);
		}


//// 복수 테스터를 입력 한다. 
		//**** 앞선 모든 Tester 정보를 삭제 한다. 
		$output = executeQuery("releasenote.deleteReleasenoteTester" , $args);
		$obj->note_srl = $args->note_srl ;
		foreach( $args->tester_srl as $key => $val){
		if ($val!= 0)
			{
				$obj->tester_srl = $val ;
				$output = executeQuery("releasenote.insertReleasenoteTester" , $obj);		
			}
		}

/// 복수 작업자를 입력한다. 

		$oModel = &getModel("wmccode");
		$obj->category = __RELEASE_NOTE_WORKER__ ;
		$obj->eapp_srl = $args->note_srl ;
		$output = $oModel->deleteEappAll( $args->note_srl );

		$outputBool  = $oModel->saveEapp( $args->sub_worker_srl , $obj );

/// 맨티스를 입력 한다. 
		$obj->note_srl = $args->note_srl ;
		foreach( $args->mantis_id as $key => $val  )
		{
			$obj->mantis_id = $val;
			$output =executeQuery("releasenote.insertReleasenoteMantisId" , $obj); 
		}
		if( $output->toBool() )
			$this->add("result", "0");	
		else 
			$this->add("result" , "-1");


		$this->setRedirectUrl( "/index.php?act=dispReleasenoteContent&note_srl=".$args->note_srl."&mid=".$args->mid );

		
	}

	function procReleasenoteDeleteMantisId()
	{
		// mantis_id , note_srl 두개가 필요합니다. 
		$args = Context::getRequestVars();
		
		$output = executeQuery("releasenote.deleteReleasenoteMantisId" , $args); 
		if( $output->toBool() )
			$this->add("result", $args->mantis_id );	
		else 
			$this->add("result" , "-1");
	}
	function procReleasenoteDeleteCode()
	{
		$oModel = &getModel('wmccode');
		$output =$oModel->deleteCode($args);

	}
	function procReleasenoteSaveCode() 
	{
		$args = Context::getRequestVars();
		$oModel = &getModel('wmccode');
		
		$args->code_int_val = getNextSequence();
		$output =$oModel->saveCode($args);
		debugPrint( $output );
	}
	function procReleasenoteConfirmCheck()
	{
		$args = Context::getRequestVars();
/*		
 define("__STT_ADDED_COMPLETE,"     , "A");
 define("__STT_WORK_COMPLETE__" 	, "B");
 
 define("__STT_TEST_UPLOAD_COMPLETE__" 	, "U"); 이게 나중에 추가됨. 순서는 여기 입니다. 
 __STT_TEST_UPLOAD_COMPLETE_NEED_TEST_UPLOAD__    그냥 올라온게 아니라. 꼭 테스트 업로드 해달라는 겁니다. 
 
 define("__STT_TEST_COMPLETE__" 	, "C");
 define("__STT_M_COMPLETE__" 		, "D");
 define("__STT_H_COMPLETE__" 		, "E");
 define("__STT_RELEASE_COMPLETE__" 	, "F");
 */
 

		if( $args->gu == __STT_TEST_UPLOAD_COMPLETE__)
				$output = executeQuery("releasenote.updateReleasenoteConfirmU", $args);
		if( $args->gu == __STT_WORK_COMPLETE__)
				$output = executeQuery("releasenote.updateReleasenoteConfirmB", $args);
		if( $args->gu == __STT_TEST_UPLOAD_COMPLETE_NEED_TEST_UPLOAD__)
				$output = executeQuery("releasenote.updateReleasenoteConfirmBU", $args);
		else if( $args->gu == __STT_TEST_COMPLETE__)		
				$output = executeQuery("releasenote.updateReleasenoteConfirmC", $args);
		else if( $args->gu == __STT_M_COMPLETE__)		
				$output = executeQuery("releasenote.updateReleasenoteConfirmD", $args);
		else if( $args->gu == __STT_H_COMPLETE__)	
				$output = executeQuery("releasenote.updateReleasenoteConfirmE", $args);		
		else if( $args->gu == __STT_RELEASE_COMPLETE__)	
				$output = executeQuery("releasenote.updateReleasenoteConfirmF", $args);		
		
		$this->setRedirectUrl( $args->error_return_url );
	}
	/***
	* 테스터가 리젝트 할 경우에 어떻게 해야하는가? 
	*/
	function procReleasenoteRejectCheck(){
		$args= Context::getRequestVars(); 
		$output = executeQuery('releasenote.updateReleasenoteReject', $args );

debugPrint( $output );		
		$this->setMessage('반려하였습니다. 스낵쪽지를 추가로 보낼 수 있습니다(수동).');
	}
	/***
	* 문제인지 아닌지 질문을 하는 단계 . 단계 수정은 없고 쪽지만 간다. 
	*/
	function procReleasenoteSendMessage(){
		$args= Context::getRequestVars(); 
		$logged_info = Context::get('logged_info');
		$output = executeQuery('member.getMemberInfoByMemberSrl', $args, array('user_id' , 'user_name'));

		if( !$output->data ){
			$this->setMessage('쪽지발송실패');
			return ;
		}else {
			$obj->recvIdList = $output->data->user_id ;
			$obj->contents .= "\n\n 확인 부탁 드립니다.  \n\n  ";
			$obj->contents .= "\n".__DEFAULT_URL__."/?act=dispReleasenoteContent&mid=".$args->mid."&note_srl=".$args->note_srl. "\n";
			$obj->contents .= "\n".$args->plane_memo."\n";
			$obj->title = sprintf("[%s] 배포요청에 %s님의 확인이 필요합니다. " , $args->title , $output->data->user_name );
			$obj->sendId = $logged_info->user_id;
			sendWMCMessage($obj);
			$this->setMessage('쪽지발송성공');	
		}
	}
	function procReleasenoteConfirmCheckTester(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');

		if( $logged_info->is_admin !='Y' && $logged_info->member_srl != $args->tester_srl ) 
			debugLog('이거 머냐? 관리자도 아니면서 대신 컨펌을? ' . $logged_info->member_srl );
		else 
			$output = executeQuery('releasenote.updateReleasenoteConfirmTester' , $args);
		$this->setRedirectUrl( $args->error_return_url );
	}
	
	function procReleasenoteConfirmCheckSubWorker(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');

		$obj->eapp_srl = $args->note_srl ;
		$obj->member_srl =  $logged_info->member_srl ;
		$obj->category  = __RELEASE_NOTE_WORKER__ ;
		
		if( $logged_info->is_admin =='Y' ) 
			$obj->member_srl = $args->member_srl ;
		
		
		$output = executeQuery('wmccode.updateEappConfirm' , $obj); 

		$this->setRedirectUrl( $args->error_return_url );
	}
	
	function procReleasenoteSaveComment()
	{
/*		
		$args = Context::getRequestVars();
		$logged_info = Context::get("logged_info");
		$args->comment = sprintf("\n<div class=\"release-comment\">%s\n <span class=\"comment-head\"> (%s) ", $logged_info->user_name , date("Y.m.d H:i:s") )."</span><span class=\"comment-body\">". htmlspecialchars($args->comment) ."</span></div>"  ;

		$oModel = &getModel('releasenote');
		if($args->note_srl ){
		  $output = $oModel->saveComment($args);
		}else{
		}
*/

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
		
		//$this->setRedirectUrl( $args->error_return_url );
	}
	/* comment 삭제...  **/
	function procReleasenoteDeleteComment(){
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
	}
	
	function procReleasenoteDrop(){
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl  ;		
		$args->module_srl = $this->module_srl ;

		$output = executeQuery('releasenote.getReleasenote' , $args ); //// member_srl 을 넣을 경우 내가 쓴것이 아닌글은 안보인다. 
		if($logged_info->is_admin=='Y'||$this->grant->gm_manager) $args->member_srl = $output->data->member_srl;
		
		$args->document_srl = $output->data->document_srl ;
		
		if( $output->data->member_srl  == $args->member_srl){
			$output  = executeQuery('document.deleteDocument' , $args);
			$output  = executeQuery('releasenote.deleteReleasenote' , $args);						
		}else{

		}

		$this->setRedirectUrl( "/index.php?mid=".$this->mid   );
	}

	function procReleasenotePmsToggle(){
		$args = Context::getRequestVars();

		$logged_info= Context::get("logged_info");
		$args->member_srl = $logged_info->member_srl;

		$args->stt3 = $args->add;
		$output = executeQuery("releasenote.updateReleasenotePmsAdd", $args );

		$this->setRedirectUrl( $args->error_return_url );
		
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

	
}/* End of file releasenote.controller.php */
/* Location: ./modules/releasenote/releasenote.controller.php */