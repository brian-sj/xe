<?php
/* Copyright (C) WMC */
/**
 * @class  releasenoteView
 * @author WMC 
 * @brief wmc module's controller class
 */
class releasenoteView extends releasenote
{
		/**  ,스킨 없다... */
	function init()
	{
			//$this->setTemplatePath($this->module_path.'tpl');
			$this->setTemplatePath($this->module_path.'skins/default/'  );
	}

	function dispReleasenoteIndex(){ 
		Context::set('layout','HTML');
		$args = Context::getRequestVars();
		$logged_info = Context::get('logged_info');
		if (!$args->filter) $args->filter=__NOT_ALL__;

    	$oModel = &getModel('releasenote');
		$args->my_member_srl = $logged_info->member_srl ;
		
		if( $args->filter == __ALL__ ) $args->my_member_srl =NULL;
		//if( !$args->stt_2 ) $args->stt_2 = "Z";
		if( !$args->stt_2 ) $args->stt_2 = __STT_RELEASE_COMPLETE__  ;
		if( $args->stt_2 == __ALL__ ) $args->stt_2 = null ;
			
		$output = executeQueryArray('releasenote.getReleasenotes', $args);

		$mem_list = $oModel->getMemberList(); 
		foreach( $output->data as $key=>$val )
		{
			$output->data[$key]->worker_name =$oModel->getMemberNameBySrl( $val->worker_srl , $mem_list  );
			$output->data[$key]->releaser_name =$oModel->getMemberNameBySrl( $val->releaser_srl , $mem_list  );
			$output->data[$key]->t_releaser_name = $oModel->getMemberNameBySrl( $val->t_releaser_srl , $mem_list  );
			$output->data[$key]->tester_name =$oModel->getMemberNameBySrl( $val->tester_srl , $mem_list  );
			$output->data[$key]->manager_name =$oModel->getMemberNameBySrl( $val->manager_srl , $mem_list  );
			$output->data[$key]->header_name =$oModel->getMemberNameBySrl( $val->header_srl , $mem_list  );
			//$output->data[$key]->worker_name =$oModel->getMemberNameById( $val->worker_srl , &$mem_list  );
		}

		Context::set('list', $output->data);
		Context::set('page_navigation', $output->page_navigation);
		//Context::set('category', $args->category);
		//Context::set('stt', $args->stt);

		$this->setTemplateFile('index');   
	}
	
	function dispReleasenoteContent(){ 
		Context::set('layout','HTML');
		$args = Context::getRequestVars();		
		$output = executeQuery('releasenote.getReleasenote', $args);
		$document_srl = $output->data->document_srl ;
		$oModel = &getModel("releasenote");
		$mem_list = $oModel->getMemberList(); 

		$output->data->worker_name = $oModel->getMemberNameBySrl( $output->data->worker_srl , $mem_list  );
		$output->data->releaser_name = $oModel->getMemberNameBySrl( $output->data->releaser_srl , $mem_list  );
		$output->data->t_releaser_name = $oModel->getMemberNameBySrl( $output->data->t_releaser_srl , $mem_list  );
		$output->data->tester_name = $oModel->getMemberNameBySrl( $output->data->tester_srl , $mem_list  );
		$output->data->manager_name = $oModel->getMemberNameBySrl( $output->data->manager_srl , $mem_list  );
		$output->data->header_name = $oModel->getMemberNameBySrl( $output->data->header_srl , $mem_list  );

		Context::set('val',$output->data);
		$logged_info = Context::get('logged_info');

debugPrint($output->data);		
		Context::set("member_srl" , $logged_info->member_srl);
		Context::set("is_admin", $logged_info->is_admin);

		// 등록된 맨티스 목록 입니다. 
		$output = executeQuery('releasenote.getMantisIdByReleasenoteSrl' , $args );
		Context::set('reg_bug_list' , $output->data );
		
		// 테스터 목록
		$output = executeQueryArray('releasenote.getReleasenoteTesters',$args);
		Context::set('tester_list', $output->data);

		// 작업자 목록
		$obj->eapp_srl = $args->note_srl ;
		$obj->category = __RELEASE_NOTE_WORKER__ ;
		$output = executeQueryArray('wmccode.getEappByEappSrl' , $obj);
		Context::set("sub_worker_list" , $output->data );
		
		$oDocumentModel = getModel('document');
		$oDocument = $oDocumentModel->getDocument( $document_srl );
		Context::set('oDocument', $oDocument);
		
		Context::set('module_srl', $this->module_srl);
		
		Context::addJsFilter($this->module_path.'tpl/filter', 'insert_comment.xml');
		Context::set('grant',$this->grant);
		
		$oModel = &getModel('wmccode');
		Context::set('request_dept', $oModel->request_dept);
		
		$this->setTemplateFile('content');   
	}
	
	function dispReleasenoteWrite(){ 
		$args = Context::getRequestVars();
		
		Context::set('layout','HTML');
		$document_srl = 0;

		if($args->note_srl){
			$output = executeQuery('releasenote.getReleasenote', $args);
			Context::set('val', $output->data);
debugPrint($output);
			$document_srl = $output->data->document_srl;
			$output_m = executeQuery('releasenote.getMantisIdByReleasenoteSrl' , $args );
			Context::set('mantis_list', $output_m->data);
		}else{
			$output->data->category="CS";  // 만약에 입력이면 C/S 기준으로 한다. 
		} 

		$oModel = &getModel('wmccode');
		Context::set('releaser_list', $oModel->releaser_members );
		Context::set('request_dept', $oModel->request_dept);

		$obj->code_gu = "SWCODE";
		$output = $oModel->getCode( $obj );
		Context::set('codelist', $output->data );
		
		$output = $oModel->getMemberList( $args );
		Context::set('userlist', $output->data );
	
		
		// 테스터는 꼭 내명까지로 추가 합니다. 		
		$output = executeQueryArray('releasenote.getReleasenoteTesters',$args);
		Context::set('tester_list', $output->data);
		

		/// 보조 작업자..
		$obj->eapp_srl = $args->note_srl ;
		$obj->category = __RELEASE_NOTE_WORKER__ ;
		$output = executeQueryArray('wmccode.getEappByEappSrl' , $obj);

		$sub_worker_srls = array();
		foreach( $output->data as $value ){
			array_push( $sub_worker_srls , $value->member_srl ) ;
		}
		Context::set("sub_worker_srls" , $sub_worker_srls );
		
		// 등록된 맨티스 목록 입니다. 
		$output = executeQuery('releasenote.getMantisIdByReleasenoteSrl' , $args );
		Context::set('reg_bug_list' , $output->data );
		
		// 등록 되지 않은 맨티스 목록 입니다. 
		$oModel = &getModel('releasenote');
		$output = $oModel->getMantisByStatus( $args);

		Context::set('bug_list', $output->data);
	

		// GET parameter document_srl from request
		//$document_srl = Context::get('document_srl');
		$oDocumentModel = &getModel('document');
		
		$oDocument = $oDocumentModel->getDocument( $document_srl , $this->grant->manager);

		$oDocument->setDocument($document_srl);
	
		if($oDocument->get('module_srl') == $oDocument->get('member_srl')) $savedDoc = TRUE;
		$oDocument->add('module_srl', $this->module_srl);

		/*
		if($oDocument->isExists() && $this->module_info->protect_content=="Y" && $oDocument->get('comment_count')>0 && $this->grant->manager==false)
		{
			return new Object(-1, 'msg_protect_content');
		}
		// if the document is not granted, then back to the password input form
		$oModuleModel = getModel('module');
		if($oDocument->isExists()&&!$oDocument->isGranted())
		{
			return $this->setTemplateFile('input_password_form');
		}
		*/
		if(!$oDocument->get('status')) $oDocument->add('status', $oDocumentModel->getDefaultStatus());

		$statusList = $this->_getStatusNameList($oDocumentModel);
		if(count($statusList) > 0) Context::set('status_list', $statusList);

		// get Document status config value
		Context::set('document_srl',$document_srl);
		Context::set('oDocument', $oDocument);

		// apply xml_js_filter on header
		$oDocumentController = &getController('document');
		$oDocumentController->addXmlJsFilter($this->module_info->module_srl);
		
		// if the document exists, then setup extra variabels on context
		if($oDocument->isExists() && !$savedDoc) Context::set('extra_keys', $oDocument->getExtraVars());

		/**
		 * add JS filters
		 **/
		if(Context::get('logged_info')->is_admin=='Y') Context::addJsFilter($this->module_path.'tpl/filter', 'insert_admin.xml');
		else Context::addJsFilter($this->module_path.'tpl/filter', 'insert.xml');

		$oSecurity = new Security();
		$oSecurity->encodeHTML('category_list.text', 'category_list.title');
		

		$this->setTemplateFile('write');   
	}
	////## 댓글 삭제... 
	function dispReleasenoteDeleteComment(){
		$comment_srl = Context::get('comment_srl');
		// if the comment exists, then get the comment information
		if($comment_srl)
		{
			$oCommentModel = getModel('comment');
			$oComment = $oCommentModel->getComment($comment_srl, $this->grant->manager);
		}
		
		// if the comment is not granted, then back to the password input form
		if(!$oComment->isGranted())
		{
			return $this->setTemplateFile('input_password_form');
		}
		Context::set('oComment',$oComment);
		/**
		 * add JS filters
		 **/
		Context::addJsFilter($this->module_path.'tpl/filter', 'delete_comment.xml');
		$this->setTemplateFile('delete_comment_form');
	}
	
	//// 주간 맨티스 입력 통계 
	function dispReleasenoteReportWeeklyAdded() {
		$output=executeQueryArray('releasenote.reportWeeklyAdded',$args);
		Context::set('list', $output->data );
	
		$this->setTemplateFile('report_weekly_added');
	}
	
	
	function _getStatusNameList(&$oDocumentModel)
	{
		$resultList = array();
		if(!empty($this->module_info->use_status))
		{
			$statusNameList = $oDocumentModel->getStatusNameList();
			$statusList = explode('|@|', $this->module_info->use_status);

			if(is_array($statusList))
			{
				foreach($statusList as $key => $value)
				{
					$resultList[$value] = $statusNameList[$value];
				}
			}
		}
		return $resultList;
	}
	
	

}/*

*/